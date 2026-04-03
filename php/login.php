<?php
include_once 'db.php';
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $email = $data['email'];
    $password = $data['password'];

    $db = new database();
    $db->connect();

    $userInfo = $db->login($email, $password);
    $db->close();

    if ($userInfo) {
        session_start();

        //Anti Session Hijacking
        session_regenerate_id(true);

        $_SESSION['email'] = $userInfo['email'];
        $_SESSION['is_admin'] = $userInfo['is_admin'];

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
} else {
    echo json_encode(["success" => false]);
}
?>