import postgres from "postgres";

const sql = postgres({
  host: process.env.POSTGRES_HOST,
  port: process.env.POSTGRES_PORT,
  db: process.env.POSTGRES_DB,
  username: process.env.POSTGRES_USER,
  pass: process.env.POSTGRES_PASSWORD,
  connect_timeout: 5
})

export default sql