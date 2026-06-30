<?php 
    include_once '../db.php';
    header('Content-Type: application/json');
include_once '../session_bootstrap.php';
    if(!isset($_SESSION['email'])){
        header("Location: ../../index.html");
        exit();
    }
    
    $db = new database();
    if(isset($_POST['submit'])){
        $name = '';
        if (isset($_POST['name'])) $name = trim($_POST['name']);
        $surname = '';
        if (isset($_POST['surname'])) $surname = trim($_POST['surname']);
        $email = '';
        if (isset($_POST['email'])) $email = trim($_POST['email']);
        $phoneNumber = '';
        if (isset($_POST['phoneNumber'])) $phoneNumber = trim($_POST['phoneNumber']);

        $errors = [];
        $nameRegex = '/^[a-zA-ZàèéìòùÀÈÉÌÒÙ\'\s\-]+$/u';

        if ($name === '' || strlen($name) > 64 || !preg_match($nameRegex, $name)) {
            $errors[] = "Nome non valido";
        }
        if ($surname === '' || strlen($surname) > 64 || !preg_match($nameRegex, $surname)) {
            $errors[] = "Cognome non valido";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            $errors[] = "Email non valida";
        }
        if ($phoneNumber !== '' && !preg_match('/^\+?[0-9 ]{6,20}$/', $phoneNumber)) {
            $errors[] = "Numero di telefono non valido";
        }

        if (!empty($errors)) {
            $_SESSION['stickyConfig'] = [
                'name'        => $name,
                'surname'     => $surname,
                'email'       => $email,
                'phoneNumber' => $phoneNumber
            ];
            header("Location: ../../optionsPage.html?section=configurations&error=" . urlencode(implode(" - ", $errors)));
            exit();
        }

        $db->connect();
        $modify = $db->modifyUserInfo($_SESSION['email'],$name, $surname, $email, $phoneNumber);

        $db->close();

        if($modify){
            $_SESSION['email'] = $email;
            header("Location: ../../optionsPage.html?section=configurations&success=" . urlencode("Dati aggiornati"));
            exit();
        }
        header("Location: ../../optionsPage.html?section=configurations&error=" . urlencode("Errore durante l'aggiornamento"));
        exit();
    }
?>
