<?php
include_once 'db.php';
header('Content-Type: application/json');
session_start();

if (isset($_POST['rmvWishlist'])) {
    $id = $_POST['id'];
    $db = new database();
    $db->connect();
    $delete = $db->removeFromWishlist($id);

    $db->close();

    if($delete){
        $_SESSION['email'] = $email;
        header("Location: ../optionsPage.html");
        exit();
    }
}
?>