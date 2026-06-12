<?php 
    include_once '../db.php';

include_once '../session_bootstrap.php';
    if(!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== 1){
        echo json_encode(["error" => "Accesso Negato"]);
        exit();
    }
    
    $db = new database();
    if(isset($_POST['submit'])){
        $id = $_POST['id'];
        $db->connect();
        $db->deleteProduct($id);
        $db->close();

        header("Location: ../../optionsPage.html");
        exit();
    }
?>
