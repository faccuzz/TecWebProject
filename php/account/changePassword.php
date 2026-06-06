<?php 
    include_once '../db.php';
    
    session_start();
    if(!isset($_SESSION['email'])){
        header("Location: ../../index.html");
        exit();
    }

    $db = new database();
    if(isset($_POST['newPass']) && isset($_POST['confPass'])){
        $password = $_POST['newPass'];
        $confermPass = $_POST['confPass'];

        if($password !== $confermPass){
            header("Location: ../../optionsPage.html?section=security&error=Passwords do not match");
            exit();
        }

        $db->connect();
        $modify = $db->changePassword($_SESSION['email'], $password);

        $db->close();

        if($modify){
            header("Location: ../../optionsPage.html?section=security&success=Password changed successfully");
            exit();
        }
    }
    else {
        header("Location: ../../optionsPage.html?section=security");
        exit();
    }
?>
