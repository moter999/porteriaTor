document.getElementById('add-user-form').addEventListener('submit', function(event) {
    event.preventDefault();

    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;

    if (username && email) {
        const userList = document.getElementById('users');
        const listItem = document.createElement('li');
        listItem.textContent = `Usuario: ${username}, Correo: ${email}`;
        userList.appendChild(listItem);

        // Limpiar el formulario
        document.getElementById('username').value = '';
        document.getElementById('email').value = '';
    }
});

function logout() {
    window.location.href = "/porteria/usuarios/logout.php";
}
