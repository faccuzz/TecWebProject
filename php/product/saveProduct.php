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
    $name        = trim($_POST['name']);
    $price       = $_POST['price'];
    $description = trim($_POST['description'] ?? '');
    $material    = trim($_POST['material']   ?? '');
    $author      = trim($_POST['author']     ?? '');
    $dimensions  = database::formatDimensions(
        $_POST['dimensionsWidth']  ?? '',
        $_POST['dimensionsHeight'] ?? ''
    );
    $weight      = trim($_POST['weight']     ?? '');
    $voltage     = trim($_POST['voltage']    ?? '');

    // validazione lato server
    $errors = [];
    if ($name === '' || strlen($name) > 80) {
        $errors[] = "Nome prodotto non valido";
    }
    $priceFloat = filter_var($price, FILTER_VALIDATE_FLOAT);
    if ($priceFloat === false || $priceFloat <= 0 || $priceFloat > 99999) {
        $errors[] = "Prezzo non valido";
    }
    if ($description === '' || strlen($description) > 500) {
        $errors[] = "Descrizione non valida";
    }
    if (strlen($material) > 120 || strlen($author) > 80 || strlen($weight) > 30 || strlen($voltage) > 30) {
        $errors[] = "Lunghezza dei campi opzionali fuori limite";
    }
    if (!in_array($_POST['inStock'] ?? '', ['true', 'false'], true)) {
        $errors[] = "Disponibilita non valida";
    }
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Immagine mancante o non caricata correttamente";
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => implode(" - ", $errors)]);
        exit();
    }

    $in_stock = $_POST['inStock'] === 'true' ? 1 : 0;
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
