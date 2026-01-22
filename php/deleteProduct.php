<?php 
    include_once 'db.php';
    session_start();
    if(isset($_SESSION['email']) && $_SESSION['email'] !== 'admin@gmail.com'){
        header("Location: ../index.html");
        exit();
    }
    $db = new database();
    if(isset($_GET['id'])){
        $id = $_GET['id'];
        $db->connect();
        $db->deleteProduct($id);
        $db->close();

        header("Location: ../adminPage.html");
        exit();
    }
?>