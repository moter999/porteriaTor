<?php
// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y establecer headers
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['user_id'])) {
        error_log('Usuario no autenticado. SESSION: ' . print_r($_SESSION, true));
        throw new Exception('No autorizado: Sesión no iniciada');
    }

    // Verificar la existencia del archivo de conexión
    $conexion_path = '../../conexion/conectado.php';
    if (!file_exists($conexion_path)) {
        error_log('Archivo de conexión no encontrado en: ' . realpath($conexion_path));
        throw new Exception('Error: Archivo de conexión no encontrado');
    }

    // Incluir el archivo de conexión
    require_once $conexion_path;

    // Verificar la conexión
    if (!isset($conn)) {
        error_log('Variable de conexión no definida después de incluir conectado.php');
        throw new Exception('Error: Variable de conexión no definida');
    }

    if ($conn->connect_error) {
        error_log('Error de conexión MySQL: ' . $conn->connect_error);
        throw new Exception('Error de conexión a la base de datos: ' . $conn->connect_error);
    }
    
    // Preparar la consulta SQL para obtener todos los registros
    $sql = "SELECT * FROM data_admi ORDER BY fecha DESC, id DESC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Error al preparar la consulta: ' . $conn->error);
        throw new Exception('Error al preparar la consulta: ' . $conn->error);
    }
    
    // Ejecutar la consulta
    if (!$stmt->execute()) {
        error_log('Error al ejecutar la consulta: ' . $stmt->error);
        throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        error_log('Error al obtener resultados: ' . $stmt->error);
        throw new Exception('Error al obtener resultados: ' . $stmt->error);
    }
    
    // Obtener los resultados
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = [
            'id' => $row['id'],
            'nombre' => htmlspecialchars($row['nombre'] ?? ''),
            'fecha' => $row['fecha'] ?? '',
            'entrada' => $row['entrada'] ?? '',
            'salida' => $row['salida'] ?? '',
            'entrada2' => $row['entrada2'] ?? '',
            'salida2' => $row['salida2'] ?? '',
            'entrada3' => $row['entrada3'] ?? '',
            'salida3' => $row['salida3'] ?? '',
            'obs' => htmlspecialchars($row['obs'] ?? ''),
            'mes' => htmlspecialchars($row['mes'] ?? '')
        ];
    }

    error_log('Registros encontrados: ' . count($records));
    
    // Devolver los resultados
    echo json_encode([
        'success' => true,
        'data' => $records,
        'message' => 'Registros obtenidos correctamente'
    ]);
    
} catch (Exception $e) {
    error_log('Error en get_all_records.php: ' . $e->getMessage());
    error_log('Trace: ' . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Error al obtener los registros',
        'debug_info' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
} finally {
    // Cerrar la conexión y el statement si existen
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?> 