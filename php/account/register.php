<?php
include_once '../session_bootstrap.php';
include_once '../db.php';

header('Content-Type: application/json');

$inputData = json_decode(file_get_contents("php://input"), true);

if (empty($inputData)) {
    echo json_encode(["success" => false, "message" => "Nessun dato ricevuto."]);
    exit();
}

$username    = trim($inputData['username'] ?? '');
$password    = $inputData['password'] ?? '';
$name        = trim($inputData['name'] ?? '');
$surname     = trim($inputData['surname'] ?? '');
$email       = trim($inputData['email'] ?? '');
$phoneNumber = trim($inputData['phone'] ?? '');

$isAdmin = 0;
if (isset($inputData['isAdmin'])) {
    if (
        $inputData['isAdmin'] === true || $inputData['isAdmin'] === "true" ||
        $inputData['isAdmin'] === 1   || $inputData['isAdmin'] === "1"
    ) {
        $isAdmin = 1;
    }
}

// validazione lato server: il client puo essere bypassato, quindi ricontrollo tutto qui
$errors = [];

if (strlen($username) < 3 || strlen($username) > 32) {
    $errors[] = "Username deve essere lungo tra 3 e 32 caratteri";
} elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
    $errors[] = "Username contiene caratteri non ammessi";
}

if (strlen($password) < 8 || strlen($password) > 128) {
    $errors[] = "Password deve essere lunga tra 8 e 128 caratteri";
} elseif (
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[^A-Za-z0-9]/', $password)
) {
    $errors[] = "Password deve contenere maiuscola, numero e carattere speciale";
}

$nameRegex = '/^[a-zA-ZàèéìòùÀÈÉÌÒÙ\'\s\-]+$/u';
if ($name === '' || strlen($name) > 64 || !preg_match($nameRegex, $name)) {
    $errors[] = "Nome non valido";
}
if ($surname === '' || strlen($surname) > 64 || !preg_match($nameRegex, $surname)) {
    $errors[] = "Cognome non valido";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    $errors[] = "Email non valida";
}

if ($phoneNumber !== '' && !preg_match('/^\+?[0-9 ]{6,20}$/', $phoneNumber)) {
    $errors[] = "Numero di telefono non valido";
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => implode(" - ", $errors)
    ]);
    exit();
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
