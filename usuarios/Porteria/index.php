<?php
// Iniciar o reanudar la sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    // Establecer un ID de usuario temporal para pruebas (solo en desarrollo)
    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
    } else {
        // Redirigir al login en producción
        header('Location: ../../login.php');
        exit;
    }
}

// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Gestión de Entradas y Salidas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <style>
        /* Estilos base modernizados */
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --secondary: #64748b;
            --success: #059669;
            --danger: #dc2626;
            --warning: #d97706;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
        }

        /* Fondo moderno con gradiente suave */
        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
        }

        /* Sidebar moderno */
        .w-64 {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.05);
        }

        /* Contenedor principal optimizado */
        .main-container {
            max-height: 100vh;
            overflow-y: auto;
            padding: 1.5rem;
        }

        /* Tabla optimizada */
        .table-container {
            background: var(--surface);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .compact-table {
            font-size: 0.875rem;
        }

        .compact-table th {
            padding: 0.75rem 1rem;
            background: linear-gradient(90deg, #1e293b 0%, #334155 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .compact-table td {
            padding: 0.75rem 1rem;
            white-space: nowrap;
        }

        /* Botones de acción en la tabla */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            padding: 0.25rem;
        }

        .action-button {
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .action-button:hover {
            transform: translateY(-2px);
        }

        /* Campos de búsqueda modernos */
        .search-container {
            position: relative;
            max-width: 24rem;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            background: white;
            transition: all 0.2s;
        }

        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Diseño responsivo mejorado */
        @media (max-width: 1280px) {
            .compact-table {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
<div class="flex">
    <!-- Sidebar mejorado -->
    <div class="w-64 bg-gray-900 h-screen p-4 fixed">
        <div class="flex flex-col items-center">
            <div class="w-24 h-24 rounded-full overflow-hidden ring-4 ring-blue-500 ring-opacity-50 mb-4">
                <img alt="User avatar" class="w-full h-full object-cover" src="https://storage.googleapis.com/a1aa/image/My5atNx3knf6zUdmV9HhqutfLt9jh2BUHHtRL4v7deo.jpg"/>
            </div>
            <h2 class="text-white text-xl font-semibold mb-8">Bienvenido, admi</h2>
        </div>
        <nav class="text-white">
        <ul>
    <li class="mb-4">
        <a class="flex items-center cursor-pointer hover:bg-gray-700 p-2 rounded transition duration-300" onclick="openModal('addModal')">
            <i class="fas fa-plus mr-2"></i> Agregar Registro
        </a>
    </li>
    <li class="mb-4">
        <a class="flex items-center cursor-pointer hover:bg-gray-700 p-2 rounded transition duration-300" onclick="viewTodayRecords()">
            <i class="fas fa-calendar-day mr-2"></i> Ver Registros de Hoy
        </a>
    </li>
    <li class="mb-4">
        <a class="flex items-center cursor-pointer hover:bg-gray-700 p-2 rounded transition duration-300" onclick="viewAllRecords()">
            <i class="fas fa-list mr-2"></i> Ver Todos los Registros
        </a>
    </li>
    <li class="mb-4">
        <a class="flex items-center cursor-pointer hover:bg-gray-700 p-2 rounded transition duration-300" href="e-r/index.php">
            <i class="fas fa-truck mr-2"></i> Gestionar Transporte
        </a>
    </li>
    <li class="mb-4">
        <a class="flex items-center cursor-pointer hover:bg-gray-700 p-2 rounded transition duration-300" onclick="logout()">
            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
        </a>
    </li>
</ul>
        </nav>
    </div>
    <!-- Main Content -->
    <div class="flex-1 pl-64">
        <div class="main-container">
            <div class="max-w-full mx-auto">
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                                <i class="fas fa-clipboard-list text-blue-500 mr-3"></i>
                                Gestión de Entradas y Salidas
                            </h1>
                            <p class="text-gray-600 mt-1" id="currentView">Registros de Hoy</p>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="search-container">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" id="search" class="search-input" placeholder="Buscar registros..." onkeyup="filterTable()"/>
                            </div>
                            
                            <button class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transform hover:scale-105 transition-all duration-300 flex items-center gap-2 shadow-lg" onclick="openModal('addModal')">
                                <i class="fas fa-plus-circle"></i>
                                Agregar
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="min-w-full compact-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Fecha</th>
                                    <th>E1</th>
                                    <th>S1</th>
                                    <th>E2</th>
                                    <th>S2</th>
                                    <th>E3</th>
                                    <th>S3</th>
                                    <th>Obs.</th>
                                    <th>Mes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                            <?php
                            include '../../conexion/conectado.php';
                            $sql = "SELECT * FROM data_admi ORDER BY id DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr class='hover:bg-gray-50 transition-colors'>";
                                    echo "<td>{$row['id']}</td>";
                                    echo "<td>{$row['nombre']}</td>";
                                    echo "<td>{$row['fecha']}</td>";
                                    echo "<td>{$row['entrada']}</td>";
                                    echo "<td>{$row['salida']}</td>";
                                    echo "<td>{$row['entrada2']}</td>";
                                    echo "<td>{$row['salida2']}</td>";
                                    echo "<td>{$row['entrada3']}</td>";
                                    echo "<td>{$row['salida3']}</td>";
                                    echo "<td class='max-w-xs truncate'>{$row['obs']}</td>";
                                    echo "<td>{$row['mes']}</td>";
                                    echo "<td>
                                        <div class='action-buttons'>
                                            <button class='action-button text-blue-500 hover:text-blue-700' onclick='viewRecord({$row['id']})'>
                                                <i class='fas fa-eye'></i>
                                            </button>
                                            <button class='action-button text-yellow-500 hover:text-yellow-700' onclick='editRecord({$row['id']})'>
                                                <i class='fas fa-edit'></i>
                                            </button>
                                            <button class='action-button text-red-500 hover:text-red-700' onclick='deleteRecord({$row['id']})'>
                                                <i class='fas fa-trash'></i>
                                            </button>
                                        </div>
                                    </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='12' class='text-center py-4 text-gray-500'>No se encontraron registros</td></tr>";
                            }
                            $conn->close();
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Add Modal -->
<div class="modal fixed w-full h-full top-0 left-0 flex items-center justify-center opacity-0 pointer-events-none" id="addModal">
    <div class="modal-overlay absolute w-full h-full bg-gray-900 opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-4xl mx-auto rounded-2xl shadow-2xl z-50 overflow-y-auto">
        <div class="modal-content py-6 text-left px-8">
            <!-- Título -->
            <div class="flex justify-between items-center pb-4 border-b border-gray-200 mb-6">
                <p class="text-3xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-plus-circle text-blue-500 mr-3"></i>
                    Agregar Registro
                </p>
                <div class="modal-close cursor-pointer z-50 hover:bg-gray-100 rounded-full p-2 transition-all duration-300" onclick="closeModal('addModal')">
                    <svg class="fill-current text-gray-500 hover:text-gray-800" height="24" viewbox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path>
                    </svg>
                </div>
            </div>
            <!-- Body -->
            <div class="my-5">
                <form id="addForm">
                    <input type="hidden" name="action" value="add">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="form-group">
                            <label class="block text-gray-700 text-sm font-bold mb-3 flex items-center" for="nombre">
                                <i class="fas fa-user text-blue-500 mr-2"></i>
                                Nombre
                            </label>
                            <input class="w-full px-4 py-3 rounded-xl border-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" id="nombre" name="nombre" placeholder="Nombre completo" type="text" required/>
                        </div>
                        <div class="form-group">
                            <label class="block text-gray-700 text-sm font-bold mb-3 flex items-center" for="fecha">
                                <i class="fas fa-calendar text-blue-500 mr-2"></i>
                                Fecha
                            </label>
                            <input class="w-full px-4 py-3 rounded-xl border-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" id="fecha" name="fecha" type="date" required/>
                        </div>
                        <div class="form-group">
                            <label class="block text-gray-700 text-sm font-bold mb-3 flex items-center" for="entrada">
                                <i class="fas fa-clock text-blue-500 mr-2"></i>
                                Entrada
                            </label>
                            <input class="w-full px-4 py-3 rounded-xl border-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" id="entrada" name="entrada" type="time" required/>
                        </div>
                        <div class="form-group">
                            <label class="block text-gray-700 text-sm font-bold mb-3 flex items-center" for="salida">
                                <i class="fas fa-clock text-blue-500 mr-2"></i>
                                Salida
                            </label>
                            <input class="w-full px-4 py-3 rounded-xl border-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" id="salida" name="salida" type="time" required/>
                        </div>
                        <div class="form-group">
                            <label class="block text-gray-700 text-sm font-bold mb-3 flex items-center" for="entrada2">
                                <i class="fas fa-clock text-blue-500 mr-2"></i>
                                Entrada 2
                            </label>
                            <input class="w-full px-4 py-3 rounded-xl border-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" id="entrada2" name="entrada2" type="time"/>
                        </div>
                        <div class="form-group">
                            <label class="block text-gray-700 text-sm font-bold mb-3 flex items-center" for="salida2">
                                <i class="fas fa-clock text-blue-500 mr-2"></i>
                                Salida 2
                            </label>
                            <input class="w-full px-4 py-3 rounded-xl border-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" id="salida2" name="salida2" type="time"/>
                        </div>
                        <div class="form-group">
                            <label class="block text-gray-700 text-sm font-bold mb-3 flex items-center" for="entrada3">
                                <i class="fas fa-clock text-blue-500 mr-2"></i>
                                Entrada 3
                            </label>
                            <input class="w-full px-4 py-3 rounded-xl border-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" id="entrada3" name="entrada3" type="time"/>
                        </div>
                        <div class="form-group">
                            <label class="block text-gray-700 text-sm font-bold mb-3 flex items-center" for="salida3">
                                <i class="fas fa-clock text-blue-500 mr-2"></i>
                                Salida 3
                            </label>
                            <input class="w-full px-4 py-3 rounded-xl border-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" id="salida3" name="salida3" type="time"/>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-3 flex items-center" for="obs">
                                <i class="fas fa-comment-alt text-blue-500 mr-2"></i>
                                Observaciones
                            </label>
                            <textarea class="w-full px-4 py-3 rounded-xl border-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" id="obs" name="obs" placeholder="Ingrese sus observaciones aquí..." rows="3"></textarea>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-3 flex items-center" for="mes">
                                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                Mes
                            </label>
                            <input class="w-full px-4 py-3 rounded-xl border-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" id="mes" name="mes" placeholder="Mes" type="text" required/>
                        </div>
                    </div>
                    <div class="flex justify-end gap-4 mt-8">
                        <button class="px-6 py-3 rounded-xl bg-gray-200 text-gray-700 hover:bg-gray-300 transform hover:scale-105 transition-all duration-300 flex items-center gap-2" type="button" onclick="closeModal('addModal')">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                        <button class="px-6 py-3 rounded-xl bg-blue-500 text-white hover:bg-blue-600 transform hover:scale-105 transition-all duration-300 flex items-center gap-2 shadow-lg" type="submit">
                            <i class="fas fa-save"></i>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Edit Modal -->
<div class="modal fixed w-full h-full top-0 left-0 flex items-center justify-center opacity-0 pointer-events-none" id="editModal">
    <div class="modal-overlay absolute w-full h-full bg-gray-900 opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-2xl mx-auto rounded shadow-lg z-50 overflow-y-auto">
        <div class="modal-content py-4 text-left px-6">
            <!-- Title -->
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <p class="text-2xl font-bold text-gray-800"><i class="fas fa-edit mr-2 text-yellow-500"></i> Editar Registro</p>
                <div class="modal-close cursor-pointer z-50" onclick="closeModal('editModal')">
                    <svg class="fill-current text-black" height="18" viewbox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14.53 3.47a.75.75 0 00-1.06 0L9 7.94 4.53 3.47a.75.75 0 10-1.06 1.06L7.94 9l-4.47 4.47a.75.75 0 101.06 1.06L9 10.06l4.47 4.47a.75.75 0 101.06-1.06L10.06 9l4.47-4.47a.75.75 0 000-1.06z"></path>
                    </svg>
                </div>
            </div>
            <!-- Body -->
            <div class="my-5">
                <form id="editForm" action="process.php" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="editId" name="id">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="editNombre">Nombre</label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="editNombre" name="nombre" placeholder="Nombre" type="text" required/>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="editFecha">Fecha</label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="editFecha" name="fecha" type="date" required/>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="editEntrada">Entrada</label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="editEntrada" name="entrada" type="time" required/>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="editSalida">Salida</label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="editSalida" name="salida" type="time" required/>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="editEntrada2">Entrada 2</label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="editEntrada2" name="entrada2" type="time"/>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="editSalida2">Salida 2</label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="editSalida2" name="salida2" type="time"/>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="editEntrada3">Entrada 3</label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="editEntrada3" name="entrada3" type="time"/>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="editSalida3">Salida 3</label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="editSalida3" name="salida3" type="time"/>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="editObs">Observaciones</label>
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="editObs" name="obs" placeholder="Observaciones" required></textarea>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="editMes">Mes</label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="editMes" name="mes" placeholder="Mes" type="text" required/>
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300" type="submit">Guardar</button>
                        <button class="modal-close px-4 py-2 rounded bg-gray-500 text-white ml-2 hover:bg-gray-700 transition duration-300" type="button" onclick="closeModal('editModal')">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Delete Modal -->
<div class="modal fixed w-full h-full top-0 left-0 flex items-center justify-center opacity-0 pointer-events-none" id="deleteModal">
    <div class="modal-overlay absolute w-full h-full bg-gray-900 opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto">
        <div class="modal-content py-4 text-left px-6">
            <!-- Title -->
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <p class="text-2xl font-bold text-gray-800"><i class="fas fa-trash mr-2 text-red-500"></i> Eliminar Registro</p>
                <div class="modal-close cursor-pointer z-50" onclick="closeModal('deleteModal')">
                    <svg class="fill-current text-black" height="18" viewbox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14.53 3.47a.75.75 0 00-1.06 0L9 7.94 4.53 3.47a.75.75 0 10-1.06 1.06L7.94 9l-4.47 4.47a.75.75 0 101.06 1.06L9 10.06l4.47 4.47a.75.75 0 101.06-1.06L10.06 9l4.47-4.47a.75.75 0 000-1.06z"></path>
                    </svg>
                </div>
            </div>
            <!-- Body -->
            <div class="my-5">
                <p class="text-gray-700">¿Está seguro de que desea eliminar este registro?</p>
                <form id="deleteForm" action="process.php" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="deleteId" name="id">
                    <div class="flex justify-end pt-2">
                        <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700 transition duration-300" type="submit">Eliminar</button>
                        <button class="modal-close px-4 py-2 rounded bg-gray-500 text-white ml-2 hover:bg-gray-700 transition duration-300" type="button" onclick="closeModal('deleteModal')">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- View Modal -->
<div class="modal fixed w-full h-full top-0 left-0 flex items-center justify-center opacity-0 pointer-events-none" id="viewModal">
    <div class="modal-overlay absolute w-full h-full bg-gray-900 opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto">
        <div class="modal-content py-4 text-left px-6">
            <!-- Title -->
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <p class="text-2xl font-bold text-gray-800"><i class="fas fa-eye mr-2 text-blue-500"></i> Ver Registro</p>
                <div class="modal-close cursor-pointer z-50" onclick="closeModal('viewModal')">
                    <svg class="fill-current text-black" height="18" viewbox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14.53 3.47a.75.75 0 00-1.06 0L9 7.94 4.53 3.47a.75.75 0 10-1.06 1.06L7.94 9l-4.47 4.47a.75.75 0 101.06 1.06L9 10.06l4.47 4.47a.75.75 0 101.06-1.06L10.06 9l4.47-4.47a.75.75 0 000-1.06z"></path>
                    </svg>
                </div>
            </div>
            <!-- Body -->
            <div class="my-5">
                <div id="viewDetails">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="viewMes">Mes:</label>
                    <select id="viewMes" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
                        <!-- Options will be populated dynamically -->
                    </select>
                    <div id="recordDetails">
                        <!-- Record details will be populated dynamically -->
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <div class="flex justify-end pt-2">
                <button class="modal-close px-4 py-2 rounded bg-gray-500 text-white ml-2 hover:bg-gray-700 transition duration-300" type="button" onclick="closeModal('viewModal')">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<div id="message" class="fixed top-4 right-4 z-50"></div>
<script src="js/scripts.js"></script>
</body>
</html>
