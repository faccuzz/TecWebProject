<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "configDB.php";

header('Content-Type: application/json');

$conn = new mysqli(DB_CONFIG["db_host"], DB_CONFIG["db_user"], DB_CONFIG["db_password"], DB_CONFIG["db_name"]);

if ($conn->connect_error) {
    echo json_encode(["errore" => "Connessione fallita"]);
    exit();
}


$prodotti = [];

if (isset($_GET['category']) && isset($_GET['value']) && $_GET['category'] != '') {
    $requestedCategory = $_GET['category'];
    $requestedValue = "%" . $_GET['value'] . "%";

    $whitelist = ['id', 'productName', 'category', 'price', 'description'];

    if (in_array($requestedCategory, $whitelist)) {
        if ($requestedCategory === 'id') {
            //Procedimento per cercare svariati id alla volta
            /**
             * 1. Separo gli ID in un array
             * 2. Li conto
             * 3. Creo la stringa placeholder per preparare la query
             * 
             * 4. Binding dei parametri
             */
            $idList = explode(',', $_GET['value']);
            $idCount = count($idList);
            $placeholder = implode(',', array_fill(0, $idCount, '?'));

            $query = $conn->prepare("SELECT * FROM products WHERE $requestedCategory IN ($placeholder)");
            $stringParam = str_repeat('s', $idCount);
            $query->bind_param($stringParam, ...$idList);

        } else {
            /**
             * Uso l'operatore LIKE così da poter eventualmente inviare un campo value vuoto, 
             * restituendomi tutti i componenti di quella colonna con %%.
             * Like mi permette anche di non dover cercare esattamente quella stringa.
             */
            $query = $conn->prepare("SELECT * FROM products WHERE $requestedCategory LIKE ?");

            $query->bind_param("s", $requestedValue);
            
        }

        $query->execute();
        $risultato = $query->get_result();
    } else {
        echo json_encode(["errore" => "Colonna non permessa"]);
        exit();
    }
} else {
    $risultato = $conn->query("SELECT * FROM products");
}

if ($risultato->num_rows > 0) {
    while ($riga = $risultato->fetch_assoc()) {
        $prodotti[] = $riga;
    }
}

$conn->close();

echo json_encode($prodotti);
?>