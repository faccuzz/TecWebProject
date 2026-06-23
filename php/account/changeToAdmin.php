<?php
include_once '../db.php';
include_once '../session_bootstrap.php';
header('Content-Type: application/json');

if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== 1) {
    echo json_encode(["error" => "Accesso Negato"]);
    exit();
}

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
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