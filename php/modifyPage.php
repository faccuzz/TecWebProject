<?php 
    include_once 'db.php';
    header('Content-Type: application/json');
    error_reporting(E_ALL);
    
    session_start();
    if(!isset($_SESSION['email']) || $_SESSION['email'] !== 'admin@gmail.com'){
        header("Location: ../index.html");
        exit();
    }
    
    $id = $_GET['id'];
    $db = new database();
    $db->connect();
    $result = $db->getproductById($id);
    echo json_encode($result->fetch_assoc());
    $db->close();
    exit();
?>
