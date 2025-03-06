<?php
require_once '../../../conexion/conectado.php';
require_once 'config.php';

// Verificar autenticación
checkAuth();

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

// Obtener los datos actualizados
$sql = "SELECT * FROM data_chofer ORDER BY id DESC";
$result = $conn->query($sql);

if ($result) {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = sanitizeData([
            'id' => $row['id'],
            'nombre' => $row['Nombre'],
            'fecha' => $row['F_Ingreso'],
            'hora' => $row['H_ing']
        ]);
    }
    sendResponse(true, 'Datos actualizados correctamente', $data);
} else {
    sendResponse(false, 'Error al obtener los datos: ' . $conn->error);
}
?>
