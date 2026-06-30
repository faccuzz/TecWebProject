<?php
include_once '../session_bootstrap.php';
header('Content-Type: application/json');

if (isset($_SESSION['email'])) {
    $isAdmin = 0;
    if (isset($_SESSION['isAdmin'])) $isAdmin = $_SESSION['isAdmin'];
    echo json_encode([
        "logged_in" => true,
        "email"     => $_SESSION['email'],
        "is_admin"  => $isAdmin
    ]);
} else {
    echo json_encode(["logged_in" => false]);
}
?>
