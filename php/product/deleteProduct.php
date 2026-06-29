<?php 
    include_once '../db.php';

include_once '../session_bootstrap.php';
    if(!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== 1){
        echo json_encode(["error" => "Accesso Negato"]);
        exit();
    }
    
    $db = new database();
    if(isset($_POST['submit'])){
        $id = trim($_POST['id'] ?? '');

        // validazione lato server: id deve essere alfanumerico maiuscolo lungo 10
        if (!preg_match('/^[A-Z0-9]{10}$/', $id)) {
            header("Location: ../../optionsPage.html?section=products&error=" . urlencode("ID prodotto non valido"));
            exit();
        }

        $db->connect();
        if (!$db->idExists($id)) {
            $db->close();
            header("Location: ../../optionsPage.html?section=products&error=" . urlencode("Prodotto inesistente"));
            exit();
        }
        $db->deleteProduct($id);
        $db->close();

        header("Location: ../../optionsPage.html?section=products");
        exit();
    }
?>
