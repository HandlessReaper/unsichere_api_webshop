-- Initialisierung der Datenbank

CREATE TABLE users 
(
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, email, password) 
    VALUES 
        ('admin', 'admin@example.com', 'passwort'),
        ('user2', 'user2@example.com', 'passwort'),
        ('user3', 'user3@example.com', 'passwort'),
        ('user4', 'user4@example.com', 'passwort'),
        ('user5', 'user5@example.com', 'passwort'),
        ('user6', 'user6@example.com', 'passwort')
;
        

CREATE TABLE products 
(
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT '',
    preis DECIMAL(10, 2) NOT NULL,
    thumbnail TEXT,
    rabatt SMALLINT DEFAULT 0,
    stock SMALLINT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (name, description, preis, thumbnail) 
    VALUES 
        ('Bachelor Arbeit (1-20 Seiten)', 'Wir schreiben Ihre Bachelorarbeit für Sie! Einfach bestellen und entspannen.', 200.00, 'https://www.rmsdruck.de/site/assets/files/2314/bachelorarbeit_quer.1200x800.jpg'),
        ('Bachelor Arbeit (21-40 Seiten)', 'Wir schreiben Ihre Bachelorarbeit für Sie! Einfach bestellen und entspannen.', 300.00, 'https://www.rmsdruck.de/site/assets/files/2314/bachelorarbeit_quer.1200x800.jpg'),
        ('Bachelor Arbeit (41-60 Seiten)', 'Wir schreiben Ihre Bachelorarbeit für Sie! Einfach bestellen und entspannen.', 400.00, 'https://www.rmsdruck.de/site/assets/files/2314/bachelorarbeit_quer.1200x800.jpg'),
        ('Master Arbeit (1-20 Seiten)', 'Wir schreiben Ihre Masterarbeit für Sie! Einfach bestellen und entspannen.', 300.00, 'https://www.bachelorprint.de/wp-content/uploads/2017/08/Masterarbeit-Bild-1-gro%C3%9F.jpg'),
        ('Master Arbeit (21-40 Seiten)', 'Wir schreiben Ihre Masterarbeit für Sie! Einfach bestellen und entspannen.', 400.00, 'https://www.bachelorprint.de/wp-content/uploads/2017/08/Masterarbeit-Bild-1-gro%C3%9F.jpg'),
        ('Master Arbeit (41-60 Seiten)', 'Wir schreiben Ihre Masterarbeit für Sie! Einfach bestellen und entspannen.', 500.00, 'https://www.bachelorprint.de/wp-content/uploads/2017/08/Masterarbeit-Bild-1-gro%C3%9F.jpg'),
        ('Dissertation (1-20 Seiten)', 'Wir schreiben Ihre Dissertation für Sie! Einfach bestellen und entspannen.', 400.00, 'https://www.bachelorprint.de/wp-content/uploads/2017/08/Dissertation-Bild-2-gro%C3%9F.jpg'),
        ('Dissertation (21-40 Seiten)', 'Wir schreiben Ihre Dissertation für Sie! Einfach bestellen und entspannen.', 500.00, 'https://www.bachelorprint.de/wp-content/uploads/2017/08/Dissertation-Bild-2-gro%C3%9F.jpg'),
        ('Dissertation (41-60 Seiten)', 'Wir schreiben Ihre Dissertation für Sie! Einfach bestellen und entspannen.', 600.00, 'https://www.bachelorprint.de/wp-content/uploads/2017/08/Dissertation-Bild-2-gro%C3%9F.jpg');
;

CREATE TABLE reviews (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    product_id INT REFERENCES products(id) ON DELETE CASCADE,
    rating SMALLINT NOT NULL,
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO reviews (user_id, product_id, rating, review_text)
    VALUES
        (1, 1, 5, 'Super Arbeit!'),
        (2, 1, 4, 'Gute Arbeit!'),
        (3, 1, 3, 'Nicht schlecht!'),
        (4, 1, 2, 'Nicht so gut!'),
        (5, 1, 1, 'Schlecht!'),
        (6, 1, 5, 'Super Arbeit!'),
        (1, 2, 5, 'Super Arbeit!'),
        (2, 2, 4, 'Gute Arbeit!'),
        (3, 2, 3, 'Nicht schlecht!'),
        (4, 2, 2, 'Nicht so gut!'),
        (5, 2, 1, 'Schlecht!'),
        (6, 2, 5, 'Super Arbeit!'),
        (1, 3, 5, 'Super Arbeit!'),
        (2, 3, 4, 'Gute Arbeit!'),
        (3, 3, 3, 'Nicht schlecht!'),
        (4, 3, 2, 'Nicht so gut!'),
        (5, 3, 1, 'Schlecht!'),
        (6, 3, 5, 'Super Arbeit!'),
        (1, 4, 5, 'Super Arbeit!'),
        (2, 4, 4, 'Gute Arbeit!'),
        (3, 4, 3, 'Nicht schlecht!'),
        (4, 4, 2, 'Nicht so gut!'),
        (5, 4, 1, 'Schlecht!'),
        (6, 4, 5, 'Super Arbeit!'),
        (1, 5, 5, 'Super Arbeit!'),
        (2, 5, 4, 'Gute Arbeit!'),
        (3, 5, 3, 'Nicht schlecht!'),
        (4, 5, 2, 'Nicht so gut!'),
        (5, 5, 1, 'Schlecht!')
;

CREATE TABLE orders
(
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items
(
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

