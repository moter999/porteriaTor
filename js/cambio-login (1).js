// js/cambio-login.js

// Función para cambiar el texto del botón, mostrar el aviso y cambiar el color de fondo
function cambiarTextoYMostrarAviso(logoType) {
  const boton = document.getElementById("login-button");
  const aviso = document.getElementById("aviso");
  const labelUsuario = document.getElementById("label-usuario"); // Label para el tipo de usuario
  const emailLabel = document.getElementById("email-label"); // Label para el email
  const passwordLabel = document.getElementById("password-label"); // Label para la contraseña
  let nuevoTexto;
  let mensajeAviso;
  let colorFondo;

  // Definir texto y color según el tipo de logo seleccionado
  if (logoType === 'porteria') {
    nuevoTexto = "PORTERIA";
    mensajeAviso = "Ingresa credenciales de portería.";
    labelUsuario.innerText = "Usuario de Portería"; // Cambiar el texto del label de usuario
    emailLabel.innerText = "Email de Portero"; // Cambiar texto para email
    passwordLabel.innerText = "Contraseña de Portero"; // Cambiar texto para contraseña
    colorFondo = "#FFCCCB"; // Color de fondo para portería (rojo claro)
  } else if (logoType === 'admin') {
    nuevoTexto = "ADMINISTRACION";
    mensajeAviso = "Ingresa credenciales de administración";
    labelUsuario.innerText = "Usuario de Administración"; // Cambiar el texto del label de usuario
    emailLabel.innerText = "Email de Administración"; // Cambiar texto para email
    passwordLabel.innerText = "Contraseña de Administración"; // Cambiar texto para contraseña
    colorFondo = "#ADD8E6"; // Color de fondo para administración (azul claro)
  } else if (logoType === 'soporte') {
    nuevoTexto = "SOPORTE";
    mensajeAviso = "Ingresa credenciales de soporte.";
    labelUsuario.innerText = "Usuario de Soporte"; // Cambiar el texto del label de usuario
    emailLabel.innerText = "Email de Soporte"; // Cambiar texto para email
    passwordLabel.innerText = "Contraseña de Soporte"; // Cambiar texto para contraseña
    colorFondo = "#D3FFD3"; // Color de fondo para soporte (verde claro)
  } else if (logoType === 'administracion') {
    nuevoTexto = "ADMINISTRACION";
    mensajeAviso = "Ingresa credenciales de administración.";
    labelUsuario.innerText = "Usuario de Administración"; // Cambiar el texto del label de usuario
    emailLabel.innerText = "Email de Administrador"; // Cambiar texto para email
    passwordLabel.innerText = "Contraseña de Administración"; // Cambiar texto para contraseña
    colorFondo = "#ADD8E6"; // Color de fondo para administración (azul claro)
  }

  boton.innerText = nuevoTexto;
  aviso.innerText = mensajeAviso;

  // Cambiar el color de fondo del body
  document.body.style.backgroundColor = colorFondo;
}

// Función para cambiar el logo y el texto del botón
function cambiarLogo(logoType) {
  const logoSrcMap = {
    porteria: 'https://cdn-icons-png.flaticon.com/512/1801/1801293.png',
    admin: 'https://www.pngitem.com/pimgs/m/69-691927_information-security-analyst-vector-hd-png-download.png',
    soporte: 'https://cdn-icons-png.flaticon.com/512/2344/2344684.png',
    administracion: 'https://icon-library.com/images/admin-icon/admin-icon-10.jpg'
  };

  const mainLogoImg = document.getElementById("main-logo");

  if (logoSrcMap[logoType]) {
      mainLogoImg.src = logoSrcMap[logoType]; // Cambia la imagen del logo principal según la selección.
      cambiarTextoYMostrarAviso(logoType); // Actualiza el texto y muestra aviso.
   }
}
