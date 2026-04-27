<?php
session_start();
header('Content-Type: application/json');
include_once 'db.php';

$db = new database();
$db->connect();

$totalPrice = 0;

foreach ($_SESSION['cart'] as $id => $qty) {
    $result = $db->getProductById($id);

    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();

        $totalPrice += ($product['price'] * $qty);
    }
}

$db->close();

echo json_encode(["totalPrice" => $totalPrice]);

?>