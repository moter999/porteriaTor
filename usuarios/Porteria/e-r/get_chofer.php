<?php
session_start();
require_once '../../../conexion/conectado.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit();
}

// Función para enviar respuesta JSON
function sendResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

try {
    // Validar y sanitizar el ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID inválido o no proporcionado');
    }

    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id === false) {
        throw new Exception('ID debe ser un número entero');
    }

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare("SELECT * FROM data_chofer WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta');
    }

    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar la consulta');
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('No se encontraron datos para el ID proporcionado');
    }

    $data = $result->fetch_assoc();
    
    // Formatear las horas antes de sanitizar
    if (isset($data['H_ing'])) {
        $data['H_ing'] = date('H:i', strtotime($data['H_ing']));
    }
    if (isset($data['H_Sal'])) {
        $data['H_Sal'] = date('H:i', strtotime($data['H_Sal']));
    }
    
    // Sanitizar los datos manualmente
    $sanitizedData = array();
    foreach ($data as $key => $value) {
        $sanitizedData[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    sendResponse(true, 'Datos obtenidos correctamente', $sanitizedData);

} catch (Exception $e) {
    error_log("Error en get_chofer.php: " . $e->getMessage());
    sendResponse(false, $e->getMessage());
}
?>