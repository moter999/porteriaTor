<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

include '../../conexion/conectado.php';

// Asegurarse de que la respuesta sea siempre JSON
header('Content-Type: application/json');

try {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Método no permitido");
    }

    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        throw new Exception("ID no proporcionado");
    }

    // Preparar la consulta
    $stmt = $conn->prepare("DELETE FROM data_admi WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conn->error);
    }

    // Vincular parámetros
    $stmt->bind_param("i", $id);

    // Ejecutar la consulta
    if (!$stmt->execute()) {
        throw new Exception("Error al eliminar el registro: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => "Registro #$id eliminado correctamente"
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => "No se encontró el registro #$id"
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>
