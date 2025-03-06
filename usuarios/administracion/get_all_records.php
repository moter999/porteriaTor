<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode([
        "success" => false,
        "error" => "No autorizado",
        "message" => "Sesión no válida"
    ]);
    exit();
}

include '../../conexion/conectado.php';

try {
    $sql = "SELECT * FROM data_admi ORDER BY fecha DESC, id DESC";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta");
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta");
    }
    
    $result = $stmt->get_result();
    
    echo '<h2>Ver Todos los Registros</h2>';
    
    if ($result->num_rows > 0) {
        echo '<table class="table table-striped">';
        echo '<thead><tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Entrada 1</th>
                <th>Salida 1</th>
                <th>Entrada 2</th>
                <th>Salida 2</th>
                <th>Entrada 3</th>
                <th>Salida 3</th>
                <th>Observaciones</th>
                <th>Mes</th>
                <th>Acciones</th>
              </tr></thead>';
        echo '<tbody>';
        
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($row['fecha']) . "</td>";
            echo "<td>" . htmlspecialchars($row['entrada']) . "</td>";
            echo "<td>" . htmlspecialchars($row['salida']) . "</td>";
            echo "<td>" . htmlspecialchars($row['entrada2']) . "</td>";
            echo "<td>" . htmlspecialchars($row['salida2']) . "</td>";
            echo "<td>" . htmlspecialchars($row['entrada3']) . "</td>";
            echo "<td>" . htmlspecialchars($row['salida3']) . "</td>";
            echo "<td>" . htmlspecialchars($row['obs']) . "</td>";
            echo "<td>" . htmlspecialchars($row['mes']) . "</td>";
            echo "<td>
                    <button class='btn btn-warning btn-sm' onclick='openEditModal(" . $row['id'] . ")'><i class='fas fa-edit'></i> Editar</button>
                    <button class='btn btn-danger btn-sm' onclick='deleteRecord(" . $row['id'] . ")'><i class='fas fa-trash'></i> Eliminar</button>
                  </td>";
            echo "</tr>";
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-info">No se encontraron registros.</div>';
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error al cargar los registros: ' . htmlspecialchars($e->getMessage()) . '</div>';
} finally {
    $conn->close();
}
?> 