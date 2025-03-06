<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("HTTP/1.1 401 Unauthorized");
    echo "No autorizado";
    exit();
}

include '../../conexion/conectado.php';

// Si es una petición GET, devolver el formulario HTML
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo "<p class='alert alert-danger'>ID de registro no proporcionado.</p>";
        exit;
    }

    $sql = "SELECT * FROM data_admi WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo "<p class='alert alert-danger'>Error al preparar la consulta: " . htmlspecialchars($conn->error) . "</p>";
        exit;
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute() === false) {
        echo "<p class='alert alert-danger'>Error al ejecutar la consulta: " . htmlspecialchars($stmt->error) . "</p>";
        exit;
    }

    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        echo "<p class='alert alert-danger'>Registro no encontrado.</p>";
        exit;
    }

    $row = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
?>
<form id="editRecordForm">
    <!-- Primera fila: Información básica -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label for="edit-nombre" class="form-label">Nombre:</label>
            <input type="text" class="form-control" id="edit-nombre" name="nombre" value="<?= htmlspecialchars($row['nombre']) ?>">
        </div>
        <div class="col-md-4">
            <label for="edit-fecha" class="form-label">Fecha:</label>
            <input type="date" class="form-control" id="edit-fecha" name="fecha" value="<?= htmlspecialchars($row['fecha']) ?>">
        </div>
        <div class="col-md-4">
            <label for="edit-mes" class="form-label">Mes:</label>
            <input type="number" class="form-control" id="edit-mes" name="mes" min="1" max="12" value="<?= htmlspecialchars($row['mes']) ?>">
        </div>
    </div>

    <!-- Segunda fila: Primer turno -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label for="edit-entrada" class="form-label">Entrada 1:</label>
            <input type="time" class="form-control" id="edit-entrada" name="entrada" value="<?= htmlspecialchars($row['entrada']) ?>">
        </div>
        <div class="col-md-4">
            <label for="edit-salida" class="form-label">Salida 1:</label>
            <input type="time" class="form-control" id="edit-salida" name="salida" value="<?= htmlspecialchars($row['salida']) ?>">
        </div>
        <div class="col-md-4">
            <label for="edit-entrada2" class="form-label">Entrada 2:</label>
            <input type="time" class="form-control" id="edit-entrada2" name="entrada2" value="<?= htmlspecialchars($row['entrada2']) ?>">
        </div>
    </div>

    <!-- Tercera fila: Segundo y tercer turno -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label for="edit-salida2" class="form-label">Salida 2:</label>
            <input type="time" class="form-control" id="edit-salida2" name="salida2" value="<?= htmlspecialchars($row['salida2']) ?>">
        </div>
        <div class="col-md-4">
            <label for="edit-entrada3" class="form-label">Entrada 3:</label>
            <input type="time" class="form-control" id="edit-entrada3" name="entrada3" value="<?= htmlspecialchars($row['entrada3']) ?>">
        </div>
        <div class="col-md-4">
            <label for="edit-salida3" class="form-label">Salida 3:</label>
            <input type="time" class="form-control" id="edit-salida3" name="salida3" value="<?= htmlspecialchars($row['salida3']) ?>">
        </div>
    </div>

    <!-- Cuarta fila: Observaciones -->
    <div class="row g-3 mb-3">
        <div class="col-12">
            <label for="edit-obs" class="form-label">Observaciones:</label>
            <textarea class="form-control" id="edit-obs" name="obs" rows="3"><?= htmlspecialchars($row['obs'] ?? '') ?></textarea>
        </div>
    </div>
</form>
<?php
    exit;
}

// Si es una petición POST, actualizar el registro (mantener el formato JSON)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de registro no proporcionado']);
        exit;
    }

    $nombre = $_POST['nombre'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $entrada = $_POST['entrada'] ?? '';
    $salida = $_POST['salida'] ?? '';
    $entrada2 = $_POST['entrada2'] ?? '';
    $salida2 = $_POST['salida2'] ?? '';
    $entrada3 = $_POST['entrada3'] ?? '';
    $salida3 = $_POST['salida3'] ?? '';
    $obs = $_POST['obs'] ?? '';
    $mes = $_POST['mes'] ?? '';

    $sql = "UPDATE data_admi SET 
            nombre = ?, 
            fecha = ?, 
            entrada = ?, 
            salida = ?, 
            entrada2 = ?, 
            salida2 = ?, 
            entrada3 = ?, 
            salida3 = ?, 
            obs = ?, 
            mes = ? 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssssssssssi", 
        $nombre, 
        $fecha, 
        $entrada, 
        $salida, 
        $entrada2, 
        $salida2, 
        $entrada3, 
        $salida3, 
        $obs, 
        $mes, 
        $id
    );

    if ($stmt->execute() === false) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el registro: ' . $stmt->error]);
        exit;
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Registro actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se realizaron cambios en el registro']);
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Si el método no es GET ni POST
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Método no permitido']);
exit;
