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
    description varchar(200),
    imageUrl varchar(255)
);

INSERT INTO products(id,productName,price,description,imageUrl) VALUES
('A7B9C0D3E1','Green Bottle',14.99,'The newest bottle made of green materials, part of the new Elementals Collection',''),
('F5G8H2J4K6','Fire Bottle',14.99,'The newest bottle made of fire? Be careful, part of the new Elementals Collection','');


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


