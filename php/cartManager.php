<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$data = json_decode(file_get_contents("php://input"), true);

//Default: restituisci il carrelo (get)
$action = isset($data['action']) ? $data['action'] : 'get';

/**
 * add: aggiunge il prodotto, o ne aumenta la quantità, nel carrello
 * update: aggiorna la quantità di prodotto
 * remove: rimuove un prodotto dal carrello
 * clear: libera l'intero carrello
 * get: restituisce il carrello nella sessione
 */
switch ($action) {
    //Se già presente sommo la quantità, altrimenti la assegno
    case 'add':
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
        //Non fa nulla, esce dallo switch e ritorna il carrello
    default:
        break;
}

echo json_encode($_SESSION['cart']);
?>