<?php
include_once '../session_bootstrap.php';
include_once '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$data = json_decode(file_get_contents("php://input"), true);

$action = 'get';
if (isset($data['action'])) $action = $data['action'];

if ($action !== 'add' && $action !== 'update' && $action !== 'remove' && $action !== 'clear' && $action !== 'get') {
    http_response_code(400);
    echo json_encode(["error" => "Azione non valida"]);
    exit();
}

if ($action === 'add') {
    $id = '';
    if (isset($data['id'])) $id = $data['id'];
    $qty = 0;
    if (isset($data['qty'])) $qty = (int)$data['qty'];

    if (!is_string($id) || !preg_match('/^[A-Z0-9]{10}$/', $id) || $qty < 1 || $qty > 99) {
        http_response_code(400);
        echo json_encode(["error" => "Parametri non validi"]);
        exit();
    }

    $db = new database();
    if (!$db->connect() || !$db->idExists($id)) {
        $db->close();
        http_response_code(400);
        echo json_encode(["error" => "Prodotto inesistente"]);
        exit();
    }
    $db->close();

    if (isset($_SESSION['cart'][$id])) {
        $newQty = $_SESSION['cart'][$id] + $qty;
        if ($newQty > 99) $newQty = 99;
        $_SESSION['cart'][$id] = $newQty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
}

if ($action === 'update') {
    $id = '';
    if (isset($data['id'])) $id = $data['id'];
    $qty = -1;
    if (isset($data['qty'])) $qty = (int)$data['qty'];

    if (!is_string($id) || !preg_match('/^[A-Z0-9]{10}$/', $id) || $qty < 0 || $qty > 99) {
        http_response_code(400);
        echo json_encode(["error" => "Parametri non validi"]);
        exit();
    }
    if (isset($_SESSION['cart'][$id])) {
        if ($qty > 0) {
            $_SESSION['cart'][$id] = $qty;
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }
}

if ($action === 'remove') {
    $id = '';
    if (isset($data['id'])) $id = $data['id'];

    if (!is_string($id) || !preg_match('/^[A-Z0-9]{10}$/', $id)) {
        http_response_code(400);
        echo json_encode(["error" => "Parametri non validi"]);
        exit();
    }
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
}

echo json_encode($_SESSION['cart']);
?>
