<?php
include '../../conexion/conectado.php';

$sql = "SELECT DISTINCT mes FROM data_admi ORDER BY mes DESC";
$result = $conn->query($sql);

$months = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $months[] = $row['mes'];
    }
}

echo json_encode($months);
$conn->close();
?>