<?php
include_once 'configDB.php';
class database
{
    public $connection;
    public $lastError = null;

    public function connect()
    {
        // disattivo i warning di mysqli cosi gestisco l'errore a mano
        // e posso ritornare un JSON pulito al client
        mysqli_report(MYSQLI_REPORT_OFF);
        $this->connection = @new mysqli(
            DB_CONFIG['db_host'],
            DB_CONFIG['db_user'],
            DB_CONFIG['db_password'],
            DB_CONFIG['db_name']
        );
        if ($this->connection && $this->connection->connect_errno) {
            $this->lastError = $this->connection->connect_error;
            $this->connection = null;
            return false;
        }
        return $this->connection !== null && $this->connection !== false;
    }

    public function isConnected()
    {
        return $this->connection !== null && $this->connection !== false;
    }

    public function close()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    private function generateRandomID($length = 10)
    {
        return strtoupper(substr(bin2hex(random_bytes($length)), 0, $length));
    }

    public function idExists($id)
    {
        $sql = "SELECT id FROM products WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function addressidExists($addressid)
    {
        $sql = "SELECT addressID FROM address WHERE addressID = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('s', $addressid);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function insertProduct($name, $price, $description, $imageUrl, $inStock = 1,
                                  $material = '', $author = '', $dimensions = '', $weight = '', $voltage = '')
    {
        do {
            $id = $this->generateRandomID();
        } while ($this->idExists($id));
        $sql = "INSERT INTO products
                  (id, productName, price, description, imageUrl, material, author, dimensions, weight, voltage, inStock)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            return "Errore Query: " . $this->connection->error;
        }
        $stmt->bind_param(
            'ssdsssssssi',
            $id, $name, $price, $description, $imageUrl,
            $material, $author, $dimensions, $weight, $voltage,
            $inStock
        );
        if (!$stmt->execute()) {
            return "Errore Dati: " . $stmt->error;
        }

        return true;
    }

    public function getProducts()
    {
        $sql = "SELECT id, productName, price, description, imageUrl,
                       material, author, dimensions, weight, voltage, inStock
                FROM products";
        $result = $this->connection->query($sql);
        return $result;
    }

    public function deleteProduct($id)
    {
        $queryItems = "DELETE FROM order_items WHERE product_id = ?";
        $stmtItems = $this->connection->prepare($queryItems);
        $stmtItems->bind_param('s', $id);
        $stmtItems->execute();

        $query = "DELETE FROM products WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('s', $id);
        $stmt->execute();
    }

    public function registration($username, $password, $name, $surname, $email, $phoneNumber, $isAdmin, $address, $city, $cap, $province, $state)
    {
        do {
            $addressid = $this->generateRandomID();
        } while ($this->addressidExists($addressid));
        
        $sqlAddress = "INSERT INTO address (addressID,address, city, cap, province, state) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtAddress = $this->connection->prepare($sqlAddress);
        $stmtAddress->bind_param('ssssss', $addressid, $address, $city, $cap, $province, $state);
        $stmtAddress->execute();

        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, name, surname, email, phoneNumber, isAdmin, addressId) VALUES (?,?,?,?,?,?,?,?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('ssssssis', $username, $hashedPassword, $name, $surname, $email, $phoneNumber, $isAdmin, $addressid);
        $result = $stmt->execute();

        return $result;
    }

    public function login($email, $password)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }

    public function modifyProduct($id, $name, $price, $description, $imageUrl, $inStock,
                                  $material = '', $author = '', $dimensions = '', $weight = '', $voltage = '')
    {
        $sql = "UPDATE products
                SET productName = ?, price = ?, description = ?, imageUrl = ?,
                    material = ?, author = ?, dimensions = ?, weight = ?, voltage = ?,
                    inStock = ?
                WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'sdsssssssis',
            $name, $price, $description, $imageUrl,
            $material, $author, $dimensions, $weight, $voltage,
            $inStock, $id
        );
        return $stmt->execute();
    }

    public function modifyProductWithoutImage($id, $name, $price, $description, $inStock,
                                              $material = '', $author = '', $dimensions = '', $weight = '', $voltage = '')
    {
        $sql = "UPDATE products
                SET productName = ?, price = ?, description = ?,
                    material = ?, author = ?, dimensions = ?, weight = ?, voltage = ?,
                    inStock = ?
                WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'sdssssssis',
            $name, $price, $description,
            $material, $author, $dimensions, $weight, $voltage,
            $inStock, $id
        );
        return $stmt->execute();
    }

    // compone la stringa delle dimensioni partendo da larghezza e altezza in cm
    public static function formatDimensions($width, $height)
    {
        $w = is_numeric($width)  ? (float)$width  : null;
        $h = is_numeric($height) ? (float)$height : null;
        if ($w === null || $h === null || $w <= 0 || $h <= 0) {
            return '';
        }
        // se è intero lo scrivo senza virgola, sennò con la virgola (formato italiano)
        $fmt = function ($n) {
            if (floor($n) == $n) return (string)(int)$n;
            return rtrim(rtrim(number_format($n, 2, ',', ''), '0'), ',');
        };
        return "Ø " . $fmt($w) . " cm × H " . $fmt($h) . " cm";
    }

    public static function validateAndMoveImage($image, $name, $destinationFolder)
    {
        if ($image['size'] > 2 * 1024 * 1024)
            return ['error' => 'Immagine troppo grande'];
        if (!in_array($image['type'], ['image/jpg', 'image/jpeg', 'image/png', 'image/webp']))
            return ['error' => 'Formato non valido'];
        $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
        $imageName = strtolower(preg_replace('/\s+/', '', $name)) . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($image['tmp_name'], $destinationFolder . $imageName))
            return ['error' => 'Errore nel caricamento del file sul server'];
        return ['success' => true, 'image_name' => $imageName];
    }

    public function getProductById($id)
    {
        $sql = "SELECT id, productName, price, description, imageUrl,
                       material, author, dimensions, weight, voltage, inStock
                FROM products WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getOrderHistory($email)
    {
        $sql = "SELECT o.orderID, o.orderDate, p.productName, p.price, op.quantity
                    FROM orders o
                    JOIN order_items op ON o.orderID = op.orderID
                    JOIN products p ON op.product_id = p.id
                    JOIN users u ON o.user = u.username
                    WHERE u.email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getUserInfo($email)
    {
        $sql = "SELECT username,name, surname, email, phoneNumber FROM users WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function modifyUserInfo($email,$name, $surname, $newemail, $phoneNumber)
    {
        $sql = "UPDATE users SET name = ?, surname = ?, email = ?, phoneNumber = ? WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('sssss', $name, $surname, $newemail, $phoneNumber, $email);
        return $stmt->execute();
    }

    public function changePassword($email, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('ss', $hashedPassword, $email);
        return $stmt->execute();
    }

    public function getUsers()
    {
        $sql = "SELECT * FROM users";
        $result = $this->connection->query($sql);
        return $result;
    }

    public function isAdmin($username)
    {
        $sql = "SELECT isAdmin FROM users WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function makeAdmin($username){
        $sql = "UPDATE users SET isAdmin = 1 WHERE username = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('s', $username);
        return $stmt->execute();
    }
}
?>