<?php 
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    session_start();

    header('Content-Type: application/json');
    
    include_once 'db.php';
    
    $db = new database();
    $products = [];

    //Save new product
    if(isset($_POST['submit'])){
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];

        $nameIMG = strtolower(preg_replace('/\s+/', '', $name));
        $urlIMG = $_POST['img'];
        $destinationFolder = "../assets/img/";

        $extension = pathinfo($urlIMG, PATHINFO_EXTENSION);
        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $extension = 'jpg'; 
        }
        $destinationFolder = $destinationFolder . $nameIMG . "." . $extension;

        $img = @file_get_contents($urlIMG);
        if($img !== false) {
            file_put_contents($destinationFolder, $img);
        }
        
        $db->connect();
        $insert = $db->insertProduct($name, $price, $description, $urlIMG);
        $db->close();

        if($insert){
            header("Location: ../adminPage.html");
            exit();
        }
    }

    //Retrieve and send products
    $db->connect();
    $result = $db->getProducts();
    
    if($result){
        while($row = $result->fetch_assoc()){
            $products[] = $row;
        }
    }
    $db->close();
    
    //Print JSON
    echo json_encode($products);
?>