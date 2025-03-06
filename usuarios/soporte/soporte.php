<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Añadimos un CDN de iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <button onclick="logout()" class="logout-button">
        <i class="fas fa-sign-out-alt"></i>
        Cerrar Sesión
    </button>
    
    <header>
        <h1>Gestión de Usuarios</h1>
    </header>
    <main>
        <section id="user-list">
            <h2>Lista de Usuarios</h2>
            <div id="message" class="message"></div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tabla</th>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include '../../conexion/conectado.php';

                        $tables = ['user_soporte', 'user_administracion', 'user_porteria'];
                        foreach ($tables as $table) {
                            $sql = "SELECT id, usuario FROM $table";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>$table</td>";
                                    echo "<td>" . $row["id"]. "</td>";
                                    echo "<td>" . $row["usuario"]. "</td>";
                                    echo "<td class='actions'>";
                                    echo "<button class='view' onclick='viewMovements(" . $row["id"] . ", \"$table\")'>";
                                    echo "<i class='fas fa-eye'></i> Ver";
                                    echo "</button>";
                                    echo "<button class='edit' onclick='openEditModal(" . $row["id"] . ", \"$table\", \"" . $row["usuario"] . "\")'>";
                                    echo "<i class='fas fa-edit'></i> Editar";
                                    echo "</button>";
                                    echo "<button class='delete' onclick='confirmDelete(" . $row["id"] . ", \"$table\")'>";
                                    echo "<i class='fas fa-trash'></i> Eliminar";
                                    echo "</button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No hay usuarios registrados en la tabla $table.</td></tr>";
                            }
                        }

                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
        <section id="user-form">
            <h2>Agregar Usuario</h2>
            <form id="add-user-form">
                <div class="form-group">
                    <label for="usuario">
                        <i class="fas fa-user"></i> Nombre de Usuario:
                    </label>
                    <input type="text" id="usuario" name="usuario" required>
                </div>
                <div class="form-group">
                    <label for="contraseña">
                        <i class="fas fa-lock"></i> Contraseña:
                    </label>
                    <div class="password-input">
                        <input type="password" id="contraseña" name="contraseña" required>
                        <button type="button" class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="rol">
                        <i class="fas fa-users"></i> Rol:
                    </label>
                    <select id="rol" name="rol" required>
                        <option value="user_soporte">Soporte</option>
                        <option value="user_administracion">Administración</option>
                        <option value="user_porteria">Portería</option>
                    </select>
                </div>
                <button type="button" onclick="addUser()" class="submit-button">
                    <i class="fas fa-plus-circle"></i> Agregar Usuario
                </button>
            </form>
        </section>
    </main>

    <!-- Modal Mejorado -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-edit"></i> Editar Usuario</h2>
                <button class="close" aria-label="Cerrar modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-user-form">
                    <input type="hidden" id="editUserId" name="id">
                    <input type="hidden" id="editUserTable" name="table">
                    
                    <div class="form-group">
                        <label for="editUsuario">
                            <i class="fas fa-user"></i> Nombre de Usuario:
                        </label>
                        <input type="text" id="editUsuario" name="usuario" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editContraseña">
                            <i class="fas fa-lock"></i> Nueva Contraseña:
                        </label>
                        <div class="password-input">
                            <input type="password" id="editContraseña" name="contraseña" required>
                            <button type="button" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="cancel-button" onclick="closeModal()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="save-button" onclick="editUser()">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Función para mostrar/ocultar contraseña
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Modal mejorado
        var modal = document.getElementById("editModal");

        function openEditModal(userId, table, currentUsername) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUserTable').value = table;
            document.getElementById('editUsuario').value = currentUsername;
            modal.classList.add('show');
            modal.style.display = "block";
        }

        function closeModal() {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = "none";
            }, 300);
        }

        document.querySelector('.close').onclick = closeModal;

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }

        // Resto de funciones existentes...
        function confirmDelete(userId, table) {
            if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "delete_user.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            showMessage('success', xhr.responseText);
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            showMessage('error', 'Error al eliminar el usuario.');
                        }
                    }
                };
                xhr.send("id=" + userId + "&table=" + table);
            }
        }

        function showMessage(type, text) {
            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = `<div class="${type}"><i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${text}</div>`;
        }

        function addUser() {
            var usuario = document.getElementById('usuario').value;
            var contraseña = document.getElementById('contraseña').value;
            var rol = document.getElementById('rol').value;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "add_users.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        showMessage('success', xhr.responseText);
                        document.getElementById('add-user-form').reset();
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showMessage('error', 'Error al agregar el usuario.');
                    }
                }
            };
            xhr.send("usuario=" + usuario + "&contraseña=" + contraseña + "&rol=" + rol);
        }

        function editUser() {
            var userId = document.getElementById('editUserId').value;
            var table = document.getElementById('editUserTable').value;
            var usuario = document.getElementById('editUsuario').value;
            var contraseña = document.getElementById('editContraseña').value;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "edit_user.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        showMessage('success', xhr.responseText);
                        closeModal();
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showMessage('error', 'Error al editar el usuario.');
                    }
                }
            };
            xhr.send("id=" + userId + "&table=" + table + "&usuario=" + usuario + "&contraseña=" + contraseña);
        }

        function viewMovements(userId, table) {
            // Implementar la lógica para ver movimientos
            showMessage('info', 'Próximamente: Ver movimientos del usuario ID: ' + userId + ' en la tabla ' + table);
        }

        function logout() {
            window.location.href = 'logout.php';
        }
    </script>
</body>
</html>