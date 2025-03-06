<?php
include '../../conexion/conectado.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST['id'];
    $table = $_POST['table'];

    $sql = "DELETE FROM $table WHERE id = $userId";

    if ($conn->query($sql) === TRUE) {
        echo "Usuario eliminado con éxito.";
        
    } else {
        echo "Error al eliminar el usuario: " . $conn->error;
    }

    $conn->close();
}
?>
