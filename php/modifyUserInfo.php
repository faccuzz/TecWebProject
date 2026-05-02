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
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $email = $_POST['email'];
        $phoneNumber = $_POST['phoneNumber'];

        $db->connect();
        $modify = $db->modifyUserInfo($_SESSION['email'],$name, $surname, $email, $phoneNumber);

        $db->close();

        if($modify){
            $_SESSION['email'] = $email;
            header("Location: ../optionsPage.html");
            exit();
        }
    }
?>
