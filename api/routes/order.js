import sql from "../db.js"
import { verifyJWT, getBearerToken, NoTokenError, InvalidTokenError } from "../jwt.js"

class InvalidOrderRequestError extends Error {
    constructor(message) {
        super(message)
        this.name = "InvalidOrderRequestError"
    }
}

const getOrder = async (id) => {
    let order = await sql`SELECT * FROM orders WHERE id = ${id}`
    if (order.length === 0) {
        throw new InvalidOrderRequestError("Invalid order id")
    }
    order = order[0]
    order.products = await sql`SELECT * FROM order_items WHERE order_id = ${order.id}`
    return order
}

const getOrders = async (ids) => {
    let orders = null;
    if (ids.length === 1) {
        orders = await sql`SELECT * FROM orders WHERE id = ${ids[0]}`
    } else {
        orders = await sql`SELECT * FROM orders WHERE id = ANY(${sql.array(ids, 'int4')})`
    }
    let ordersOut = []
    for (let order of orders) {
        ordersOut.push(await getOrder(order.id))
    }
    return ordersOut
}

const orderProducts = (req, res) => {
    // get bearer token from header
    let token = getBearerToken(req)
    verifyJWT(token).then((payload) => {
        let orderRequests = req.body;
        // Validate order requests
        for (let orderRequest of orderRequests) {
            if (!orderRequest.productId || !orderRequest.quantity) {
                return Promise.reject(new InvalidOrderRequestError("Invalid order request"))
            }
            if (orderRequest.quantity < 1 || !Number.isInteger(orderRequest.quantity)) {
                return Promise.reject(new InvalidOrderRequestError("Invalid quantity"))
            }
        }

        let productChecks = orderRequests.map(orderRequest => {
            return sql`SELECT * FROM products WHERE id = ${orderRequest.productId}`.then((product) => {
                if (product.length === 0) {
                    return Promise.reject(new InvalidOrderRequestError("Invalid product id"))
                }
                if (product.stock < orderRequest.quantity) {
                    return Promise.reject(new InvalidOrderRequestError("Not enough stock"))
                }
            });
        });

        return Promise.all(productChecks).then(() => payload);
    }).then((payload) => {
        return sql`INSERT INTO orders (user_id) VALUES (${payload.user_id}) RETURNING *`
    }).then((order) => order[0]).then((order) => {
        let orderItemInserts = req.body.map((orderRequest) => {
            return sql`INSERT INTO order_items (order_id, product_id, quantity) VALUES (${order.id}, ${orderRequest.productId}, ${orderRequest.quantity})`
        });
        let productUpdates = req.body.map((orderRequest) => {
            return sql`UPDATE products SET stock = stock - ${orderRequest.quantity} WHERE id = ${orderRequest.productId}`
        });
        return Promise.all([...orderItemInserts, ...productUpdates]).then(() => order)
    }).then((order) => {
        return getOrder(order.id);
    }).then((order) => {
        res.status(200).send(order)
    }).catch((err) => {
        if (err instanceof NoTokenError || err instanceof InvalidTokenError) {
            res.status(401).send("Unauthorized")
            return
        }
        if (err instanceof InvalidOrderRequestError) {
            res.status(400).send(err.message)
            return
        }
        console.log(err);
        res.status(500).send("Internal server error");
    });
}

const viewOrders = (req, res) => {
    let token = getBearerToken(req)
    verifyJWT(token).then((payload) => {
        return sql`SELECT * FROM orders WHERE user_id = ${payload.user_id}`
    }).then((orders) => {
        return getOrders(orders.map((order) => order.id))
    }).then((orders) => {
        res.status(200).send(orders)
    }).catch((err) => {
        if (err instanceof NoTokenError || err instanceof InvalidTokenError) {
            res.status(401).send("Unauthorized")
            return
        }
        console.log(err);
        res.status(500).send("Internal server error");
    });
}

const viewOrder = (req, res) => {
    let token = getBearerToken(req)
    verifyJWT(token).then((payload) => {
        return getOrder(req.params.id).then((order) => {
            if (!order) {
                throw new InvalidOrderRequestError("Invalid order id")
            }
            if (order.user_id !== payload.user_id) {
                throw new InvalidOrderRequestError("Unauthorized")
            }
            res.status(200).send(order)
        })
    }).catch((err) => {
        if (err instanceof NoTokenError || err instanceof InvalidTokenError) {
            res.status(401).send("Unauthorized")
            return
        }
        if (err instanceof InvalidOrderRequestError) {
            res.status(400).send(err.message)
            return
        }
        console.log(err);
        res.status(500).send("Internal server error");
    });
}

export { orderProducts, viewOrders, viewOrder }