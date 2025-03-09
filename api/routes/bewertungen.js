import sql from "../db.js"
import { verifyJWT, getBearerToken, NoTokenError, InvalidTokenError  } from "../jwt.js"

export const getAllReviews = (req, res) => {
  const productId = req.params.productId;
  sql`SELECT * FROM reviews WHERE product_id = ${productId}`.then((reviews) => {
    res.status(200).send(reviews);
  }).catch((err) => {
    console.log(err);
    res.status(500).send("Internal server error");
  });
};

export const getUserReview = (req, res) => {
  const userId = req.params.userId;
  sql`SELECT * FROM reviews WHERE user_id = ${userId}`.then((reviews) => {
    res.status(200).send(reviews);
  }).catch((err) => {
    console.log(err);
    res.status(500).send("Internal server error");
  });
};

export const createReview = (req, res) => {
  let token = getBearerToken(req)
  verifyJWT(token).then((payload) => {
    let productId = req.body.product_id;
    let userId = payload.user_id;
    let rating = req.body.rating || 0;
    let review_text = req.body.review_text || "";
    return sql`INSERT INTO reviews (product_id, user_id, rating, review_text) VALUES (${productId}, ${userId}, ${rating}, ${review_text}) RETURNING *`
  }).then((review) => {
    res.status(200).send(review[0]);
  }).catch((err) => {
    if (err instanceof NoTokenError || err instanceof InvalidTokenError) {
      res.status(401).send("Unauthorized")
      return
    }
    console.log(err);
    res.status(500).send("Internal server error");
  });
};
