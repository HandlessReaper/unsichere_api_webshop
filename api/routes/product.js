import sql from "../db.js"

const product = (req, res) => {
    let id = req.params.id
    sql`SELECT * FROM products WHERE id = ${id};`.then(result => {
        res.send(result)
    }).catch(_ => {
        res.status(404).send("Product not found")
    })
}
/* maybe implementieren?
begrenz produkte anzeigen, falls viele produkte angezeigt werden müssen zu hohe belastung
const limit = parseInt(req.query.limit) || 10; // Anzahl der Produkte pro Seite
const offset = parseInt(req.query.offset) || 0; // Startindex
sql`SELECT * FROM products LIMIT ${limit} OFFSET ${offset};`.then(result => {
    res.send(result);
}).catch(err => {
    console.log(err);
    res.send([]);
});
*/
const products = (req, res) => {
    sql`SELECT * FROM products;`.then(result => {
        res.send(result)
    }).catch(err => {
        console.log(err)
        res.send([])
    })
}

const searchProduct = (req, res) => {
    let query = "%" + (req.params.name || "") + "%"
    sql`SELECT * FROM products WHERE name LIKE ${query};`.then(result => {
        res.send(result)
    }).catch((err) => {
        console.log(err)
        res.send([])
    })
}

export { product, products, searchProduct }
