import * as jose from "jose"

// Disclaimer not really secret
const secret = Buffer.from("secret")

// Errors
class InvalidTokenError extends Error {
  constructor(message) {
    super(message)
    this.name = "InvalidTokenError"
  }
}

class NoTokenError extends Error {
  constructor(message) {
    super(message)
    this.name = "NoTokenError"
  }
}

class JwtCreationError extends Error {
  constructor(message) {
    super(message)
    this.name = "JwtCreationError"
  }
}

const createJWT = async (user) => {
  try {
    return await new jose.SignJWT({ user_id: user.id })
    .setProtectedHeader({ alg: "HS256" })
    .setIssuedAt()
    .setExpirationTime("24h")
    .sign(secret)
  } catch (e) {
    throw new JwtCreationError(e.message)
  } 
}

const verifyJWT = async (token) => {
  if (!token) {
    throw new NoTokenError("No token provided")
  }
  try {
    const { payload } = await jose.jwtVerify(token, secret)
    return payload
  } catch (e) {
    throw new InvalidTokenError(e.message)
  }
}

const getBearerToken = (req) => {
  if (!req.headers.authorization) {
    return null
  }
  const parts = req.headers.authorization.split(" ")
  if (parts.length !== 2) {
    return null
  }
  if (parts[0] !== "Bearer") {
    return null
  }
  return parts[1]
}

export { createJWT, verifyJWT, getBearerToken, InvalidTokenError, NoTokenError, JwtCreationError };