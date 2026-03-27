<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['email'])) {

    $isAdmin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);

    echo json_encode([
        "logged_in" => true,
        "email" => $_SESSION['email'],
        "is_admin" => $isAdmin
    ]);
} else {
    echo json_encode(["logged_in" => false]);
}
?>