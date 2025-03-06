<?php
include '../../conexion/conectado.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $userId = $_GET['id'];
    $table = $_GET['table'];

    $sql = "SELECT * FROM movements WHERE user_id = $userId AND user_table = '$table'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<p>Movimiento: " . $row["movement_description"] . "</p>";
        }
    } else {
        echo "<p>No hay movimientos registrados para este usuario.</p>";
    }

    $conn->close();
}
?>
