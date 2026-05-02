<?php 
    include_once 'db.php';
    header('Content-Type: application/json');
    error_reporting(E_ALL);

    session_start();
    if(!isset($_SESSION['email'])){
        header("Location: ../index.html");
        exit();
    }

    $db = new database();
    if(isset($_POST['submit'])){
        $password = $_POST['newPass'];
        $confermPass = $_POST['confPass'];

        if($password !== $confermPass){
            header("Location: ../optionsPage.html?error=Passwords do not match");
            exit();
        }

        $db->connect();
        $modify = $db->changePassword($_SESSION['email'], $password);

        $db->close();

        if($modify){
            header("Location: ../optionsPage.html");
            exit();
        }
    }
?>
