<?php 
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    session_start();
    if(!isset($_SESSION['email']) || $_SESSION['email'] !== 'admin@gmail.com'){
        header("Location: ../index.html");
        exit();
    }

    header('Content-Type: application/json');
    
    /**
     * TODO: Verificare se admin, aggiungere voce SOLO se flag settato correttamente
     *
    *if(!isset($_SESSION['email'])){
       * echo json_encode(["error" => "Accesso negato"]);
      *  exit();
    *}
    */

    include_once 'db.php';
    
    $db = new database();
    $products = [];

    //Save new product
    if(isset($_POST['submit'])){
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $inStock = $_POST['inStock'] === 'true' ? 1 : 0;

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
        $insert = $db->insertProduct($name, $price, $description, $urlIMG, $inStock);
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
