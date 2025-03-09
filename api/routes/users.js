import sql from "../db.js"
import { createJWT, getBearerToken, JwtCreationError, NoTokenError, verifyJWT } from "../jwt.js"

class UserNotFoundError extends Error {
    constructor(message) {
        super(message)
        this.name = "UserNotFoundError"
    }
}

class UserAlreadyExistsError extends Error {
    constructor(message) {
        super(message)
        this.name = "UserAlreadyExistsError"
    }
}

class InvalidPasswordError extends Error {
    constructor(message) {
        super(message)
        this.name = "InvalidPasswordError"
    }
}

const login = (req, res) => {
  let user; 
  sql`SELECT * FROM users WHERE username = ${req.body.username}`
    .then((result) => {
        if (result.length <= 0) {
            throw new UserNotFoundError("User not found")
        } else if (result.length > 1) {
            throw new Error("Database error!")
        } else if (result[0].password !== req.body.password) {
            throw new InvalidPasswordError("Invalid password")
        }
        user = result[0]
        return createJWT(user)
    }).then((token) => {
      res.status(200).send({"token": token, ...user});
    }).catch((err) => {
      if (err instanceof UserNotFoundError) {
          res.status(404).send();
          return
      }
      if (err instanceof InvalidPasswordError) {
          res.status(401).send();
          return
      }
      console.log(err);
      res.status(500).send();
    });
}

const register = (req, res) => {
  let user;
  sql`SELECT * FROM users WHERE username = ${req.body.username} OR email = ${req.body.email}`
    .then((result) => {
        if (result.length > 0) {
            throw new UserAlreadyExistsError("User already exists")
        }
        return sql`INSERT INTO users (username, email, password) VALUES (${req.body.username}, ${req.body.email}, ${req.body.password}) RETURNING *`
    }).then((result) => {
      user = result[0]
      return createJWT(user)
    }).then((token) => {
      res.status(201).send({"token": token, ...user});
    }).catch((err) => {
      if (err instanceof UserAlreadyExistsError) {
          res.status(409).send();
          return
      }
      console.log(err);
      res.status(500).send();
    });
}

const getUserinfo = (req, res) => {
    let userId = req.params.userId;
    let token_userId;
    let token = getBearerToken(req)
    if (token == null) {
        sql `SELECT username FROM users WHERE id = ${userId}`.then((result) => {
            if (result.length <= 0) {
                throw new UserNotFoundError("User not found")
            }
            res.status(200).send(result[0])
        }).catch((err) => {
            if (err instanceof UserNotFoundError) {
                res.status(404).send();
                return
            }
            console.log(err);
            res.status(500).send();
        });
    }else {
        verifyJWT(token).then((payload) => {
            token_userId = payload.user_id
    
            if (token_userId == userId) {
                return sql `SELECT * FROM users WHERE id = ${userId}`
            }
            else {
                return sql `SELECT username FROM users WHERE id = ${userId}`
            }
        }).then((result) => {
            if (result.length <= 0) {
                throw new UserNotFoundError("User not found")
            }
            res.status(200).send(result[0])
        }).catch((err) => {
            if (err instanceof UserNotFoundError) {
                res.status(404).send();
                return
            }
            console.log(err);
            res.status(500).send();
        });
    }
}

const deleteUser = (req, res) => {
    let userId = req.params.userId
    let token = getBearerToken(req)
    verifyJWT(token).then((payload) => {
        if (payload.user_id == userId) {
            let queries = [
                sql`DELETE FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE user_id = ${userId})`,
                sql`DELETE FROM orders WHERE user_id = ${userId}`,
                
                sql`DELETE FROM reviews WHERE user_id = ${userId}`,

                sql`DELETE FROM users WHERE id = ${userId}`
            ]

            Promise.all(queries).then(() => {
                res.status(204).send()
            }).catch((err) => {
                console.log(err);
                res.status(500).send();
            });
        } else {
            res.status(403).send("Unauthorized");
        }
    }).catch((err) => {
        if (err instanceof NoTokenError) {
            res.status(401).send("Unauthorized");
            return
        }
        console.log(err);
        res.status(500).send();
    });
}


export { login, register, getUserinfo , deleteUser }

