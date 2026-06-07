<?php
include_once '../session_bootstrap.php';
include_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Nessun dato ricevuto."]);
    exit();
}

$email    = $data['email'] ?? '';
$password = $data['password'] ?? '';

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
    $userInfo = $db->login($email, $password);

    if ($userInfo) {
        session_regenerate_id(true); // Anti Session Hijacking
        $_SESSION['email']   = $userInfo['email'];
        $_SESSION['isAdmin'] = $userInfo['isAdmin'];
        session_write_close();
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Credenziali non valide."]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Errore durante l'accesso. Riprova più tardi."
    ]);
} finally {
    $db->close();
}
?>
