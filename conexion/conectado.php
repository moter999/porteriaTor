<?php
// Configuración simple para entorno local
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "porteria";

try {
    // Crear conexión
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar conexión
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    // Configurar el conjunto de caracteres
    if (!$conn->set_charset('utf8mb4')) {
        throw new Exception("Error al configurar el conjunto de caracteres: " . $conn->error);
    }

    // Configurar modo de error
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

} catch (Exception $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    die("Lo sentimos, ha ocurrido un error al conectar con la base de datos. Por favor, intente más tarde.");
}
?> 