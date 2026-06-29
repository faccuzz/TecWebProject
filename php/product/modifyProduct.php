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
    $id          = trim($_POST['id'] ?? '');
    $name        = trim($_POST['name'] ?? '');
    $price       = $_POST['price'] ?? '';
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
    if (!preg_match('/^[A-Z0-9]{10}$/', $id) || !$db->idExists($id)) {
        $errors[] = "ID prodotto non valido";
    }
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

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => implode(" - ", $errors)]);
        $db->close();
        exit();
    }

    $in_stock = $_POST['inStock'] === 'true' ? 1 : 0;

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
