<?php 
    include_once 'db.php';
    header('Content-Type: application/json');
    error_reporting(0);

    $db = new database();
    $products = [];
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
        file_put_contents($destinationFolder, $img);
        
        $db->connect();
        $insert = $db->insertProduct($name, $price, $description);
    
        $db->close();

        if($insert){
            header("Location: ../adminPage.html");
            exit();
        }

    }

    $db->connect();
    $result = $db->getProducts();
    
    if($result){
        while($row = $result->fetch_assoc()){
            $products[] = $row;
        }
    }
    $db->close();
    echo json_encode($products);
?>
