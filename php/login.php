<?php
include_once 'db.php';
session_start();
$db = new database();
if (isset($_SESSION['email'])) {
    header("Location: ../index.html");
    exit();
}
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $db->connect();
    $result = $db->login($email, $password);
    $db->close();

    if ($result) {
        session_start();

        //Anti Session Hijacking
        session_regenerate_id(true);

        $_SESSION['email'] = $row['email'];
        $_SESSION['is_admin'] = $row['is_admin'];

        header("Location: ../index.html");
        exit();
    } else {
        echo "<script>alert('Invalid email or password.'); window.location.href = '../login.html';</script>";
    }
}
?>