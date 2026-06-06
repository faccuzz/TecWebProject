<?php
include_once '../db.php';

header('Content-Type: application/json');

$db = new database();
$db->connect();

$prodotti = [];

if (isset($_GET['category']) && isset($_GET['value']) && $_GET['category'] != '') {
    $requestedCategory = $_GET['category'];
    $requestedValue = "%" . $_GET['value'] . "%";

    $whitelist = ['id', 'productName', 'category', 'price', 'description'];

    if (in_array($requestedCategory, $whitelist)) {
        if ($requestedCategory === 'id') {
            // Procedimento per cercare svariati id alla volta:
            // separo gli ID, conto, creo i placeholder, eseguo bind dinamico
            $idList = explode(',', $_GET['value']);
            $idCount = count($idList);
            $placeholder = implode(',', array_fill(0, $idCount, '?'));

            $query = $db->connection->prepare("SELECT * FROM products WHERE $requestedCategory IN ($placeholder)");
            $stringParam = str_repeat('s', $idCount);
            $query->bind_param($stringParam, ...$idList);
        } else {
            $query = $db->connection->prepare("SELECT * FROM products WHERE $requestedCategory LIKE ?");
            $query->bind_param("s", $requestedValue);
        }

        $query->execute();
        $risultato = $query->get_result();
    } else {
        echo json_encode(["errore" => "Colonna non permessa"]);
        $db->close();
        exit();
    }
} else {
    $risultato = $db->connection->query("SELECT * FROM products");
}

if ($risultato->num_rows > 0) {
    while ($riga = $risultato->fetch_assoc()) {
        $prodotti[] = $riga;
    }
}

$db->close();

echo json_encode($prodotti);
?>