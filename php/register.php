<?php
include_once 'db.php';
session_start();

if (!empty($_POST)) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $phoneNumber = $_POST['phoneNumber'];
    $isAdmin = $_POST['isAdmin'];
    $address = $_POST['street'];
    $city = $_POST['city'];
    $cap = $_POST['postalCode'];
    $province = $_POST['province'];
    $state = $_POST['state'];

    if($isAdmin === 'true') {
        $isAdmin = 1;
    } else {
        $isAdmin = 0;
    }

    $db = new database();
    $db->connect();
    $result = $db->registration($username, $password, $name, $surname, $email, $phoneNumber, $isAdmin, $address, $city, $cap, $province, $state);
    

    if ($result) {
        session_regenerate_id(true);
        $_SESSION['email'] = $email;
        $_SESSION['is_admin'] = $isAdmin;

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
    $db->close();
    header("Location: ../optionsPage.html");
    exit();
} 
?>