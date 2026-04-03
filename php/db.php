<?php 
    include_once 'configDB.php';
    class database{
        public $connection;

        public function connect(){
            $this->connection = new mysqli(DB_CONFIG['db_host'], DB_CONFIG['db_user'], DB_CONFIG['db_password'], DB_CONFIG['db_name']);
        }

        public function close(){
            $this->connection->close();
        }

        private function generateRandomID($length = 10) {
            return strtoupper(substr(bin2hex(random_bytes($length)), 0, $length));
        }

        public function idExists($id) {
            $sql = "SELECT id FROM products WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        }

        public function insertProduct($name, $price, $description,$imageUrl, $inStock = 1){
            do {
                $id = $this->generateRandomID();
            } while ($this->idExists($id));
            $sql = "INSERT INTO products (id,productName,price,description,imageUrl,inStock) VALUES (?,?,?,?,?,?)";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('ssdssi',$id, $name, $price, $description, $imageUrl, $inStock);
            return $stmt->execute();
        }

        public function getProducts(){
            $sql = "SELECT id,productName,price,description,imageUrl,inStock FROM products";
            $result = $this->connection->query($sql);
            return $result;
        }

        public function deleteProduct($id){
            $query = "DELETE FROM products WHERE id = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('s',$id);
            $stmt->execute();
        }

        /**
         * Sistemare db
         * tabella separata per gli indirizzi
         * 
         * TODO: inserire salvataggio variabile isAdmin
         */
        public function registration($username, $password, $name, $surname, $email, $phoneNumber){
            $hashedPassword = hash('sha256',$password);
            $sql = "INSERT INTO users (username, password, name, surname, email, phoneNumber) VALUES (?,?,?,?,?,?)";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('ssssss', $username, $hashedPassword, $name, $surname, $email, $phoneNumber);
            $stmt->execute();

            $result = $stmt->get_result();
            if($result) return true;
            else return false;
        }

        public function login($email,$password){
            $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
            $stmt = $this->connection->prepare($sql);
            $password = hash('sha256',$password);
            $stmt->bind_param('ss',$email,$password);
            $stmt->execute();

            $result = $stmt->get_result();
            if($result->num_rows > 0){
                return $result->fetch_assoc();
            }
            else{
                return false;
            }
        }

        public function modifyProduct($id, $name, $price, $description, $imageUrl, $inStock){
            $sql = "UPDATE products SET productName = ?, price = ?, description = ?, imageUrl = ?, inStock = ? WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('sdssis',$name, $price, $description,$imageUrl,$inStock,$id);
            return $stmt->execute();
        }

        public function getProductById($id){
            $sql = "SELECT id,productName,price,description,imageUrl,inStock FROM products WHERE id = ?";
            $stmt =  $this->connection->prepare($sql);
            $stmt->bind_param('s', $id);
            $stmt->execute();
            return $stmt->get_result();
        }

        public function getOrderHistory($username){
            $sql = "SELECT o.orderID, o.orderDate, p.productName, p.price, op.quantity
                    FROM orders o
                    JOIN order_items op ON o.orderID = op.orderID
                    JOIN products p ON op.product_id = p.id
                    WHERE o.user = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            return $stmt->get_result();
        }

        public function getUserInfo($username){
            $sql = "SELECT username, name, surname, email, phoneNumber, street, city, postalCode FROM users WHERE username = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            return $stmt->get_result();
        }

        public function modifyUserInfo($username, $name, $surname, $email, $phoneNumber, $street, $city, $postalCode){
            $sql = "UPDATE users SET name = ?, surname = ?, email = ?, phoneNumber = ?, street = ?, city = ?, postalCode = ? WHERE username = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('ssssssss', $name, $surname, $email, $phoneNumber, $street, $city, $postalCode, $username);
            return $stmt->execute();
        }

        public function changePassword($username, $newPassword){
            $hashedPassword = hash('sha256', $newPassword);
            $sql = "UPDATE users SET password = ? WHERE username = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('ss', $hashedPassword, $username);
            return $stmt->execute();
        }

        public function addToWishlist($username, $productId){
            $sql = "INSERT INTO wishlist (user, product_id) VALUES (?, ?)";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('ss', $username, $productId);
            return $stmt->execute();
        }

        public function getWishlist($username){
            $sql = "SELECT p.productName, p.price, p.description
                    FROM wishlist w
                    JOIN products p ON w.product_id = p.id
                    WHERE w.user = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            return $stmt->get_result();
        }
    }
?>
