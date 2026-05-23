DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS address;


CREATE TABLE address (
    addressID varchar(10) PRIMARY KEY,
    address varchar(50),
    city varchar(25),
    cap varchar(10),
    province varchar(10),
    state varchar(25)
);

INSERT INTO address(addressID, address, city, cap, province, state) VALUES
('AB0102', 'Via Giuseppe Verdi 23', 'Milano', '25001', 'MI', 'Italy');

CREATE TABLE users (
    username varchar(25) PRIMARY KEY,
    password varchar(64) NOT NULL,
    surname varchar(50) NOT NULL,
    name varchar(50) NOT NULL,
    email varchar(100) NOT NULL,
    phoneNumber varchar(20),
    isAdmin boolean NOT NULL DEFAULT FALSE,
    addressId varchar(10),
    FOREIGN KEY (addressId) REFERENCES address(addressId) ON DELETE CASCADE
);

INSERT INTO users(username,password,name,surname,email,phoneNumber,isAdmin) VALUES
('admin','8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918','Davide','Rossi','admin@gmail.com','+391562145632', TRUE),
('user','04f8996da763b7a969b1028ee3007569eaf3a635486ddab211d512c85b9df8fb','Daniele','Bortoli','user@gmail.com','+391512631544', FALSE);

CREATE TABLE products(
    id char(10) PRIMARY KEY,
    productName varchar(40) NOT NULL,
    price numeric(7,2) NOT NULL,
    description varchar(200),
    imageUrl varchar(255),
    inStock boolean NOT NULL DEFAULT TRUE
);

INSERT INTO products(id,productName,price,description,imageUrl) VALUES
('A7B9C0D3E1','Sapphire Glow',14.99,'A stunning blend of modern design and eco-friendly craftsmanship. This handcrafted table lamp is upcycled from the iconic, azure-blue Bombay Sapphire gin bottle, beautifully enhanced by delicate fairy LED lights glowing inside. Topped with a crisp, cream-colored drum shade, it diffuses a soft, welcoming light that accentuates its vibrant glass. It’s the perfect accent piece to bring a touch of eclectic style to your living room, home bar, or bedside table.','sapphire_glow.jpg'),
('F5G8H2J4K6','Hendricks Heritage',14.99,'Designed for those who appreciate refined details and a vintage aesthetic. This custom lamp reinterprets the classic, clean silhouette of a Hendricks bottle, adding a magical touch with an intricate string of warm micro-LEDs inside the glass. Paired with a neutral linen shade, it creates a cozy, ambient illumination that elevates any corner of your home. A unique statement piece that effortlessly sparks conversation.','hendricksHeritage.jpg'),
('JD7SKEL098','Nox Midnight',14.99,'For those who prefer their decor with a dramatic, mysterious edge. This striking lamp pairs a rich, deep-purple Nox Gin bottle with a sophisticated matte-black shade for a moody, focused lighting effect. Inside, the tiny fairy lights sparkle like a personal constellation against the dark glass, creating a mesmerizing color contrast. Its an ideal choice for contemporary spaces, executive desks, or adding a sleek "industrial chic" vibe to your room.','noxMidnight.jpg'),
('KSJGIE8685','Frost & Amber',17.99,'Bring warmth and cozy textures into your space with this beautifully upcycled frosted wine bottle lamp. The satin-finish glass gently diffuses a cascade of warm amber fairy lights inside, creating a dreamy, candle-like glow. Featuring a beautifully textured white twine-wrapped neck, a classic tapered fabric shade, and a vintage-style twisted fabric cord with a rustic plug, this piece is the ultimate addition to farmhouse, coastal, or bohemian interior decor.','frostAndAmber.jpg'),
('84KD8F7GLH','Frost & Starlight',17.99,'A sleek, cool-toned twin to our warmer design, this artisan lamp uses a frosted glass wine bottle to house a glittering array of bright white and cool LED fairy lights. The frosted finish softens the starlight effect inside, while the white twine wrapping on the neck adds a subtle touch of organic texture. Topped with a clean white fabric shade and grounded by a vintage-inspired twisted cord, it provides a bright, refreshing ambiance perfect for modern, Scandinavian, or minimalist spaces.','frostAndStarlight.jpg'),
('S84IT09909','Cobalt Geometric',17.99,'Bold, structural, and undeniably striking. This industrial-style accent lamp is crafted from a vivid, deep cobalt blue glass bottle with a smooth matte finish. Stepping away from traditional fabric shades, it features an edgy, black iron geometric wire cage that perfectly frames an exposed filament Edison-style bulb. Delivering a clean, unfiltered warm light and an eye-catching architectural silhouette, this piece is a standout choice for urban lofts, modern studios, or anyone looking to make a confident style statement.','cobaltGeometric.jpg'),
('SLOIUAMJG0','Diamond Starlight',18.99,'A captivating mix of organic textures and sharp, modern geometries. This handcrafted lamp features a clear, square-shaped glass bottle filled to the brim with glittering cool-white fairy lights. The neck is elegantly wrapped in natural jute rope, leading up to a sleek black wire diamond cage that frames a warm, glowing Edison bulb. Complete with a vintage-style twisted green fabric cord and a rustic plug, its a stellar accent piece to brighten up any rustic or industrial interior.','diamondStarlight.jpg'),
('SKAJSOEL9K','Botanical Berry',18.99,'Bring a touch of the countryside indoors with this visually stunning botanical lamp. The clear rectangular glass base is carefully packed with dried berries, lavender sprigs, and rich purple botanicals, offering an intricate display of deep indigo tones. The bottles neck is finished with a natural twine wrap, topped with a massive, warm-tinted globe Edison bulb that casts a gentle, vintage glow over the natural textures below. Perfect for adding a peaceful, organic vibe to your desk, bookshelf, or vanity.','botanicalBerry.jpg'),
('HSKR6574IH','Citrus & Spice',18.99,'Celebrate natural aesthetics with this exquisite, sensory-rich design. This custom-made bottle lamp is artfully filled with a dried arrangement of citrus slices—including vibrant orange and lemon wheels—complemented by deep, earthy juniper berries and hints of rosemary. A neat wrap of jute rope sits beneath a brass fixture, holding an oversized, exposed-filament globe bulb. Its amber radiance beautifully illuminates the detailed textures inside the glass, making it an ideal cozy addition to a kitchen, dining area, or living room hearth.','citrusAndSpice.jpg'),
('LALOIO0909','Amber Vine',20.99,'Stripped back to the essentials of organic industrial design. This table lamp is crafted from a classic olive-green wine bottle, allowing its deep, earthy tones to take center stage. The neck features a thick, neatly wrapped jute rope collar that adds a rustic, tactile contrast to the smooth glass. Crowned with an exposed globe Edison bulb displaying a stunning spiral filament, it casts an inviting, golden amber light while proudly showcasing its internal black wiring for a sleek, honest design.','amberVine.jpg'),
('HSJGUTYH65','Ocean Velvet',20.99,'A beautiful exploration of contrasting color blocks. This striking accent lamp features a vibrant turquoise-blue glass bottle base that immediately draws the eye. Its neck is wrapped in a textured, deep crimson red felt collar, offering a bold and unexpected pop of color and softness. Complete with a large spiral-filament Edison bulb and a clean black cord passing straight through the base, it provides an artful, contemporary light source for modern spaces.','oceanVelvet.jpg'),
('AAEW34QRSD','Denim & Glass',20.99,'Perfect for a casual, urban loft aesthetic or contemporary minimalist decor. This lamp relies on a perfectly transparent clear glass bottle, putting the clean lines of the internal wiring and structural silhouette on full display. The neck is dressed in a unique, dark indigo denim fabric wrap with visible topstitching, lending a relaxed, stylish edge to the piece. An oversized spiral Edison bulb sits up top, throwing a warm, nostalgic glow across the clean glass below.','denimAndGlass.jpg');


CREATE TABLE orders(
    orderID char(10) PRIMARY KEY,
    user varchar(25) NOT NULL,
    orderDate TIMESTAMP NOT NULL,
    totalAmount numeric(9,2) NOT NULL,
    FOREIGN KEY (user) REFERENCES users(username) ON DELETE CASCADE
);

INSERT INTO orders(orderID,user,orderDate,totalAmount) VALUES
('A7B9DS3E1','user',NOW(),29.98),
('GDFFDJ4K6','user',NOW(),14.99);

CREATE TABLE order_items(
    id int AUTO_INCREMENT PRIMARY KEY,
    orderID varchar(20) NOT NULL,
    product_id char(10) NOT NULL,
    quantity smallint NOT NULL,
    FOREIGN KEY (orderID) REFERENCES orders(orderID) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

INSERT INTO order_items(orderID,product_id,quantity) VALUES
('A7B9DS3E1','A7B9C0D3E1',2),
('GDFFDJ4K6','F5G8H2J4K6',1);


CREATE TABLE wishlist(
    id int AUTO_INCREMENT PRIMARY KEY,
    user varchar(25) NOT NULL,
    product_id char(10) NOT NULL,
    FOREIGN KEY (user) REFERENCES users(username) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

INSERT INTO wishlist(user,product_id) VALUES
('user','A7B9C0D3E1');

