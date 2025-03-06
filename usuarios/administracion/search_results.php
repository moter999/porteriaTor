<?php
include '../../conexion/conectado.php';

$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$mes = isset($_POST['mes']) ? $_POST['mes'] : '';

$stmt = $conn->prepare("SELECT * FROM data_admi WHERE 1=1");

if (!empty($nombre)) {
    $nombre = "%$nombre%";
    $stmt = $conn->prepare("SELECT * FROM data_admi WHERE nombre LIKE ?");
    $stmt->bind_param("s", $nombre);
} elseif (!empty($fecha)) {
    $stmt = $conn->prepare("SELECT * FROM data_admi WHERE fecha = ?");
    $stmt->bind_param("s", $fecha);
} elseif (!empty($mes)) {
    $stmt = $conn->prepare("SELECT * FROM data_admi WHERE mes = ?");
    $stmt->bind_param("i", $mes);
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Búsqueda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {font-family: 'Montserrat', sans-serif;background-color: #f4f6f9;}
        .table-responsive {overflow-x: auto;}
        .table-dark {background-color: #343a40;color: white;}
        .table-dark th, .table-dark td {border-color: #454d55;}
        .table-dark tbody tr:nth-of-type(odd) {background-color: #454d55;}
        .table-dark tbody tr:hover {background-color: #555;}
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Resultados de Búsqueda</h1>
        <?php if (isset($result) && $result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover table-dark">
                    <thead><tr><th>#</th><th>Nombre</th><th>Fecha</th><th>Entrada 1</th><th>Salida 1</th><th>Entrada 2</th><th>Salida 2</th><th>Entrada 3</th><th>Salida 3</th><th>Observaciones</th><th>Mes</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['nombre']) ?></td>
                                <td><?= htmlspecialchars($row['fecha']) ?></td>
                                <td><?= htmlspecialchars($row['entrada']) ?></td>
                                <td><?= htmlspecialchars($row['salida']) ?></td>
                                <td><?= htmlspecialchars($row['entrada2']) ?></td>
                                <td><?= htmlspecialchars($row['salida2']) ?></td>
                                <td><?= htmlspecialchars($row['entrada3']) ?></td>
                                <td><?= htmlspecialchars($row['salida3']) ?></td>
                                <td><?= htmlspecialchars($row['obs']) ?></td>
                                <td><?= htmlspecialchars($row['mes']) ?></td>
                                <td><button class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</button> <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Eliminar</button></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="alert alert-info">No se encontraron registros que coincidan con su búsqueda.</p>
        <?php endif; ?>
        <a href="admi.php" class="btn btn-secondary mt-3">Volver al Menú Principal</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>