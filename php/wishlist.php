<?php
include_once 'db.php';
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['email'])) {
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if ($data && isset($data['product_id'])) {
    $productId = $data['product_id'];
    $email = $_SESSION['email'];

    $db = new database();
    $db->connect();

    $userInfoResult = $db->getUserInfo($email);
    
    if ($userInfoResult && $userInfoResult->num_rows > 0) {
        $username = $userInfoResult->fetch_assoc()['username'];
        $success = $db->addToWishlist($username, $productId);
        $db->close();
    }
}
?>