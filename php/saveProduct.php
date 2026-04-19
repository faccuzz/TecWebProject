<?php 
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    session_start();
    if(!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== 1){
        echo json_encode(["error" => "Accesso Negato"]);
        exit();
    }

    header('Content-Type: application/json');
    
    include_once 'db.php';
    
    $db = new database();
    $products = [];

    //Save new product
    if(isset($_POST['name'])){
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $image = $_FILES['image'];
        $in_stock = $_POST['inStock'] === 'true' ? 1 : 0;

        $destination_folder = "../assets/img/";

        $max_size = 2 * 1024 * 1024;
        if($image['size'] > $max_size){
            echo json_encode(['error' => 'Immagine troppo grande']);
            exit();
        }

        $extension = $image['type'];
        if (!in_array($extension, ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'])) {
            echo json_encode(['error' => 'Formato non valido']);
            exit();
        }

        $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
        $image_name = time() . "_" . strtolower(preg_replace('/\s+/', '', $name)) . "." . $extension;
        $destination_folder = $destination_folder . $image_name;

        if(move_uploaded_file($image['tmp_name'], $destination_folder)){
            $db->connect();
            $insert = $db->insertProduct($name, $price, $description, $image_name, $in_stock);
            $db->close();

            if($insert === true){
                echo json_encode(['success' => true]);
                exit();
            } else {
                echo json_encode(['success' => false, 'error' => $insert]);
                exit();
            }
        }

        exit();
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
