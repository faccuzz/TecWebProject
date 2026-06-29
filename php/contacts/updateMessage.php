<?php
include_once '../session_bootstrap.php';
include_once '../db.php';

// solo admin loggato puo gestire i messaggi
if (empty($_SESSION['email']) || empty($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    http_response_code(403);
    header("Location: ../../index.html");
    exit();
}

$id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$action = $_POST['action'] ?? '';

if (!$id || !in_array($action, ['handle', 'delete'], true)) {
    http_response_code(400);
    header("Location: ../../optionsPage.html?section=messages");
    exit();
}

$db = new database();
if (!$db->connect()) {
    http_response_code(503);
    header("Location: ../../optionsPage.html?section=messages");
    exit();
}

try {
    if ($action === 'handle') {
        $stmt = $db->connection->prepare("UPDATE messages SET handled = 1 WHERE id = ?");
    } else {
        $stmt = $db->connection->prepare("DELETE FROM messages WHERE id = ?");
    }
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
} catch (Throwable $e) {
    // fallback silenzioso: torna comunque alla pagina, eventuali errori si vedono dal listato
} finally {
    $db->close();
}

header("Location: ../../optionsPage.html?section=messages");
exit();
?>
