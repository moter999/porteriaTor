<?php
// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar el buffer de salida al principio
ob_start();

// Iniciar la sesión
session_start();

// Función para enviar respuesta JSON
function sendResponse($success, $message) {
    // Limpiar cualquier salida anterior
    ob_clean();
    
    // Establecer headers
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Crear la respuesta
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    // Registrar la respuesta para depuración
    error_log("Respuesta JSON: " . json_encode($response));
    
    // Enviar respuesta JSON
    echo json_encode($response);
    
    // Terminar la ejecución
    exit();
}

try {
    // Incluir el archivo de conexión
    require_once '../../../conexion/conectado.php';

    // Verificar si el usuario está logueado
    if (!isset($_SESSION['user_id'])) {
        sendResponse(false, 'No autorizado');
    }

    // Verificar si se recibieron los datos requeridos
    $required_fields = ['nombre', 'patente', 'cod1', 'fecha_ingreso'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            sendResponse(false, "El campo $field es requerido");
        }
    }

    // Sanitizar y validar los datos
    $data = [
        'Chofer' => trim($_POST['nombre']),
        'Patente' => trim($_POST['patente']),
        'cod1' => trim($_POST['cod1']),
        'F_Ingreso' => $_POST['fecha_ingreso'],
        'H_ing' => !empty($_POST['hora_ingreso']) ? $_POST['hora_ingreso'] : null,
        'Cod2' => !empty($_POST['cod2']) ? trim($_POST['cod2']) : null,
        'K_Ing' => !empty($_POST['k_ingreso']) ? (int)$_POST['k_ingreso'] : null,
        'F_Salida' => !empty($_POST['fecha_salida']) ? $_POST['fecha_salida'] : null,
        'H_Sal' => !empty($_POST['hora_salida']) ? $_POST['hora_salida'] : null,
        'K_Sal' => !empty($_POST['k_salida']) ? (int)$_POST['k_salida'] : null,
        'T_Ocupado' => !empty($_POST['tiempo_ocupado']) ? trim($_POST['tiempo_ocupado']) : null,
        'K_Ocup' => !empty($_POST['k_ocupado']) ? (int)$_POST['k_ocupado'] : null,
        'Lugar' => !empty($_POST['lugar']) ? trim($_POST['lugar']) : null,
        'Detalle' => !empty($_POST['detalle']) ? trim($_POST['detalle']) : null
    ];

    // Validar formato de fecha
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['F_Ingreso'])) {
        throw new Exception("Formato de fecha de ingreso inválido");
    }

    if ($data['F_Salida'] && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['F_Salida'])) {
        throw new Exception("Formato de fecha de salida inválido");
    }

    // Validar formato de hora
    if ($data['H_ing'] && !preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/", $data['H_ing'])) {
        throw new Exception("Formato de hora de ingreso inválido");
    }

    if ($data['H_Sal'] && !preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/", $data['H_Sal'])) {
        throw new Exception("Formato de hora de salida inválido");
    }

    // Preparar la consulta SQL
    $sql = "INSERT INTO data_chofer (" . implode(", ", array_keys($data)) . ") VALUES (" . implode(", ", array_fill(0, count($data), "?")) . ")";
    
    // Registrar la consulta SQL para depuración
    error_log("SQL Query: " . $sql);
    error_log("Data: " . print_r($data, true));

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conn->error);
    }
    
    // Crear array de tipos para bind_param
    $types = str_repeat('s', count($data));
    
    // Crear array de valores para bind_param
    $values = array_values($data);
    
    // Agregar tipos como primer elemento del array
    array_unshift($values, $types);
    
    // Usar call_user_func_array para pasar los parámetros
    if (!call_user_func_array([$stmt, 'bind_param'], $values)) {
        throw new Exception("Error al vincular parámetros: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    sendResponse(true, 'Chofer agregado correctamente');
    
} catch (Exception $e) {
    error_log("Error en add_chofer.php: " . $e->getMessage());
    sendResponse(false, 'Error al agregar el chofer: ' . $e->getMessage());
} 