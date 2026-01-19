USE prova;

DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS products;

CREATE TABLE users (
    username varchar(25) PRIMARY KEY,
    password varchar(64) NOT NULL,
    surname varchar(50) NOT NULL,
    name varchar(50) NOT NULL,
    email varchar(100) NOT NULL,
    phoneNumber varchar(20)
);

INSERT INTO users(username,password,name,surname,email,phoneNumber) VALUES
('admin','8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918','Davide','Rossi','admin@gmail.com','+391562145632'),
('user','04f8996da763b7a969b1028ee3007569eaf3a635486ddab211d512c85b9df8fb','Daniele','Bortoli','user@gmail.com','+391512631544');

CREATE TABLE products(
    id char(10) PRIMARY KEY,
    productName varchar(40) NOT NULL,
    price numeric(7,2) NOT NULL,
    description varchar(200)
);

INSERT INTO products(id,productName,price,description) VALUES
('A7B9C0D3E1','Green Bottle',14.99,'The newest bottle made of green materials, part of the new Elementals Collection'),
('F5G8H2J4K6','Fire Bottle',14.99,'The newest bottle made of fire? Be careful, part of the new Elementals Collection'),
('L1M3N5P7Q9','Ocean Bottle',14.99,'The newest bottle made in the deepest parts of th ocean, part of the new Elementals Collection'),
('R0S2T4U6V8','Earth Bottle',14.99,'The newest bottle made of Rocks? Nah, part of the new Elementals Collection'),
('W9X7Y5Z3A2','Wind Bottle',14.99,'The newest bottle that looks empty? look closer, part of the new Elementals Collection'),
('B4C6D8E0F1','2099 Bottle',17.99,'An bottle that came from the future'),
('G3H5J7K9L0','Old Texas Bottle',18.99,'For the ones who loves the Texas style'),
('M2N4P6Q8R7','Luxury Bottle',19.99,'An special product for people who like to spend a bit more on our products'),
('S1T3U5V7W9','Basket Bottle',12.99,'An bottle for you who watchs NBA a lot'),
('X0Y2Z4A6B5','Classic Bottle',13.99,'The oldest bottle ever made');

CREATE TABLE cart(
    product_id char(10) NOT NULL,
    quantity smallint(2) NOT NULL,
    date TIMESTAMP NOT NULL,
    user varchar(25) NOT NULL,
    PRIMARY KEY (product_id, user),
    FOREIGN KEY (user) REFERENCES users(username) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE orders(
    orderID char(10) PRIMARY KEY,
    user varchar(25) NOT NULL,
    orderDate TIMESTAMP NOT NULL,
    totalAmount numeric(9,2) NOT NULL,
    FOREIGN KEY (user) REFERENCES users(username) ON DELETE CASCADE
);

CREATE TABLE order_items(
    id int AUTO_INCREMENT PRIMARY KEY,
    orderID varchar(20) NOT NULL,
    product_id char(10) NOT NULL,
    quantity smallint NOT NULL,
    FOREIGN KEY (orderID) REFERENCES orders(orderID) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);


