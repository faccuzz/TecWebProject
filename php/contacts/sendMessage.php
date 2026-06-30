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

$name = '';
if (isset($data['name'])) $name = trim($data['name']);
$surname = '';
if (isset($data['surname'])) $surname = trim($data['surname']);
$email = '';
if (isset($data['email'])) $email = trim($data['email']);
$subject = '';
if (isset($data['subject'])) $subject = trim($data['subject']);
$message = '';
if (isset($data['message'])) $message = trim($data['message']);

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
    dbFailJson("Servizio non disponibile. Riprova più tardi.");
}

$sql = "INSERT INTO messages (name, surname, email, subject, message)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $db->connection->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Errore durante l'invio del messaggio."
    ]);
    $db->close();
    exit();
}
$stmt->bind_param('sssss', $name, $surname, $email, $subject, $message);
$stmt->execute();
echo json_encode([
    "success" => true,
    "message" => "Messaggio inviato. Ti risponderemo entro 24 ore."
]);

$db->close();
?>
