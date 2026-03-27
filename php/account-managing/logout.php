<?php
session_start();

//Rimuove le variabili
session_unset();
//Distrugge la sessione
session_destroy();

header("Location: ../index.html");
exit();
?>