<?php 
    include_once 'db.php';
    session_start();
    if(isset($_SESSION['email'])){
        header("Location: ../index.html");
        exit();
    }
    $db = new database();
    if(isset($_POST['register'])){
        $username = $_POST['username'];
        $password = $_POST['password'];
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $email = $_POST['email'];
        $phoneNumber = $_POST['prefix'] . $_POST['phone'];

        $db->connect();
        $db->registration($username,$password,$name,$surname,$email,$phoneNumber);
        $db->close();
        $_SESSION['email'] = $email;
        header("Location: ../index.html");
        exit();
    }
?>