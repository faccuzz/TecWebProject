<?php
include_once '../db.php';
include_once '../session_bootstrap.php';
header('Content-Type: application/json');

if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== 1) {
    echo json_encode(["error" => "Accesso Negato"]);
    exit();
}

if (isset($_POST['submit'])) {
    $username = '';
    if (isset($_POST['username'])) $username = trim($_POST['username']);

    if (strlen($username) < 3 || strlen($username) > 32 || !preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Username non valido"]);
        exit();
    }

    $db = new database();
    $db->connect();
    $result = $db->makeAdmin($username);
    $db->close();
    
    if($result){
        header("Location: ../../optionsPage.html");
        exit();
    }

    echo json_encode($result ? ["success" => true] : ["success" => false]);
}
?>