<?php
session_start();
require_once '../../../conexion/conectado.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Obtener datos ingresados hoy
$hoy = date('Y-m-d');
$sqlHoy = "SELECT * FROM data_chofer WHERE F_Ingreso = ?";
$stmtHoy = $conn->prepare($sqlHoy);
$stmtHoy->bind_param("s", $hoy);
$stmtHoy->execute();
$resultHoy = $stmtHoy->get_result();

// Leer todos los datos
$orderBy = "id ASC";
if (isset($_GET['order'])) {
    switch ($_GET['order']) {
        case 'id_asc': $orderBy = "id ASC"; break;
        case 'id_desc': $orderBy = "id DESC"; break;
        case 'mes': $orderBy = "MONTH(F_Ingreso) ASC"; break;
        case 'hora': $orderBy = "H_ing ASC"; break;
    }
}

// Modificar la consulta SQL para manejar las diferentes vistas
$view = isset($_GET['view']) ? $_GET['view'] : 'default';
$sql = "SELECT * FROM data_chofer";

switch($view) {
    case 'all':
        $sql .= " ORDER BY id DESC";
        break;
    case 'today':
        $sql .= " WHERE F_Ingreso = ? ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        break;
    default:
        $sql .= " ORDER BY id DESC LIMIT 10";
        break;
}

if ($view !== 'today') {
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Choferes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="main.js" defer></script>
    <script src="modal-functions.js" defer></script>
    <style>
        /* Estilos actualizados para la tabla */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            background-color: var(--card-background);
        }

        .data-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
        }

        .data-table tr:hover td {
            background-color: rgba(37, 99, 235, 0.05);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table-wrapper {
            overflow-x: auto;
            margin: 1.5rem 0;
            border-radius: 0.75rem;
        }

        /* Estilos para modales */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 0.75rem;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }

        .modal.active .modal-content {
            transform: translateY(0);
        }

        /* Estilos para botones */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            font-size: 0.95rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-danger {
            background-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
        }

        /* Resto de los estilos... */
    </style>
</head>
<body>
    <!-- Resto del contenido HTML... -->
</body>
</html>
