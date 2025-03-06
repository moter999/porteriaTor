<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(["error" => "No autorizado"]);
    exit();
}

include '../../conexion/conectado.php';

$date = $_GET['date'] ?? null;

if (!$date) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(["error" => "Fecha no proporcionada"]);
    exit;
}

$sql = "SELECT * FROM data_admi WHERE fecha = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(["error" => "Error al preparar la consulta: " . $conn->error]);
    exit;
}

$stmt->bind_param("s", $date);

if ($stmt->execute() === false) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(["error" => "Error al ejecutar la consulta: " . $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$records = [];

while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode(["records" => $records]);
?>
