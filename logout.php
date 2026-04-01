<?php
// -------------------------------------------------------
//  logout.php
//  Destroys session AND clears the JWT cookie.
// -------------------------------------------------------

session_start();
session_destroy();

// Clear the JWT cookie by setting it to expire in the past
setcookie('jwt_token', '', [
    'expires'  => time() - 3600,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);

header("Location: index.php");
exit();