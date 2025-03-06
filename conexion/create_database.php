<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";

// Crear conexión sin seleccionar base de datos
$conn = new mysqli($servername, $username, $password);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Crear la base de datos si no existe
$sql = "CREATE DATABASE IF NOT EXISTS porteria";
if ($conn->query($sql) === TRUE) {
    echo "Base de datos 'porteria' creada o ya existe<br>";
} else {
    echo "Error creando la base de datos: " . $conn->error . "<br>";
}

$conn->close();
?> 