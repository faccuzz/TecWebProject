<?php
include_once 'db.php';
session_start();

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $db = new database();
    $db->connect();
    $result = $db->makeAdmin($username);

    if ($result) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
    $db->close();

    header("Location: ../optionsPage.html");
    exit();
} 
?>