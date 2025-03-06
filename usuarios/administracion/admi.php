<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../login.php");
    exit();
}

date_default_timezone_set('America/Bogota');

$usuario = $_SESSION["usuario"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración de Usuarios</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Contenedor para alertas -->
    <div id="liveAlertPlaceholder"></div>

    <!-- Sidebar -->
    <div class="sidebar">
        <h3>
            <i class="fas fa-shield-alt me-2"></i>
            Administración
        </h3>
        <div class="user-info mb-4">
            <i class="fas fa-user-circle me-2"></i>
            <span>Administrador: <?php echo htmlspecialchars($usuario); ?></span>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item" id="viewTodayRecords" onclick="showSection('viewTodayRecordsSection'); loadTodayRecords();">
                <i class="fas fa-calendar-day"></i>
                <span>Registros del Día</span>
            </li>
            <li class="nav-item" id="viewAllRecords" onclick="showSection('viewAllRecordsSection')">
                <i class="fas fa-list-alt"></i>
                <span>Ver Todos los Registros</span>
            </li>
            <li class="nav-item" onclick="showSearchSection()">
                <i class="fas fa-search"></i>
                <span>Búsqueda Avanzada</span>
            </li>
            <li class="nav-item" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </li>
        </ul>
    </div>

    <!-- Contenido Principal -->
    <div class="content">
        <!-- Sección de Búsqueda -->
        <div class="section" id="searchSection" style="display:none;">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-search me-2"></i>Sistema de Búsqueda
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Búsqueda por Nombre -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="searchName" class="form-label">
                                    <i class="fas fa-user me-2"></i>Buscar Usuario
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchName" 
                                           placeholder="Nombre del usuario..." list="namesList">
                                    <button class="btn btn-primary" onclick="searchByName()">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                </div>
                                <datalist id="namesList"></datalist>
                            </div>
                        </div>
                        
                        <!-- Búsqueda por Rango de Fechas -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar me-2"></i>Filtrar por Fechas
                                </label>
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                            <input type="date" class="form-control" id="startDate" 
                                                   placeholder="Fecha inicial">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                            <input type="date" class="form-control" id="endDate" 
                                                   placeholder="Fecha final">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-primary w-100" onclick="searchByDateRange()">
                                            <i class="fas fa-filter"></i> Filtrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="searchResults" class="mt-4"></div>
        </div>

        <!-- Sección de Todos los Registros -->
        <div class="section" id="viewAllRecordsSection" style="display:none;">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Ver Todos los Registros</h5>
                </div>
                <div class="card-body">
                    <?php
                    include '../../conexion/conectado.php';
                    
                    $sql = "SELECT * FROM data_admi ORDER BY fecha DESC, id DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-hover table-sm align-middle">';
                        echo '<thead class="table-dark"><tr>
                                <th scope="col" style="width: 4%;" class="text-center">#</th>
                                <th scope="col" style="width: 12%;">Nombre</th>
                                <th scope="col" style="width: 8%;" class="text-center">Fecha</th>
                                <th scope="col" style="width: 7%;" class="text-center">E1</th>
                                <th scope="col" style="width: 7%;" class="text-center">S1</th>
                                <th scope="col" style="width: 7%;" class="text-center">E2</th>
                                <th scope="col" style="width: 7%;" class="text-center">S2</th>
                                <th scope="col" style="width: 7%;" class="text-center">E3</th>
                                <th scope="col" style="width: 7%;" class="text-center">S3</th>
                                <th scope="col" style="width: 18%;">Observaciones</th>
                                <th scope="col" style="width: 6%;" class="text-center">Mes</th>
                                <th scope="col" style="width: 10%;" class="text-center">Acciones</th>
                              </tr></thead>';
                        echo '<tbody>';
                        
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class='text-center'>" . htmlspecialchars($row['id'] ?? '') . "</td>";
                            echo "<td class='text-nowrap'>" . htmlspecialchars($row['nombre'] ?? '') . "</td>";
                            echo "<td class='text-center text-nowrap'>" . htmlspecialchars($row['fecha'] ?? '') . "</td>";
                            echo "<td class='text-center'>" . htmlspecialchars($row['entrada'] ?? '') . "</td>";
                            echo "<td class='text-center'>" . htmlspecialchars($row['salida'] ?? '') . "</td>";
                            echo "<td class='text-center'>" . htmlspecialchars($row['entrada2'] ?? '') . "</td>";
                            echo "<td class='text-center'>" . htmlspecialchars($row['salida2'] ?? '') . "</td>";
                            echo "<td class='text-center'>" . htmlspecialchars($row['entrada3'] ?? '') . "</td>";
                            echo "<td class='text-center'>" . htmlspecialchars($row['salida3'] ?? '') . "</td>";
                            echo "<td class='text-wrap'>" . htmlspecialchars($row['obs'] ?? '') . "</td>";
                            echo "<td class='text-center'>" . htmlspecialchars($row['mes'] ?? '') . "</td>";
                            echo "<td class='text-center'>
                                    <div class='btn-group btn-group-sm'>
                                        <button class='btn btn-warning' onclick='openEditModal(" . ($row['id'] ?? 0) . ")' title='Editar'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <button class='btn btn-danger ms-1' onclick='deleteRecord(" . ($row['id'] ?? 0) . ")' title='Eliminar'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </div>
                                  </td>";
                            echo "</tr>";
                        }
                        
                        echo '</tbody></table>';
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                No se encontraron registros.
                              </div>';
                    }
                    $conn->close();
                    ?>
                </div>
            </div>
        </div>

        <!-- Sección de Registros de Hoy -->
        <div class="section" id="viewTodayRecordsSection" style="display:none;">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Registros de Hoy</h5>
                </div>
                <div class="card-body table-responsive">
                    <div id="todayRecordsTable"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edición -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="fas fa-edit me-2"></i>Editar Registro
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editModalBody">
                    <!-- El contenido se carga dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveRecord()">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/scripts.js"></script>
    <script src="js/animations.js"></script>
</body>
</html>

