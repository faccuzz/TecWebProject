<?php
include_once '../session_bootstrap.php';
include_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Nessun dato ricevuto."]);
    exit();
}

$name    = trim($data['name']    ?? '');
$surname = trim($data['surname'] ?? '');
$email   = trim($data['email']   ?? '');
$subject = trim($data['subject'] ?? '');
$message = trim($data['message'] ?? '');

// whitelist degli oggetti consentiti (devono coincidere con i value del select in contacts.html)
$allowedSubjects = ['order', 'damage', 'custom', 'general'];

$errors = [];

if ($name === '' || strlen($name) > 64) {
    $errors[] = "Nome non valido";
}
if ($surname === '' || strlen($surname) > 64) {
    $errors[] = "Cognome non valido";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    $errors[] = "Email non valida";
}
if (!in_array($subject, $allowedSubjects, true)) {
    $errors[] = "Oggetto non valido";
}
if (strlen($message) < 10 || strlen($message) > 2000) {
    $errors[] = "Il messaggio deve essere lungo tra 10 e 2000 caratteri";
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => implode(" - ", $errors)
    ]);
    exit();
}

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
    $sql = "INSERT INTO messages (name, surname, email, subject, message)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->connection->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Errore durante l'invio del messaggio. Riprova più tardi."
        ]);
        exit();
    }
    $stmt->bind_param('sssss', $name, $surname, $email, $subject, $message);
    $stmt->execute();
    echo json_encode([
        "success" => true,
        "message" => "Messaggio inviato. Ti risponderemo entro 24 ore."
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Errore durante l'invio del messaggio. Riprova più tardi."
    ]);
} finally {
    $db->close();
}
?>
