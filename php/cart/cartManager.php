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

/* azioni gestite:
   add    -> aggiunge un prodotto (o aumenta la quantità se gia c'è)
   update -> cambia la quantità di un prodotto
   remove -> toglie un prodotto
   clear  -> svuota tutto
   get    -> ritorna il carrello in sessione
*/
switch ($action) {
    case 'add':
        // se è già nel carrello sommo, altrimenti lo aggiungo
        $id = $data['id'];
        $qty = intval($data['qty']);
        
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] += $qty;
        } else {
            $_SESSION['cart'][$id] = $qty;
        }
        break;

    case 'update':
        $id = $data['id'];
        $qty = intval($data['qty']);
        if (isset($_SESSION['cart'][$id])) {
            if ($qty > 0) {
                $_SESSION['cart'][$id] = $qty;
            } else {
                unset($_SESSION['cart'][$id]);
            }
        }
        break;

    case 'remove':
        $id = $data['id'];
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
        break;

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