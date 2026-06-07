<?php
include_once '../session_bootstrap.php';
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== 1) {
    echo json_encode(["error" => "Accesso Negato"]);
    exit();
}

header('Content-Type: application/json');
include_once '../db.php';

$db = new database();
$products = [];

if (isset($_POST['name'])) {
    $name        = $_POST['name'];
    $price       = $_POST['price'];
    $description = $_POST['description'];
    $material    = $_POST['material']   ?? '';
    $author      = $_POST['author']     ?? '';
    $dimensions  = $_POST['dimensions'] ?? '';
    $weight      = $_POST['weight']     ?? '';
    $voltage     = $_POST['voltage']    ?? '';
    $in_stock    = $_POST['inStock'] === 'true' ? 1 : 0;

    $upload = database::validateAndMoveImage($_FILES['image'], $name, "../../assets/img/");
    if (isset($upload['error'])) {
        echo json_encode(['error' => $upload['error']]);
        exit();
    }

    $db->connect();
    $insert = $db->insertProduct(
        $name, $price, $description, $upload['image_name'], $in_stock,
        $material, $author, $dimensions, $weight, $voltage
    );
    $db->close();

    echo json_encode($insert === true ? ['success' => true] : ['success' => false, 'error' => $insert]);
    exit();
}

$db->connect();
$result = $db->getProducts();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$db->close();
echo json_encode($products);
?>
