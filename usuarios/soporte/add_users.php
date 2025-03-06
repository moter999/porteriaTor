<?php
include '../../conexion/conectado.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_BCRYPT);
    $rol = $_POST['rol'];

    $sql = "INSERT INTO $rol (usuario, contraseña) VALUES ('$usuario', '$contraseña')";

    if ($conn->query($sql) === TRUE) {
        echo "Usuario agregado con éxito.";
    } else {
        echo "Error al agregar el usuario: " . $conn->error;
    }

    $conn->close();
}
?>
