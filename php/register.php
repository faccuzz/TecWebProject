<?php
include_once 'db.php';
session_start();

header('Content-Type: application/json');

$inputData = json_decode(file_get_contents("php://input"), true);

if (!empty($inputData)) {
    $username    = $inputData['username'];
    $password    = $inputData['password'];
    $name        = $inputData['name'];
    $surname     = $inputData['surname'];
    $email       = $inputData['email'];
    $phoneNumber = $inputData['phone'];

    $isAdmin = 0;
    if(isset($inputData['isAdmin'])) {
        if($inputData['isAdmin'] === true || $inputData['isAdmin'] === "true" || $inputData['isAdmin'] === 1 || $inputData['isAdmin'] === "1") {
            $isAdmin = 1;
        }
    }

    if(isset($inputData['street']) && isset($inputData['city']) && isset($inputData['postalCode']) && isset($inputData['province']) && isset($inputData['state'])) {
        $address = $inputData['street'];
        $city = $inputData['city'];
        $cap = $inputData['postalCode'];
        $province = $inputData['province'];
        $state = $inputData['state'];
    } else {
        $address = null;
        $city = null;
        $cap = null;
        $province = null;
        $state = null;
    }

    $db = new database();
    $db->connect();
    $result = $db->registration($username, $password, $name, $surname, $email, $phoneNumber, $isAdmin, $address, $city, $cap, $province, $state);
    

    if ($result) {
        if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 1) {
            echo json_encode(["success" => true, "stayOnPage" => true]);
            $db->close();
            exit();
        } else {
            $_SESSION['email'] = $email;
            $_SESSION['isAdmin'] = $isAdmin;
            session_write_close();
            echo json_encode(["success" => true, "stayOnPage" => false]);
            $db->close();
            exit();
        }
    } else {
        echo json_encode(["success" => false, "message" => "Registration failed. Please try again."]);
        $db->close();
        exit();
    }
}
else{
    echo json_encode(["success" => false, "message" => "No data received"]);
    $db->close();
    exit();
} 
?>