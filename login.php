<?php
session_start();
include('conexion/conectado.php'); // Incluir el archivo de conexión

// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role']; // Nuevo campo para el rol

    if (empty($username) || empty($password) || empty($role)) {
        echo "<script>alert('Por favor complete todos los campos'); window.history.back();</script>";
        exit();
    }

    // Determinar la tabla según el rol
    $table = '';
    switch ($role) {
        case 'soporte':
            $table = 'user_soporte';
            break;
        case 'administracion':
            $table = 'user_administracion';
            break;
        case 'porteria':
            $table = 'user_porteria';
            break;
        default:
            echo "<script>alert('Rol no válido'); window.history.back();</script>";
            exit();
    }

    // Consulta para obtener el usuario por nombre de usuario
    $stmt = $conn->prepare("SELECT id, usuario, contraseña FROM $table WHERE usuario = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verificar la contraseña usando password_verify
        if (password_verify($password, $user['contraseña'])) {
            // Iniciar sesión
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['id'] = $user['id'];
            $_SESSION['role'] = $role;

            // Redirigir al portal del usuario según su rol
            switch ($role) {
                case 'soporte':
                    header("Location: /porteria/usuarios/soporte/soporte.php");
                    break;
                case 'administracion':
                    header("Location: /porteria/usuarios/Administracion/admi.php");
                    break;
                case 'porteria':
                    header("Location: /porteria/usuarios/Porteria/index.php");
                    break;
                default:
                    header("Location: login.php");
            }
            exit();
        } else {
            echo "<script>alert('Contraseña incorrecta'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Acceso</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary-color: #4A90E2;
            --secondary-color: #2C3E50;
            --accent-color: #2ECC71;
            --text-color: #2C3E50;
            --text-light: #7F8C8D;
            --white: #fff;
            --error-color: #E74C3C;
            --shadow-color: rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
            --card-bg: rgba(255, 255, 255, 0.95);
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(255,255,255,0.1) 0%, transparent 10%),
                radial-gradient(circle at 80% 70%, rgba(255,255,255,0.1) 0%, transparent 10%);
            animation: backgroundFloat 20s linear infinite;
        }

        @keyframes backgroundFloat {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .container {
            width: 100%;
            max-width: 1400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4rem;
            position: relative;
            z-index: 1;
            padding: 2rem;
        }

        .title {
            text-align: center;
            color: var(--white);
            animation: fadeInDown 0.8s ease;
            margin-bottom: 2rem;
        }

        .title h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            background: linear-gradient(45deg, #fff, #e6e6e6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
        }

        .title p {
            font-size: 1.4rem;
            opacity: 0.9;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            font-weight: 300;
        }

        .role-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 3rem;
            width: 100%;
            max-width: 1200px;
            opacity: 1;
            visibility: visible;
            transition: var(--transition);
            animation: fadeInUp 0.8s ease;
        }

        .avatar-container {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 10px 30px var(--shadow-color);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            transform: translateY(0);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .avatar-container:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            background: var(--white);
        }

        .avatar-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--accent-color), var(--primary-color));
            opacity: 0;
            transition: var(--transition);
        }

        .avatar-container:hover::before {
            opacity: 1;
        }

        .avatar-image {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            margin: 0 auto 2rem;
            background: var(--white);
            box-shadow: 0 10px 20px var(--shadow-color);
            transition: var(--transition);
            position: relative;
            border: 2px solid rgba(74, 144, 226, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-image i {
            font-size: 4rem;
            color: var(--primary-color);
            transition: var(--transition);
        }

        .avatar-container:hover .avatar-image i {
            transform: scale(1.1);
            color: var(--accent-color);
        }

        .avatar-title {
            font-size: 2rem;
            color: var(--text-color);
            margin-bottom: 1rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .avatar-container:hover .avatar-title {
            color: var(--primary-color);
            transform: translateY(-3px);
        }

        .avatar-description {
            color: var(--text-light);
            font-size: 1.1rem;
            line-height: 1.6;
            transition: var(--transition);
            padding: 0 1rem;
        }

        .avatar-container:hover .avatar-description {
            color: var(--text-color);
        }

        .login-form {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            max-width: 400px;
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .login-form.active {
            display: block;
        }

        .login-form h2 {
            color: var(--text-color);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 1rem;
            transition: var(--transition);
            background: rgba(255,255,255,0.9);
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(74,144,226,0.1);
        }

        .form-group label {
            position: absolute;
            left: 1rem;
            top: 1rem;
            color: var(--text-light);
            transition: var(--transition);
            pointer-events: none;
            font-size: 1rem;
        }

        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label {
            transform: translateY(-2.5rem);
            font-size: 0.9rem;
            color: var(--primary-color);
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1rem;
        }

        .submit-btn:hover {
            background: #357abd;
            transform: translateY(-2px);
        }

        .back-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            font-size: 1.5rem;
            transition: var(--transition);
        }

        .back-btn:hover {
            color: var(--error-color);
            transform: scale(1.1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, -48%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            z-index: 999;
        }

        .overlay.active {
            display: block;
        }

        @media (max-width: 1200px) {
            .role-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
                gap: 2rem;
            }

            .role-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .title h1 {
                font-size: 2.5rem;
            }

            .title p {
                font-size: 1.1rem;
            }

            .login-form {
                padding: 2.5rem;
                margin: 1rem;
            }

            .selected-role {
                padding: 1.2rem;
            }

            .avatar-container {
                padding: 2rem;
            }

            .avatar-image {
                width: 120px;
                height: 120px;
            }

            .avatar-title {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 480px) {
            .title h1 {
                font-size: 2rem;
            }

            .login-form {
                padding: 2rem;
            }

            .selected-role img {
                width: 40px;
                height: 40px;
            }

            .selected-role-info h3 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">
            <h1>Portal de Acceso</h1>
            <p>Seleccione su rol para iniciar sesión</p>
        </div>
        
        <div class="role-grid">
            <div class="avatar-container" onclick="showLoginForm('soporte')">
                <div class="avatar-image">
                    <i class="fas fa-headset"></i>
                </div>
                <h2 class="avatar-title">Soporte</h2>
                <p class="avatar-description">Acceso al panel de soporte técnico</p>
            </div>
            
            <div class="avatar-container" onclick="showLoginForm('administracion')">
                <div class="avatar-image">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h2 class="avatar-title">Administración</h2>
                <p class="avatar-description">Acceso al panel administrativo</p>
            </div>
            
            <div class="avatar-container" onclick="showLoginForm('porteria')">
                <div class="avatar-image">
                    <i class="fas fa-door-open"></i>
                </div>
                <h2 class="avatar-title">Portería</h2>
                <p class="avatar-description">Acceso al control de portería</p>
            </div>
        </div>
        
        <div class="overlay" id="overlay"></div>
        
        <div class="login-form" id="loginForm">
            <button class="back-btn" onclick="hideLoginForm()">×</button>
            <h2>Iniciar Sesión</h2>
            <form method="POST" action="">
                <input type="hidden" name="role" id="roleInput">
                <div class="form-group">
                    <input type="text" id="username" name="username" placeholder=" " required>
                    <label for="username">Usuario</label>
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder=" " required>
                    <label for="password">Contraseña</label>
                </div>
                <button type="submit" class="submit-btn">Ingresar</button>
            </form>
        </div>
    </div>

    <script>
    function showLoginForm(role) {
        document.getElementById('overlay').classList.add('active');
        document.getElementById('loginForm').classList.add('active');
        document.getElementById('roleInput').value = role;
        
        // Actualizar el título del formulario según el rol
        const titles = {
            'soporte': 'Soporte Técnico',
            'administracion': 'Administración',
            'porteria': 'Portería'
        };
        document.querySelector('#loginForm h2').textContent = `Iniciar Sesión - ${titles[role]}`;
    }

    function hideLoginForm() {
        document.getElementById('overlay').classList.remove('active');
        document.getElementById('loginForm').classList.remove('active');
    }

    // Cerrar el formulario al hacer clic en el overlay
    document.getElementById('overlay').addEventListener('click', hideLoginForm);

    // Prevenir que el clic en el formulario cierre el modal
    document.getElementById('loginForm').addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Validación del formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        
        if (!username || !password) {
            e.preventDefault();
            alert('Por favor complete todos los campos');
        }
    });
    </script>
</body>
</html>

