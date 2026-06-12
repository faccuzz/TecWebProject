<?php
// Setup della sessione. Lo includo prima di session_start() negli endpoint
// che usano la sessione, cosi il cookie ha path '/' e funziona da qualsiasi
// pagina (altrimenti su Apache vs server built-in cambia path).

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('PHPSESSID');
    session_start();
}
?>
