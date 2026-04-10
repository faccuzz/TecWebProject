<?php
include_once 'db.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $username = $data['username'];
    $password = $data['password'];
    $name = $data['name'];
    $surname = $data['surname'];
    $email = $data['email'];
    $phoneNumber = $data['phone'];
    
    $db = new database();
    $db->connect();
    $result = $db->registration($username, $password, $name, $surname, $email, $phoneNumber);
    $db->close();

    if ($result) {
        session_regenerate_id(true);
        $_SESSION['email'] = $email;
        $_SESSION['is_admin'] = 0; //Default

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
} else {
    echo json_encode(["success" => false]);
}
?>