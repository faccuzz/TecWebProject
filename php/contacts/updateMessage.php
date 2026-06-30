<?php
include_once '../session_bootstrap.php';
include_once '../db.php';

if (empty($_SESSION['email']) || empty($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    http_response_code(403);
    header("Location: ../../index.html");
    exit();
}

$id = 0;
if (isset($_POST['id'])) $id = (int)$_POST['id'];
$action = '';
if (isset($_POST['action'])) $action = $_POST['action'];

if ($id < 1 || ($action !== 'handle' && $action !== 'delete')) {
    http_response_code(400);
    header("Location: ../../optionsPage.html?section=messages");
    exit();
}

$db = new database();
if (!$db->connect()) {
    dbFailRedirect("../../optionsPage.html?section=messages");
}

if ($action === 'handle') {
    $stmt = $db->connection->prepare("UPDATE messages SET handled = 1 WHERE id = ?");
} else {
    $stmt = $db->connection->prepare("DELETE FROM messages WHERE id = ?");
}
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
}
$db->close();

header("Location: ../../optionsPage.html?section=messages");
exit();
?>
