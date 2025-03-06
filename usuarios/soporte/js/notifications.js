// Función para cargar las notificaciones
async function loadNotifications(type = null) {
    try {
        const response = await fetch(`notification_handler.php?action=list${type ? `&type=${type}` : ''}`);
        const data = await response.json();
        
        if (data.success) {
            displayNotifications(data.data);
            updateCounts();
        } else {
            showToast('error', 'Error al cargar las notificaciones');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error al conectar con el servidor');
    }
}

// Función para mostrar las notificaciones en la interfaz
function displayNotifications(notifications) {
    const container = document.querySelector('.notification-list');
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <h3>No hay notificaciones</h3>
                <p>Cuando haya nuevas notificaciones, aparecerán aquí.</p>
            </div>
        `;
        return;
    }
    
    const notificationsHTML = notifications.map(notification => `
        <div class="notification-item ${notification.read_at ? 'read' : ''}" onclick="showNotificationDetails(${notification.id})">
            <div class="notification-icon ${notification.type}">
                <i class="fas fa-${getIconByType(notification.type)}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">${formatDate(notification.created_at)}</div>
            </div>
            <div class="notification-actions" onclick="event.stopPropagation()">
                ${!notification.read_at ? `
                    <button class="action-button" title="Marcar como leído" onclick="markAsRead(${notification.id})">
                        <i class="fas fa-check"></i>
                    </button>
                ` : ''}
                <button class="action-button" title="Eliminar" onclick="deleteNotification(${notification.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = `<h1>Centro de Notificaciones</h1>${notificationsHTML}`;
}

// Función para actualizar los contadores
async function updateCounts() {
    try {
        const response = await fetch('notification_handler.php?action=counts');
        const data = await response.json();
        
        if (data.success) {
            const counts = data.data;
            document.querySelector('[data-type="all"] .badge').textContent = 
                Object.values(counts).reduce((a, b) => a + b, 0);
            document.querySelector('[data-type="error"] .badge').textContent = counts.error;
            document.querySelector('[data-type="warning"] .badge').textContent = counts.warning;
            document.querySelector('[data-type="info"] .badge').textContent = counts.info;
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Función para marcar una notificación como leída
async function markAsRead(id) {
    try {
        const response = await fetch('notification_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=mark_read&id=${id}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Notificación marcada como leída');
            loadNotifications();
        } else {
            showToast('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error al conectar con el servidor');
    }
}

// Función para eliminar una notificación
async function deleteNotification(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta notificación?')) {
        return;
    }
    
    try {
        const response = await fetch('notification_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&id=${id}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Notificación eliminada');
            loadNotifications();
        } else {
            showToast('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error al conectar con el servidor');
    }
}

// Función para mostrar los detalles de una notificación
async function showNotificationDetails(id) {
    try {
        const response = await fetch(`notification_handler.php?action=details&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const notification = data.data;
            const details = JSON.parse(notification.details || '{}');
            
            document.getElementById('notificationDetails').innerHTML = `
                <div class="notification-detail">
                    <h3>${notification.title}</h3>
                    <p class="timestamp">${formatDate(notification.created_at)}</p>
                    <div class="detail-content">
                        <p>${notification.message}</p>
                        ${details.additionalInfo ? `
                            <h4>Detalles adicionales:</h4>
                            <ul>
                                ${Object.entries(details.additionalInfo).map(([key, value]) => `
                                    <li><strong>${key}:</strong> ${value}</li>
                                `).join('')}
                            </ul>
                        ` : ''}
                        ${details.recommendations ? `
                            <h4>Acciones recomendadas:</h4>
                            <ul>
                                ${details.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                            </ul>
                        ` : ''}
                    </div>
                </div>
            `;
            
            document.getElementById('notificationModal').style.display = 'block';
            
            // Marcar como leída si no lo está
            if (!notification.read_at) {
                markAsRead(id);
            }
        } else {
            showToast('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error al cargar los detalles');
    }
}

// Función para obtener el icono según el tipo de notificación
function getIconByType(type) {
    switch (type) {
        case 'error':
            return 'exclamation-circle';
        case 'warning':
            return 'exclamation-triangle';
        case 'info':
            return 'info-circle';
        default:
            return 'bell';
    }
}

// Función para formatear fechas
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) { // menos de 1 minuto
        return 'Hace un momento';
    } else if (diff < 3600000) { // menos de 1 hora
        const minutes = Math.floor(diff / 60000);
        return `Hace ${minutes} minuto${minutes > 1 ? 's' : ''}`;
    } else if (diff < 86400000) { // menos de 1 día
        const hours = Math.floor(diff / 3600000);
        return `Hace ${hours} hora${hours > 1 ? 's' : ''}`;
    } else if (diff < 604800000) { // menos de 1 semana
        const days = Math.floor(diff / 86400000);
        return `Hace ${days} día${days > 1 ? 's' : ''}`;
    } else {
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

// Función para mostrar mensajes toast
function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Inicializar la página
document.addEventListener('DOMContentLoaded', () => {
    loadNotifications();
    
    // Manejar filtros
    document.querySelectorAll('.filter-button').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.filter-button').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            const type = this.dataset.type;
            loadNotifications(type === 'all' ? null : type);
        });
    });
});

// Actualizar notificaciones cada 5 minutos
setInterval(() => {
    const activeFilter = document.querySelector('.filter-button.active');
    const type = activeFilter ? activeFilter.dataset.type : null;
    loadNotifications(type === 'all' ? null : type);
}, 300000); 