<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

include '../../conexion/conectado.php';

$term = $_GET['term'] ?? '';
$term = $conn->real_escape_string($term);

$sql = "SELECT DISTINCT nombre FROM data_admi WHERE nombre LIKE ? LIMIT 10";
$stmt = $conn->prepare($sql);
$searchTerm = "%$term%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row['nombre'];
}

header('Content-Type: application/json');
echo json_encode($suggestions);

$stmt->close();
$conn->close();
?>
