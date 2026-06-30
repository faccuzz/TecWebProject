<?php 
    include_once '../db.php';
    
include_once '../session_bootstrap.php';
    if(!isset($_SESSION['email'])){
        header("Location: ../../index.html");
        exit();
    }

    $db = new database();
    if(isset($_POST['newPass']) && isset($_POST['confPass'])){
        $password = $_POST['newPass'];
        $confermPass = $_POST['confPass'];

        if (strlen($password) < 8 || strlen($password) > 128) {
            header("Location: ../../optionsPage.html?section=security&error=" . urlencode("Password deve essere lunga tra 8 e 128 caratteri"));
            exit();
        }
        if (
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[0-9]/', $password) ||
            !preg_match('/[^A-Za-z0-9]/', $password)
        ) {
            header("Location: ../../optionsPage.html?section=security&error=" . urlencode("Password deve contenere maiuscola, numero e carattere speciale"));
            exit();
        }
        if($password !== $confermPass){
            header("Location: ../../optionsPage.html?section=security&error=" . urlencode("Le password non coincidono"));
            exit();
        }

        $db->connect();
        $modify = $db->changePassword($_SESSION['email'], $password);

        $db->close();

        if($modify){
            header("Location: ../../optionsPage.html?section=security&success=" . urlencode("Password modificata con successo"));
            exit();
        }
        header("Location: ../../optionsPage.html?section=security&error=" . urlencode("Errore durante la modifica della password"));
        exit();
    }
    else {
        header("Location: ../../optionsPage.html?section=security");
        exit();
    }
?>
