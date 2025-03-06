// Gestor de notificaciones
const ToastManager = {
    show(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="fas ${this.getIcon(type)}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },
    
    success(message) {
        this.show(message, 'success');
    },
    
    error(message) {
        this.show(message, 'error');
    },
    
    warning(message) {
        this.show(message, 'warning');
    },
    
    info(message) {
        this.show(message, 'info');
    },
    
    getIcon(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        return icons[type] || icons.info;
    }
};

// Gestor de carga
const LoadingManager = {
    show(message = 'Cargando...') {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-message">${message}</div>
            </div>
        `;
        
        document.body.appendChild(overlay);
    },
    
    hide() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }
};

// Gestor de modales
const ModalManager = {
    open(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            ToastManager.error('Modal no encontrado');
            return;
        }
        
        // Cerrar todos los modales antes de abrir uno nuevo
        this.closeAll();
        
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    },
    
    close(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        modal.classList.remove('show');
        document.body.style.overflow = '';
    },
    
    closeAll() {
        document.querySelectorAll('.modal.show').forEach(modal => {
            modal.classList.remove('show');
        });
        document.body.style.overflow = '';
    },
    
    init() {
        // Cerrar modales al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeAll();
            }
        });
        
        // Cerrar modales con la tecla Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAll();
            }
        });
        
        // Inicializar botones de cierre
        document.querySelectorAll('.modal-close').forEach(button => {
            button.addEventListener('click', () => {
                const modal = button.closest('.modal');
                if (modal) {
                    this.close(modal.id);
                }
            });
        });
    }
};

// Gestor de formularios
const FormManager = {
    validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        
        if (!value) {
            this.showError(field, 'Este campo es requerido');
            isValid = false;
        } else {
            this.clearError(field);
            
            // Validaciones específicas según el tipo de campo
            switch (field.type) {
                case 'email':
                    if (!this.isValidEmail(value)) {
                        this.showError(field, 'Ingrese un email válido');
                        isValid = false;
                    }
                    break;
                    
                case 'number':
                    if (field.min && value < field.min) {
                        this.showError(field, `El valor mínimo es ${field.min}`);
                        isValid = false;
                    }
                    if (field.max && value > field.max) {
                        this.showError(field, `El valor máximo es ${field.max}`);
                        isValid = false;
                    }
                    break;
                    
                case 'date':
                    if (!this.isValidDate(value)) {
                        this.showError(field, 'Ingrese una fecha válida');
                        isValid = false;
                    }
                    break;
            }
        }
        
        return isValid;
    },
    
    showError(field, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        
        field.classList.add('error');
        field.parentNode.appendChild(errorDiv);
    },
    
    clearError(field) {
        field.classList.remove('error');
        const errorDiv = field.parentNode.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
    },
    
    clearForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return;
        
        form.reset();
        form.querySelectorAll('.error').forEach(field => {
            this.clearError(field);
        });
    },
    
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },
    
    isValidDate(dateString) {
        const date = new Date(dateString);
        return date instanceof Date && !isNaN(date);
    }
};

// Gestor de tablas
const TableManager = {
    sort(table, column, type = 'text') {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const direction = table.dataset.sortDirection === 'asc' ? -1 : 1;
        
        rows.sort((a, b) => {
            const aValue = a.cells[column].textContent.trim();
            const bValue = b.cells[column].textContent.trim();
            
            switch (type) {
                case 'number':
                    return direction * (parseFloat(aValue) - parseFloat(bValue));
                case 'date':
                    return direction * (new Date(aValue) - new Date(bValue));
                default:
                    return direction * aValue.localeCompare(bValue);
            }
        });
        
        // Actualizar dirección de ordenamiento
        table.dataset.sortDirection = direction === 1 ? 'asc' : 'desc';
        
        // Limpiar y reinsertar filas ordenadas
        rows.forEach(row => tbody.appendChild(row));
        
        // Actualizar indicadores de ordenamiento
        this.updateSortIndicators(table, column);
    },
    
    updateSortIndicators(table, activeColumn) {
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            header.classList.remove('sort-asc', 'sort-desc');
            if (index === activeColumn) {
                header.classList.add(table.dataset.sortDirection === 'asc' ? 'sort-asc' : 'sort-desc');
            }
        });
    },
    
    search(table, query) {
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query.toLowerCase()) ? '' : 'none';
        });
    },
    
    init(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        // Inicializar ordenamiento
        const headers = table.querySelectorAll('th[data-sortable]');
        headers.forEach((header, index) => {
            header.addEventListener('click', () => {
                const type = header.dataset.sortType || 'text';
                this.sort(table, index, type);
            });
        });
        
        // Inicializar búsqueda
        const searchInput = document.querySelector(`input[data-search="${tableId}"]`);
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.search(table, e.target.value);
            });
        }
    }
};

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar gestores
    ModalManager.init();
    TableManager.init('choferTable');
    
    // Inicializar tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = e.target.dataset.tooltip;
            document.body.appendChild(tooltip);
            
            const rect = e.target.getBoundingClientRect();
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
            tooltip.style.left = rect.left + (rect.width - tooltip.offsetWidth) / 2 + 'px';
        });
        
        element.addEventListener('mouseleave', () => {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
}); 