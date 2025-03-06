<?php
// Iniciar sesión y verificar autenticación
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Habilitar visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Establecer el tipo de contenido como JSON
header('Content-Type: application/json');

try {
    // Verificar que se proporcionó un ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID de registro no válido');
    }

    $id = intval($_GET['id']);

    // Incluir el archivo de conexión
    require_once '../../conexion/conectado.php';

    // Preparar la consulta
    $stmt = $conn->prepare("SELECT * FROM data_admi WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . $conn->error);
    }

    // Vincular parámetros y ejecutar
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
    }

    // Obtener resultados
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Error al obtener resultados: ' . $stmt->error);
    }

    // Verificar si se encontró el registro
    if ($result->num_rows === 0) {
        throw new Exception('No se encontró el registro');
    }

    // Obtener los datos
    $record = $result->fetch_assoc();

    // Cerrar la declaración y la conexión
    $stmt->close();
    $conn->close();

    // Devolver los datos como JSON
    echo json_encode([
        'success' => true,
        'message' => 'Registro encontrado',
        'id' => $record['id'],
        'nombre' => $record['nombre'],
        'fecha' => $record['fecha'],
        'entrada' => $record['entrada'],
        'salida' => $record['salida'],
        'entrada2' => $record['entrada2'],
        'salida2' => $record['salida2'],
        'entrada3' => $record['entrada3'],
        'salida3' => $record['salida3'],
        'obs' => $record['obs'],
        'mes' => $record['mes']
    ]);

} catch (Exception $e) {
    // Manejar cualquier error
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}