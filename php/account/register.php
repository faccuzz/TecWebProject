<?php
include_once '../session_bootstrap.php';
include_once '../db.php';

header('Content-Type: application/json');

$inputData = json_decode(file_get_contents("php://input"), true);

if (empty($inputData)) {
    echo json_encode(["success" => false, "message" => "Nessun dato ricevuto."]);
    exit();
}

$username    = $inputData['username'] ?? '';
$password    = $inputData['password'] ?? '';
$name        = $inputData['name'] ?? '';
$surname     = $inputData['surname'] ?? '';
$email       = $inputData['email'] ?? '';
$phoneNumber = $inputData['phone'] ?? '';

$isAdmin = 0;
if (isset($inputData['isAdmin'])) {
    if (
        $inputData['isAdmin'] === true || $inputData['isAdmin'] === "true" ||
        $inputData['isAdmin'] === 1   || $inputData['isAdmin'] === "1"
    ) {
        $isAdmin = 1;
    }
}

// l'indirizzo non lo chiediamo in registrazione (lo mette al checkout)
$address  = $inputData['street']     ?? '';
$city     = $inputData['city']       ?? '';
$cap      = $inputData['postalCode'] ?? '';
$province = $inputData['province']   ?? '';
$state    = $inputData['state']      ?? '';

$db = new database();
if (!$db->connect()) {
    http_response_code(503);
    echo json_encode([
        "success" => false,
        "message" => "Servizio temporaneamente non disponibile. Riprova più tardi."
    ]);
    exit();
}

try {
    $result = $db->registration($username, $password, $name, $surname, $email, $phoneNumber, $isAdmin, $address, $city, $cap, $province, $state);

    if ($result) {
        if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 1) {
            echo json_encode(["success" => true, "stayOnPage" => true]);
        } else {
            $_SESSION['email']   = $email;
            $_SESSION['isAdmin'] = $isAdmin;
            session_write_close();
            echo json_encode(["success" => true, "stayOnPage" => false]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Registrazione fallita. Controlla i dati e riprova."
        ]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Errore durante la registrazione. Riprova più tardi."
    ]);
} finally {
    $db->close();
}
?>
