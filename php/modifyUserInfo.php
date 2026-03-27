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
        $street = $_POST['street'];
        $city = $_POST['city'];
        $postalCode = $_POST['postalCode'];

        $db->connect();
        $modify = $db->modifyUserInfo('user', $name, $surname, $email, $phoneNumber, $street, $city, $postalCode);

        $db->close();

        if($modify){
            header("Location: ../optionsPage.html");
            exit();
        }
    }
?>
