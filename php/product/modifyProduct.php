<?php
include_once '../db.php';
header('Content-Type: application/json');

include_once '../session_bootstrap.php';
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== 1) {
    echo json_encode(["error" => "Accesso Negato"]);
    exit();
}

$db = new database();
$db->connect();

if (isset($_POST['submit'])) {
    $id          = $_POST['id'];
    $name        = $_POST['name'];
    $price       = $_POST['price'];
    $description = $_POST['description'];
    $material    = $_POST['material']   ?? '';
    $author      = $_POST['author']     ?? '';
    $dimensions  = $_POST['dimensions'] ?? '';
    $weight      = $_POST['weight']     ?? '';
    $voltage     = $_POST['voltage']    ?? '';
    $in_stock    = $_POST['inStock'] === 'true' ? 1 : 0;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload = database::validateAndMoveImage($_FILES['image'], $name, "../../assets/img/");
        if (isset($upload['error'])) {
            echo json_encode(['error' => $upload['error']]);
            $db->close();
            exit();
        }
        $insert = $db->modifyProduct(
            $id, $name, $price, $description, $upload['image_name'], $in_stock,
            $material, $author, $dimensions, $weight, $voltage
        );
    } else {
        $insert = $db->modifyProductWithoutImage(
            $id, $name, $price, $description, $in_stock,
            $material, $author, $dimensions, $weight, $voltage
        );
    }

    $db->close();
    echo json_encode($insert ? ['success' => true] : ['success' => false, 'error' => 'Errore aggiornamento']);
    exit();
}
?>
