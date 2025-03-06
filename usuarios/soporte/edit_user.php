<?php
include '../../conexion/conectado.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST['id'];
    $table = $_POST['table'];
    $newUsername = $_POST['usuario'];
    $newPassword = password_hash($_POST['contraseña'], PASSWORD_BCRYPT);

    $sql = "UPDATE $table SET usuario = '$newUsername', contraseña = '$newPassword' WHERE id = $userId";

    if ($conn->query($sql) === TRUE) {
        echo "Usuario editado con éxito.";
    } else {
        echo "Error al editar el usuario: " . $conn->error;
    }

    $conn->close();
}
?>
