<?php
include_once '../session_bootstrap.php';
include_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Nessun dato ricevuto."]);
    exit();
}

$identifier = '';
if (isset($data['identifier'])) {
    $identifier = $data['identifier'];
} elseif (isset($data['email'])) {
    $identifier = $data['email'];
}
$password = '';
if (isset($data['password'])) {
    $password = $data['password'];
}

$db = new database();
if (!$db->connect()) {
    dbFailJson("Servizio non disponibile. Riprova più tardi.");
}

$userInfo = $db->login($identifier, $password);

if ($userInfo) {
    regenSession();
    $_SESSION['email']   = $userInfo['email'];
    $_SESSION['isAdmin'] = $userInfo['isAdmin'];
    session_write_close();
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Credenziali non valide."]);
}

$db->close();
?>
