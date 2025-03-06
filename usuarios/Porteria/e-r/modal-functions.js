// Funciones para manejar modales
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Cerrar modal al hacer clic fuera de él
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Funciones para formularios
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('error');
        } else {
            field.classList.remove('error');
        }
    });

    return isValid;
}

// Función para mostrar mensajes
function showMessage(message, type = 'info') {
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert alert-${type}`;
    messageDiv.textContent = message;
    
    const container = document.querySelector('.container');
    container.insertBefore(messageDiv, container.firstChild);
    
    setTimeout(() => {
        messageDiv.remove();
    }, 3000);
}

// Función para cargar datos en un modal
function loadDataIntoModal(modalId, data) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    Object.keys(data).forEach(key => {
        const input = modal.querySelector(`[name="${key}"]`);
        if (input) {
            input.value = data[key];
        }
    });
}

// Función para limpiar formulario
function clearForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.reset();
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.classList.remove('error');
    });
}

// Función para confirmar acción
function confirmAction(message = '¿Está seguro de realizar esta acción?') {
    return confirm(message);
}

// Función para manejar errores de AJAX
function handleAjaxError(error) {
    console.error('Error:', error);
    showMessage('Ha ocurrido un error. Por favor, intente nuevamente.', 'danger');
}

// Función para actualizar tabla
function updateTable(tableId, data) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    data.forEach(item => {
        const row = document.createElement('tr');
        Object.values(item).forEach(value => {
            const cell = document.createElement('td');
            cell.textContent = value;
            row.appendChild(cell);
        });
        tbody.appendChild(row);
    });
}

// Función para formatear fecha
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Función para validar campos específicos
function validateField(field, pattern) {
    if (!field.value.match(pattern)) {
        field.classList.add('error');
        return false;
    }
    field.classList.remove('error');
    return true;
}

// Función para manejar carga de archivos
function handleFileUpload(input, previewId) {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById(previewId);
        if (preview) {
            preview.src = e.target.result;
        }
    };
    reader.readAsDataURL(file);
}

// Función para manejar búsqueda
function handleSearch(input, tableId) {
    const searchTerm = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

// Función para manejar ordenamiento de tabla
function sortTable(tableId, column) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll('tr'));
    const direction = table.dataset.sortDirection === 'asc' ? -1 : 1;

    rows.sort((a, b) => {
        const aValue = a.cells[column].textContent;
        const bValue = b.cells[column].textContent;
        return direction * aValue.localeCompare(bValue);
    });

    table.dataset.sortDirection = direction === 1 ? 'asc' : 'desc';
    
    rows.forEach(row => tbody.appendChild(row));
}

// Gestor de modales
const ModalManager = {
    open(modalId) {
        // Cerrar todos los modales antes de abrir uno nuevo
        this.closeAll();
        
        const modal = document.getElementById(modalId);
        if (!modal) {
            ToastManager.error('Modal no encontrado');
            return;
        }
        
        // Asegurarse de que el modal esté oculto antes de mostrarlo
        modal.style.display = 'none';
        modal.classList.remove('show');
        
        // Forzar un reflow
        modal.offsetHeight;
        
        // Mostrar el modal con animación
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
        
        // Bloquear el scroll del body
        document.body.style.overflow = 'hidden';
        
        // Agregar evento para cerrar al hacer clic fuera
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.close(modalId);
            }
        });
        
        // Agregar evento para cerrar con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.close(modalId);
            }
        });
    },
    
    close(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        // Remover la clase show primero
        modal.classList.remove('show');
        
        // Esperar a que termine la animación antes de ocultar
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    },
    
    closeAll() {
        document.querySelectorAll('.modal, .details-modal').forEach(modal => {
            this.close(modal.id);
        });
    },
    
    init() {
        // Inicializar botones de cierre
        document.querySelectorAll('.close, .details-close').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const modal = button.closest('.modal, .details-modal');
                if (modal) {
                    this.close(modal.id);
                }
            });
        });
    }
};

// Función para abrir el modal de agregar chofer
function openAddModal() {
    // Limpiar el formulario antes de abrirlo
    const form = document.getElementById('choferForm');
    if (form) {
        form.reset();
        
        // Actualizar título del modal
        const modalTitle = document.querySelector('#addModal h2');
        if (modalTitle) {
            modalTitle.textContent = 'Nuevo Chofer';
        }
        
        // Eliminar el input oculto de ID si existe
        const idInput = form.querySelector('input[name="id"]');
        if (idInput) {
            idInput.remove();
        }
    }
    
    ModalManager.open('addModal');
}

// Función para abrir el modal de detalles
function openDetailsModal(data) {
    if (!data) {
        ToastManager.error('No hay datos para mostrar');
        return;
    }
    
    const content = document.getElementById('detailsContent');
    if (!content) {
        ToastManager.error('Contenedor de detalles no encontrado');
        return;
    }
    
    const fields = {
        'ID': data.id,
        'Chofer': data.Chofer,
        'Patente': data.Patente,
        'Código 1': data.cod1,
        'Código 2': data.Cod2,
        'Fecha Ingreso': data.F_Ingreso,
        'Hora Ingreso': data.H_ing,
        'K Ingreso': data.K_Ing,
        'Fecha Salida': data.F_Salida,
        'Hora Salida': data.H_Sal,
        'K Salida': data.K_Sal,
        'Tiempo Ocupado': data.T_Ocupado,
        'K Ocupado': data.K_Ocup,
        'Lugar': data.Lugar,
        'Detalle': data.Detalle
    };
    
    content.innerHTML = '';
    
    Object.entries(fields).forEach(([label, value]) => {
        const item = document.createElement('div');
        item.className = 'detail-item';
        item.innerHTML = `
            <div class="detail-label">${label}</div>
            <div class="detail-value">${value || '-'}</div>
        `;
        content.appendChild(item);
    });
    
    ModalManager.open('detailsModal');
}

// Función para procesar el formulario de chofer
function processChoferForm(event) {
    event.preventDefault();
    
    if (!FormManager.validateForm(event.target)) {
        ToastManager.error('Por favor, complete todos los campos requeridos');
        return false;
    }
    
    LoadingManager.show('Guardando datos...');
    
    const formData = new FormData(event.target);
    const id = formData.get('id');
    const url = id ? 'update_chofer.php' : 'add_chofer.php';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ToastManager.success('Datos guardados correctamente');
            ModalManager.close('addModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            ToastManager.error(data.message || 'Error al guardar los datos');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        ToastManager.error('Error al procesar la solicitud');
    })
    .finally(() => {
        LoadingManager.hide();
    });
    
    return false;
}

// Función para editar chofer
function editChofer(id) {
    if (!id) {
        ToastManager.error('ID no válido');
        return;
    }
    
    LoadingManager.show('Cargando datos del chofer...');
    
    fetch(`get_chofer.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const form = document.getElementById('choferForm');
                if (!form) {
                    ToastManager.error('Formulario no encontrado');
                    return;
                }
                
                FormManager.clearForm('choferForm');
                
                // Llenar el formulario con los datos
                Object.entries(data.data).forEach(([key, value]) => {
                    const input = form.querySelector(`[name="${key.toLowerCase()}"]`);
                    if (input) {
                        input.value = value || '';
                    }
                });
                
                // Agregar el ID como campo oculto
                let idInput = form.querySelector('input[name="id"]');
                if (!idInput) {
                    idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id';
                    form.appendChild(idInput);
                }
                idInput.value = id;
                
                // Actualizar título del modal
                const modalTitle = document.querySelector('#addModal h2');
                if (modalTitle) {
                    modalTitle.textContent = 'Editar Chofer';
                }
                
                ModalManager.open('addModal');
            } else {
                ToastManager.error(data.message || 'Error al cargar los datos');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ToastManager.error('Error al cargar los datos');
        })
        .finally(() => {
            LoadingManager.hide();
        });
}

// Función para eliminar chofer
function deleteChofer(id) {
    if (!id) {
        ToastManager.error('ID no válido');
        return;
    }
    
    if (confirm('¿Está seguro de que desea eliminar este registro?')) {
        LoadingManager.show('Eliminando registro...');
        
        fetch('delete_chofer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ToastManager.success('Registro eliminado correctamente');
                setTimeout(() => location.reload(), 1500);
            } else {
                ToastManager.error(data.message || 'Error al eliminar el registro');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ToastManager.error('Error al eliminar el registro');
        })
        .finally(() => {
            LoadingManager.hide();
        });
    }
}

// Función para manejar la carga de archivos
function handleFileUpload(input) {
    if (!input.files || !input.files[0]) {
        ToastManager.error('No se ha seleccionado ningún archivo');
        return;
    }
    
    const file = input.files[0];
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (file.size > maxSize) {
        ToastManager.error('El archivo es demasiado grande. El tamaño máximo es 5MB');
        input.value = '';
        return;
    }
    
    if (!allowedTypes.includes(file.type)) {
        ToastManager.error('Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF');
        input.value = '';
        return;
    }
    
    // Mostrar vista previa de la imagen
    const preview = document.getElementById('imagePreview');
    if (preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

// Función para validar campos específicos
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    
    if (!value) {
        FormManager.showError(field, 'Este campo es requerido');
        isValid = false;
    } else {
        FormManager.clearError(field);
        
        switch (field.type) {
            case 'email':
                if (!FormManager.isValidEmail(value)) {
                    FormManager.showError(field, 'Ingrese un email válido');
                    isValid = false;
                }
                break;
                
            case 'number':
                if (field.min && value < field.min) {
                    FormManager.showError(field, `El valor mínimo es ${field.min}`);
                    isValid = false;
                }
                if (field.max && value > field.max) {
                    FormManager.showError(field, `El valor máximo es ${field.max}`);
                    isValid = false;
                }
                break;
                
            case 'date':
                if (!FormManager.isValidDate(value)) {
                    FormManager.showError(field, 'Ingrese una fecha válida');
                    isValid = false;
                }
                break;
        }
    }
    
    return isValid;
}

// Función para formatear fechas
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

// Función para formatear horas
function formatTime(timeString) {
    if (!timeString) return '-';
    return timeString.substring(0, 5); // Formato HH:mm
}

// Función para calcular tiempo ocupado
function calculateTimeOccupied(startDate, startTime, endDate, endTime) {
    if (!startDate || !startTime || !endDate || !endTime) return '-';
    
    const start = new Date(`${startDate}T${startTime}`);
    const end = new Date(`${endDate}T${endTime}`);
    
    const diff = end - start;
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    
    return `${hours}h ${minutes}m`;
}

// Función para calcular kilómetros ocupados
function calculateKilometersOccupied(startKm, endKm) {
    if (!startKm || !endKm) return '-';
    return (endKm - startKm).toFixed(2);
}

// Función para manejar la búsqueda en tiempo real
function handleSearch(input) {
    const searchText = input.value.toLowerCase();
    const table = document.getElementById('choferTable');
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
    });
}

// Función para manejar el ordenamiento de la tabla
function handleSort(table, column, type = 'text') {
    TableManager.sort(table, column, type);
}

// Función para exportar a CSV
function exportToCSV() {
    const table = document.getElementById('choferTable');
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr:not([style*="display: none"])');
    
    rows.forEach(row => {
        const rowData = [];
        const cells = row.querySelectorAll('th, td:not(:last-child)');
        
        cells.forEach(cell => {
            rowData.push('"' + cell.textContent.replace(/"/g, '""') + '"');
        });
        
        csv.push(rowData.join(','));
    });
    
    const csvString = csv.join('\n');
    const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
    
    const link = document.createElement('a');
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'choferes.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        ToastManager.success('Archivo CSV generado correctamente');
    } else {
        ToastManager.error('Tu navegador no soporta la descarga de archivos');
    }
}

// Función para imprimir tabla
function printTable() {
    const table = document.getElementById('choferTable');
    if (!table) return;
    
    const printWindow = window.open('', '_blank', 'height=600,width=800');
    
    printWindow.document.write(`
        <html>
        <head>
            <title>Gestión de Choferes - Impresión</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { padding: 8px; text-align: center; border: 1px solid #ddd; }
                th { background-color: #f2f2f2; }
                h1 { text-align: center; }
                .header { margin-bottom: 20px; }
                .footer { margin-top: 30px; font-size: 12px; text-align: center; color: #666; }
                @media print {
                    button { display: none !important; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Reporte de Choferes</h1>
                <p>Fecha de impresión: ${new Date().toLocaleString()}</p>
                <button onclick="window.print();window.close();" style="padding: 10px; background: #4f46e5; color: white; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 20px;">Imprimir</button>
            </div>
            <table>
                ${table.querySelector('thead').outerHTML}
                <tbody>
    `);
    
    const rows = table.querySelectorAll('tbody tr:not([style*="display: none"])');
    rows.forEach(row => {
        const rowHTML = document.createElement('tr');
        row.querySelectorAll('td:not(:last-child)').forEach(cell => {
            const cellClone = cell.cloneNode(true);
            rowHTML.appendChild(cellClone);
        });
        printWindow.document.write(rowHTML.outerHTML);
    });
    
    printWindow.document.write(`
                </tbody>
            </table>
            <div class="footer">
                <p>Este reporte fue generado desde el sistema de Gestión de Choferes.</p>
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    
    ToastManager.success('Vista de impresión preparada');
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    ModalManager.init();
}); 