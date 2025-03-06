<?php
include '../../conexion/conectado.php';

header('Content-Type: application/json');

// Función para agregar un registro
function addRecord($nombre, $fecha, $entrada, $salida, $entrada2, $salida2, $entrada3, $salida3, $obs, $mes) {
    global $conn;
    try {
        // Validar datos
        if (empty($nombre) || empty($fecha) || empty($mes)) {
            throw new Exception("Los campos nombre, fecha y mes son obligatorios");
        }

        // Preparar la consulta
        $sql = "INSERT INTO data_admi (nombre, fecha, entrada, salida, entrada2, salida2, entrada3, salida3, obs, mes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }

        // Todos los campos son strings en la base de datos
        $stmt->bind_param("ssssssssss", $nombre, $fecha, $entrada, $salida, $entrada2, $salida2, $entrada3, $salida3, $obs, $mes);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        return true;
    } catch (Exception $e) {
        error_log("Error en addRecord: " . $e->getMessage());
        throw $e;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

// Función para editar un registro
function editRecord($id, $nombre, $fecha, $entrada, $salida, $entrada2, $salida2, $entrada3, $salida3, $obs, $mes) {
    global $conn;
    try {
        if (empty($id) || empty($nombre) || empty($fecha) || empty($mes)) {
            throw new Exception("Los campos ID, nombre, fecha y mes son obligatorios");
        }

        $sql = "UPDATE data_admi SET nombre=?, fecha=?, entrada=?, salida=?, entrada2=?, salida2=?, entrada3=?, salida3=?, obs=?, mes=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }

        $stmt->bind_param("ssssssssssi", $nombre, $fecha, $entrada, $salida, $entrada2, $salida2, $entrada3, $salida3, $obs, $mes, $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        return true;
    } catch (Exception $e) {
        error_log("Error en editRecord: " . $e->getMessage());
        throw $e;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

// Función para eliminar un registro
function deleteRecord($id) {
    global $conn;
    $sql = "DELETE FROM data_admi WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Función para obtener detalles de un registro
function getRecordDetails($id) {
    global $conn;
    $sql = "SELECT * FROM data_admi WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Función para obtener registros de hoy
function getTodayRecords() {
    global $conn;
    $today = date('Y-m-d');
    $sql = "SELECT * FROM data_admi WHERE fecha = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Manejar las acciones del formulario
if (isset($_POST['action'])) {
    try {
        $response = array();
        $action = $_POST['action'];

        switch ($action) {
            case 'add':
                if (addRecord(
                    $_POST['nombre'] ?? '',
                    $_POST['fecha'] ?? '',
                    $_POST['entrada'] ?? '',
                    $_POST['salida'] ?? '',
                    $_POST['entrada2'] ?? '',
                    $_POST['salida2'] ?? '',
                    $_POST['entrada3'] ?? '',
                    $_POST['salida3'] ?? '',
                    $_POST['obs'] ?? '',
                    $_POST['mes'] ?? ''
                )) {
                    $response = array(
                        "success" => true,
                        "message" => "Registro agregado correctamente"
                    );
                }
                break;

            case 'edit':
                if (editRecord(
                    $_POST['id'] ?? '',
                    $_POST['nombre'] ?? '',
                    $_POST['fecha'] ?? '',
                    $_POST['entrada'] ?? '',
                    $_POST['salida'] ?? '',
                    $_POST['entrada2'] ?? '',
                    $_POST['salida2'] ?? '',
                    $_POST['entrada3'] ?? '',
                    $_POST['salida3'] ?? '',
                    $_POST['obs'] ?? '',
                    $_POST['mes'] ?? ''
                )) {
                    $response = array(
                        "success" => true,
                        "message" => "Registro actualizado correctamente"
                    );
                }
                break;

            case 'delete':
                if (deleteRecord($_POST['id'])) {
                    $response = array(
                        "success" => true,
                        "message" => "Registro eliminado correctamente"
                    );
                }
                break;

            default:
                throw new Exception("Acción no válida");
        }

        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(array(
            "success" => false,
            "error" => $e->getMessage(),
            "message" => "Error en la operación"
        ));
    }
    exit();
}
?>
