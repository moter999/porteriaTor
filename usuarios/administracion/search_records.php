<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("HTTP/1.1 401 Unauthorized");
    echo "No autorizado";
    exit();
}

// Desactivar la visualización de errores
ini_set('display_errors', '0'); // Asegúrate de que no se muestren errores
ini_set('log_errors', '1'); // Habilitar el registro de errores
ini_set('error_log', '../administracion/..usuarios/e-r/error.php'); // Cambia esta ruta a donde quieras guardar el log

include '../../conexion/conectado.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['type'] ?? '';
    $searchResults = [];
    $error = null;

    try {
        switch ($type) {
            case 'name':
                $term = $_POST['term'] ?? '';
                if (empty($term)) {
                    throw new Exception("Por favor ingrese un nombre para buscar");
                }
                $sql = "SELECT * FROM data_admi WHERE nombre LIKE ? ORDER BY fecha DESC, id DESC";
                $stmt = $conn->prepare($sql);
                $searchTerm = "%$term%";
                $stmt->bind_param("s", $searchTerm);
                break;

            case 'date_range':
                $startDate = $_POST['start_date'] ?? '';
                $endDate = $_POST['end_date'] ?? '';
                $name = $_POST['name'] ?? '';
                
                if (empty($startDate) || empty($endDate)) {
                    throw new Exception("Por favor seleccione ambas fechas");
                }
                
                if (!empty($name)) {
                    // Búsqueda combinada por nombre y rango de fechas
                    $sql = "SELECT * FROM data_admi WHERE nombre LIKE ? AND fecha BETWEEN ? AND ? ORDER BY fecha DESC, id DESC";
                    $stmt = $conn->prepare($sql);
                    $searchName = "%$name%";
                    $stmt->bind_param("sss", $searchName, $startDate, $endDate);
                } else {
                    // Solo búsqueda por rango de fechas
                    $sql = "SELECT * FROM data_admi WHERE fecha BETWEEN ? AND ? ORDER BY fecha DESC, id DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $startDate, $endDate);
                }
                break;

            default:
                throw new Exception("Tipo de búsqueda no válido");
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo '<div class="table-responsive">';
            echo '<table class="table table-striped table-bordered">';
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
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['nombre'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['fecha'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['entrada'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['salida'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['entrada2'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['salida2'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['entrada3'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['salida3'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['obs'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['mes'] ?? '') . "</td>";
                echo "<td>
                        <button class='btn btn-warning btn-sm' onclick='openEditModal(" . ($row['id'] ?? 0) . ")'><i class='fas fa-edit'></i> Editar</button>
                        <button class='btn btn-danger btn-sm' onclick='deleteRecord(" . ($row['id'] ?? 0) . ")'><i class='fas fa-trash'></i> Eliminar</button>
                      </td>";
                echo "</tr>";
            }
            
            echo '</tbody></table>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">No se encontraron registros.</div>';
        }

        $stmt->close();
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

$conn->close();
?>
