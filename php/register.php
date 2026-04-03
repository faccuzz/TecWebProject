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
        $street = $_POST['street'];
        $city = $_POST['city'];
        $postalCode = $_POST['postalCode'];
        if($_POST['isAdmin'] === 'true'){
            $isAdmin = 1;
        }
        else{
            $isAdmin = 0;
        }

        $db->connect();
        $db->registration($username,$password,$name,$surname,$email,$phoneNumber,$isAdmin,$street,$city,$postalCode);
        $db->close();
        $_SESSION['email'] = $email;
        $_SESSION['is_admin'] = $isAdmin;
        header("Location: ../index.html");
        exit();
    }
?>