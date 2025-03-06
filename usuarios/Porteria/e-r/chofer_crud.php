<?php
require_once '../../../conexion/conectado.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
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

// Procesar la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $chofer = $_POST['nombre'] ?? '';
            $fecha = $_POST['fecha'] ?? date('Y-m-d');
            $hora = $_POST['hora'] ?? date('H:i:s');
            
            if (empty($chofer)) {
                sendResponse(false, 'El nombre del chofer es requerido');
            }
            
            $stmt = $conn->prepare("INSERT INTO data_chofer (Chofer, F_Ingreso, H_ing) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $chofer, $fecha, $hora);
            
            if ($stmt->execute()) {
                sendResponse(true, 'Chofer agregado correctamente', ['id' => $conn->insert_id]);
            } else {
                sendResponse(false, 'Error al agregar el chofer: ' . $stmt->error);
            }
            break;
            
        case 'update':
            $id = $_POST['id'] ?? '';
            $chofer = $_POST['nombre'] ?? '';
            $fecha = $_POST['fecha'] ?? '';
            $hora = $_POST['hora'] ?? '';
            
            if (empty($id) || empty($chofer)) {
                sendResponse(false, 'ID y nombre del chofer son requeridos');
            }
            
            $stmt = $conn->prepare("UPDATE data_chofer SET Chofer = ?, F_Ingreso = ?, H_ing = ? WHERE id = ?");
            $stmt->bind_param("sssi", $chofer, $fecha, $hora, $id);
            
            if ($stmt->execute()) {
                sendResponse(true, 'Chofer actualizado correctamente');
            } else {
                sendResponse(false, 'Error al actualizar el chofer: ' . $stmt->error);
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                sendResponse(false, 'ID es requerido');
            }
            
            $stmt = $conn->prepare("DELETE FROM data_chofer WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                sendResponse(true, 'Chofer eliminado correctamente');
            } else {
                sendResponse(false, 'Error al eliminar el chofer: ' . $stmt->error);
            }
            break;
            
        default:
            sendResponse(false, 'Acción no válida');
    }
} else {
    sendResponse(false, 'Método no permitido');
}
?>
