<?php
include_once '../session_bootstrap.php';
include_once '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$data = json_decode(file_get_contents("php://input"), true);

// di default torna il carrello
$action = isset($data['action']) ? $data['action'] : 'get';

$allowedActions = ['add', 'update', 'remove', 'clear', 'get'];
if (!in_array($action, $allowedActions, true)) {
    http_response_code(400);
    echo json_encode(["error" => "Azione non valida"]);
    exit();
}

// helper per validare id prodotto (10 caratteri alfanumerici come da generateRandomID)
function validateProductId($raw)
{
    if (!is_string($raw)) return false;
    return preg_match('/^[A-Z0-9]{10}$/', $raw) === 1 ? $raw : false;
}

// helper per validare quantita (intero tra 1 e 99)
function validateQty($raw)
{
    return filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);
}

/* azioni gestite:
   add    -> aggiunge un prodotto (o aumenta la quantità se gia c'è)
   update -> cambia la quantità di un prodotto
   remove -> toglie un prodotto
   clear  -> svuota tutto
   get    -> ritorna il carrello in sessione
*/
switch ($action) {
    case 'add': {
        $id = validateProductId($data['id'] ?? null);
        $qty = validateQty($data['qty'] ?? null);
        if ($id === false || $qty === false) {
            http_response_code(400);
            echo json_encode(["error" => "Parametri non validi"]);
            exit();
        }
        // verifico che il prodotto esista nel db prima di metterlo in carrello
        $db = new database();
        if (!$db->connect() || !$db->idExists($id)) {
            $db->close();
            http_response_code(400);
            echo json_encode(["error" => "Prodotto inesistente"]);
            exit();
        }
        $db->close();

        // se è già nel carrello sommo, altrimenti lo aggiungo
        if (isset($_SESSION['cart'][$id])) {
            $newQty = $_SESSION['cart'][$id] + $qty;
            $_SESSION['cart'][$id] = min($newQty, 99);
        } else {
            $_SESSION['cart'][$id] = $qty;
        }
        break;
    }

    case 'update': {
        $id = validateProductId($data['id'] ?? null);
        $qty = filter_var($data['qty'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99]]);
        if ($id === false || $qty === false) {
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
        break;
    }

    case 'remove': {
        $id = validateProductId($data['id'] ?? null);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(["error" => "Parametri non validi"]);
            exit();
        }
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
        break;
    }

    case 'clear':
        $_SESSION['cart'] = [];
        break;

    case 'get':
    default:
        // non fa niente, esce dallo switch e ritorna il carrello
        break;
}

echo json_encode($_SESSION['cart']);
?>