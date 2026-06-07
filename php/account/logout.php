<?php
include_once '../session_bootstrap.php';

//Rimuove le variabili
session_unset();
//Distrugge la sessione
session_destroy();

header("Location: ../../index.html");
exit();
?>