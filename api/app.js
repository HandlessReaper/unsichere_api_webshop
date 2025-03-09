import express from "express"
import root from "./routes/root.js"
import { product, products, searchProduct } from "./routes/product.js"
import { orderProducts, viewOrders, viewOrder } from "./routes/order.js"
import swagger from "swagger-ui-express"
import yaml from "js-yaml"
import fs from "fs"
import { getAllReviews, getUserReview, createReview } from "./routes/bewertungen.js";
import { register, login, getUserinfo, deleteUser } from "./routes/users.js";
import { get } from "http"


const yamlFile = fs.readFileSync('swagger.yml', 'utf8');
const swaggerSpec = yaml.load(yamlFile);

var app = express()

app.use("/docs", swagger.serve, swagger.setup(swaggerSpec))

app.use(express.json())

app.get("/", root)
app.get("/products", products)
app.get("/products/:id", product)
app.get("/products/search/:name", searchProduct)
app.post("/users/login", login)
app.post("/users/register", register)
app.post("/order", orderProducts)
app.get("/orders", viewOrders)
app.get("/orders/:id", viewOrder)
app.get("/reviews/product/:productId", getAllReviews);
app.get("/reviews/user/:userId", getUserReview);  
app.post("/reviews", createReview);
app.get("/users/:userId", getUserinfo);
app.delete("/users/:userId", deleteUser)


const PORT = process.env.API_PORT || 8080
app.listen(PORT, () => {
  console.log("Started listening on port:", PORT);
})