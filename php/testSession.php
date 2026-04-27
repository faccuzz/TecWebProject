<?php
/**
 * Cancellare prima di consegnare. File solo di test
 */
session_start();

// Diciamo al browser di formattare bene il testo
echo "<pre>";

// print_r stampa a schermo TUTTO il contenuto della sessione attuale
print_r($_SESSION);

echo "</pre>";
?>