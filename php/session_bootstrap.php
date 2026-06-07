<?php
/**
 * Bootstrap di sessione condiviso.
 *
 * Da includere PRIMA di session_start() in qualsiasi endpoint che usa la sessione.
 * Forza cookie_path = '/' e SameSite = 'Lax' in modo che il cookie PHPSESSID
 * sia condiviso fra tutti i path (es. /php/account/register.php e /index.html),
 * indipendentemente da come è configurato il php.ini del server (built-in vs Apache).
 */

if (session_status() === PHP_SESSION_NONE) {
    // Path '/' per assicurare che il cookie sia inviato da qualsiasi URL del sito.
    $cookieParams = [
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    session_set_cookie_params($cookieParams);
    session_name('PHPSESSID');
    session_start();
}
?>
