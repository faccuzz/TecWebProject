<?php 
    include_once 'configDB.php';
    class database{
        private $connection;

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

        public function insertProduct($name, $price, $description){
            do {
                $id = $this->generateRandomID();
            } while ($this->idExists($id));
            $sql = "INSERT INTO products (id,productName,price,description) VALUES (?,?,?,?)";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('ssds',$id, $name, $price, $description);
            return $stmt->execute();
        }

        public function getProducts(){
            $sql = "SELECT productName,price,description FROM products";
            $result = $this->connection->query($sql);
            return $result;
        }
    }
?>
