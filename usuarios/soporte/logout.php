<?php
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, borrar también la cookie de sesión
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir al usuario a la página de login
header("Location: ../../login.php");
exit(); 