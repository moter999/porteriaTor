// Animaciones y efectos interactivos
document.addEventListener('DOMContentLoaded', () => {
    initializeAnimations();
    initializeSidebarEffects();
    initializeCardEffects();
    initializeButtonEffects();
});

function initializeAnimations() {
    // Animación de entrada para el contenido principal
    const content = document.querySelector('.content');
    content.style.opacity = '0';
    setTimeout(() => {
        content.style.opacity = '1';
        content.style.transform = 'translateY(0)';
    }, 100);

    // Animación para las tarjetas
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + (index * 100));
    });
}

function initializeSidebarEffects() {
    const navItems = document.querySelectorAll('.nav-item');
    
    // Efecto hover mejorado para elementos del menú
    navItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            item.style.transform = 'translateX(10px)';
        });
        
        item.addEventListener('mouseleave', () => {
            item.style.transform = 'translateX(0)';
        });
    });

    // Efecto de click
    navItems.forEach(item => {
        item.addEventListener('click', () => {
            // Remover clase activa de todos los items
            navItems.forEach(i => i.classList.remove('active'));
            // Agregar clase activa al item clickeado
            item.classList.add('active');
            
            // Efecto de onda al hacer click
            const ripple = document.createElement('div');
            ripple.classList.add('ripple');
            item.appendChild(ripple);
            setTimeout(() => ripple.remove(), 1000);
        });
    });
}

function initializeCardEffects() {
    const cards = document.querySelectorAll('.card');
    
    cards.forEach(card => {
        // Efecto de elevación al hover
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
            card.style.boxShadow = '0 15px 30px rgba(0,0,0,0.1)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
        });
    });
}

function initializeButtonEffects() {
    const buttons = document.querySelectorAll('.btn');
    
    buttons.forEach(button => {
        // Efecto de pulsación al hacer click
        button.addEventListener('click', function(e) {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const ripple = document.createElement('div');
            ripple.classList.add('btn-ripple');
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            button.appendChild(ripple);
            setTimeout(() => ripple.remove(), 1000);
        });
    });
}

// Función para mostrar el loader
function showLoader() {
    const loader = document.createElement('div');
    loader.className = 'loader-container';
    loader.innerHTML = '<div class="loader"></div>';
    document.body.appendChild(loader);
}

// Función para ocultar el loader
function hideLoader() {
    const loader = document.querySelector('.loader-container');
    if (loader) {
        loader.remove();
    }
}

// Función para mostrar alertas con animación
function showAnimatedAlert(message, type = 'info') {
    const alertPlaceholder = document.getElementById('liveAlertPlaceholder');
    const wrapper = document.createElement('div');
    wrapper.className = 'alert-wrapper';
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="alert-icon me-2">
                ${getAlertIcon(type)}
            </div>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    wrapper.appendChild(alert);
    alertPlaceholder.insertBefore(wrapper, alertPlaceholder.firstChild);
    
    setTimeout(() => {
        wrapper.style.opacity = '0';
        setTimeout(() => wrapper.remove(), 300);
    }, 5000);
}

// Función para animar las transiciones entre secciones
function animatePageTransition(callback) {
    const content = document.querySelector('.content');
    content.style.opacity = '0';
    content.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        callback();
        content.style.opacity = '1';
        content.style.transform = 'translateY(0)';
    }, 300);
}

// Función para animar la carga de tablas
function animateTableRows() {
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        setTimeout(() => {
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, 50 * index);
    });
}

// Función para animar el scroll suave
function smoothScroll(target) {
    const element = document.querySelector(target);
    if (element) {
        window.scrollTo({
            top: element.offsetTop,
            behavior: 'smooth'
        });
    }
} 