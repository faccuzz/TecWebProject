<?php
include_once 'db.php';
header('Content-Type: application/json');

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $db = new database();
    $db->connect();

    $product = $db->getProductById($id);
    $db->close();
    $data = $product->fetch_assoc();
    echo json_encode($data);
}
?>