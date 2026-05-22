<?php 
    include_once 'db.php';
    header('Content-Type: application/json');
    error_reporting(E_ALL);

    session_start();
    if(!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== 1){
        echo json_encode(["error" => "Accesso Negato"]);
        exit();
    }
    
    $db = new database();
    $db->connect();
    if(isset($_POST['submit'])){
        $id = $_POST['id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $image = $_FILES['image'];
        $in_stock = $_POST['inStock'] === 'true' ? 1 : 0;

        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
            $image = $_FILES['image'];
            $destination_folder = "../assets/img/";

            $max_size = 2 * 1024 * 1024;
            if($image['size'] > $max_size){
                echo json_encode(['error' => 'Immagine troppo grande']);
                $db->close();
                exit();
            }

            $extension_type = $image['type'];
            if (!in_array($extension_type, ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'])) {
                echo json_encode(['error' => 'Formato non valido']);
                $db->close();
                exit();
            }

            $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
            $image_name = strtolower(preg_replace('/\s+/', '', $name)) . "_" . time() . "." . $extension;
            $destination_folder = $destination_folder . $image_name;

            if(move_uploaded_file($image['tmp_name'], $destination_folder)){
                $insert = $db->modifyProduct($id, $name, $price, $description, $image_name, $in_stock);
            } else {
                echo json_encode(['success' => false, 'error' => 'Errore nel caricamento del file sul server']);
                $db->close();
                exit();
            }
        } else {
            $insert = $db->modifyProductWithoutImage($id, $name, $price, $description, $in_stock);
        }

        $db->close();

        if($insert === true){
            echo json_encode(['success' => true]);
            exit();
        } else {
            echo json_encode(['success' => false, 'error' => $insert]);
            exit();
        }
    }
?>
