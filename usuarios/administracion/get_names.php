<?php
include '../../conexion/conectado.php';

$stmt = $conn->prepare("SELECT DISTINCT nombre FROM data_admi");
$stmt->execute();
$result = $stmt->get_result();

$names = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $names[] = $row['nombre'];
    }
}

echo json_encode(['names' => $names]);

$stmt->close();
$conn->close();
?>