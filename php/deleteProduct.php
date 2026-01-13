<?php 
    include_once 'db.php';
    $db = new database();
    if(isset($_GET['id'])){
        $id = $_GET['id'];
        $db->connect();
        $delete = $db->deleteProduct($id);
        $db->close();

        header("Location: ../adminPage.html");
        exit();
    }
?>