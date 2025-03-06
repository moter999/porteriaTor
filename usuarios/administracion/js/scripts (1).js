document.addEventListener('DOMContentLoaded', () => {
    showSection('viewTodayRecordsSection');
    loadTodayRecords();

    // Event listener para búsqueda por nombre con Enter
    document.getElementById('searchName').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchByName();
        }
    });

    // Event listener para cambio en el select de mes
    document.getElementById('searchMonth')?.addEventListener('change', searchByMonth);
});

let currentRecordId = null; // Store the ID of the record being edited

function showLoading() {
    const overlay = document.getElementById('loading-overlay');
    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    overlay.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function loadTodayRecords() {
    const todayRecordsTable = document.getElementById('todayRecordsTable');
    const today = new Date().toISOString().slice(0, 10);
    fetch('get_today_records.php?date=' + encodeURIComponent(today))
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                todayRecordsTable.innerHTML = `<p class="alert alert-danger">${data.error}</p>`;
                return;
            }

            let tableHTML = '<table class="table table-striped table-bordered table-hover">';
            tableHTML += '<thead><tr><th>#</th><th>Nombre</th><th>Fecha</th><th>Entrada 1</th><th>Salida 1</th><th>Entrada 2</th><th>Salida 2</th><th>Entrada 3</th><th>Salida 3</th><th>Observaciones</th><th>Mes</th><th>Acciones</th></tr></thead>';
            tableHTML += '<tbody>';

            if (data.records && data.records.length > 0) {
                data.records.forEach(record => {
                    tableHTML += `<tr>
                        <td>${escapeHtml(record.id)}</td>
                        <td>${escapeHtml(record.nombre)}</td>
                        <td>${escapeHtml(record.fecha)}</td>
                        <td>${escapeHtml(record.entrada)}</td>
                        <td>${escapeHtml(record.salida)}</td>
                        <td>${escapeHtml(record.entrada2)}</td>
                        <td>${escapeHtml(record.salida2)}</td>
                        <td>${escapeHtml(record.entrada3)}</td>
                        <td>${escapeHtml(record.salida3)}</td>
                        <td>${escapeHtml(record.obs)}</td>
                        <td>${escapeHtml(record.mes)}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="openEditModal(${record.id})"><i class="fas fa-edit"></i> Editar</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteRecord(${record.id})"><i class="fas fa-trash"></i> Eliminar</button>
                        </td>
                    </tr>`;
                });
            } else {
                tableHTML += '<tr><td colspan="12" class="text-center">No se encontraron registros para hoy.</td></tr>';
            }

            tableHTML += '</tbody></table>';
            todayRecordsTable.innerHTML = tableHTML;
        })
        .catch(error => {
            console.error('Error:', error);
            todayRecordsTable.innerHTML = '<p class="alert alert-danger">Error al cargar los registros: ' + error.message + '</p>';
        });
}

function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(section => section.style.display = 'none');
    document.getElementById(sectionId).style.display = 'block';
    if (sectionId !== 'specificSearchSection') {
        document.getElementById('searchResults').innerHTML = '';
    }
}

function logout() {
    window.location.href = "/porteria/usuarios/administracion/logout.php";
}


function openEditModal(id) {
    currentRecordId = id;
    const editModalBody = document.getElementById('editModalBody');
    editModalBody.innerHTML = '<p>Cargando formulario de edición...</p>';

    fetch(`edit_records.php?id=${encodeURIComponent(id)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.text();
        })
        .then(html => {
            console.log('Respuesta del servidor:', html);
            editModalBody.innerHTML = html;
            
            const entradaField = document.getElementById('edit-entrada');
            console.log('Campo entrada:', entradaField?.value);
            
            $('#editModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            editModalBody.innerHTML = '<p class="alert alert-danger">Error al cargar el formulario de edición.</p>';
        });
}

function saveRecord() {
    if (!currentRecordId) {
        showMessage('Error', 'No se ha seleccionado ningún registro para editar.', 'danger');
        return;
    }

    const entrada = document.getElementById('edit-entrada')?.value;
    console.log('Valor de entrada antes de enviar:', entrada);

    const formData = new FormData();
    formData.append('id', currentRecordId);
    formData.append('nombre', document.getElementById('edit-nombre').value);
    formData.append('fecha', document.getElementById('edit-fecha').value);
    formData.append('entrada', entrada);
    formData.append('salida', document.getElementById('edit-salida').value);
    formData.append('entrada2', document.getElementById('edit-entrada2').value);
    formData.append('salida2', document.getElementById('edit-salida2').value);
    formData.append('entrada3', document.getElementById('edit-entrada3').value);
    formData.append('salida3', document.getElementById('edit-salida3').value);
    formData.append('obs', document.getElementById('edit-obs').value);
    formData.append('mes', document.getElementById('edit-mes').value);
    formData.append('action', 'update');

    console.log('Datos a enviar:', Object.fromEntries(formData));

    fetch('edit_records.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            const text = await response.text();
            console.error('Respuesta no JSON:', text);
            throw new Error('La respuesta del servidor no es JSON válido');
        }
    })
    .then(data => {
        if (data.success) {
            showMessage('Éxito', 'Registro actualizado correctamente', 'success');
            $('#editModal').modal('hide');
            // Recargar la vista actual
            const currentView = document.getElementById('currentView')?.textContent;
            if (currentView === 'Registros de Hoy') {
                loadTodayRecords();
            } else {
                location.reload();
            }
        } else {
            throw new Error(data.message || 'Error al actualizar el registro');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', error.message, 'danger');
    });
}

function deleteRecord(id) {
    if (!id) {
        showMessage('Error', 'ID de registro no válido', 'danger');
        return;
    }

    if (confirm(`¿Estás seguro de que deseas eliminar el registro #${id}?`)) {
        showMessage('Procesando', `Eliminando registro #${id}...`, 'info');

        fetch('delete_records.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('La respuesta no es JSON válido');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMessage('Éxito', data.message, 'success');
                // Actualizar la vista actual
                const currentSection = document.querySelector('.section[style*="block"]');
                if (currentSection) {
                    switch(currentSection.id) {
                        case 'viewTodayRecordsSection':
                            loadTodayRecords();
                            break;
                        case 'viewAllRecordsSection':
                            location.reload();
                            break;
                        case 'searchSection':
                            // Si estamos en la sección de búsqueda, repetir la última búsqueda
                            if (document.getElementById('startDate').value && 
                                document.getElementById('endDate').value) {
                                searchByDateRange();
                            } else if (document.getElementById('searchName').value.trim()) {
                                searchByName();
                            }
                            break;
                    }
                }
            } else {
                throw new Error(data.error || 'Error desconocido al eliminar el registro');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error', `Error al eliminar el registro #${id}: ${error.message}`, 'danger');
        });
    }
}

async function updateViews() {
    try {
        // Limpiar mensajes anteriores
        clearMessages();

        // Actualizar registros de hoy
        await loadTodayRecordsAsync();

        // Actualizar la vista de todos los registros si está visible
        const allRecordsSection = document.getElementById('viewAllRecordsSection');
        if (allRecordsSection && allRecordsSection.style.display === 'block') {
            await loadAllRecordsAsync();
        }

        // Actualizar resultados de búsqueda si están visibles
        const searchSection = document.getElementById('specificSearchSection');
        if (searchSection && searchSection.style.display === 'block') {
            const lastSearchType = document.querySelector('#searchModal input:not([style*="none"])')?.name;
            if (lastSearchType) {
                await new Promise(resolve => setTimeout(resolve, 500));
                await searchRecordsAsync(lastSearchType);
            }
        }
    } catch (error) {
        console.error('Error en updateViews:', error);
        showMessage('Error', 'Error al actualizar las vistas: ' + error.message, 'danger');
    }
}

// Versión asíncrona de loadTodayRecords
async function loadTodayRecordsAsync() {
    const todayRecordsTable = document.getElementById('todayRecordsTable');
    const today = new Date().toISOString().slice(0, 10);
    
    try {
        const response = await fetch('get_today_records.php?date=' + encodeURIComponent(today));
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        
        const data = await response.json();
        if (data.error) {
            todayRecordsTable.innerHTML = `<p class="alert alert-danger">${data.error}</p>`;
            return;
    }

        let tableHTML = '<table class="table table-striped table-bordered table-hover">';
        tableHTML += '<thead><tr><th>#</th><th>Nombre</th><th>Fecha</th><th>Entrada 1</th><th>Salida 1</th><th>Entrada 2</th><th>Salida 2</th><th>Entrada 3</th><th>Salida 3</th><th>Observaciones</th><th>Mes</th><th>Acciones</th></tr></thead>';
        tableHTML += '<tbody>';

        if (data.records && data.records.length > 0) {
            data.records.forEach(record => {
                tableHTML += `<tr>
                    <td>${escapeHtml(record.id)}</td>
                    <td>${escapeHtml(record.nombre)}</td>
                    <td>${escapeHtml(record.fecha)}</td>
                    <td>${escapeHtml(record.entrada)}</td>
                    <td>${escapeHtml(record.salida)}</td>
                    <td>${escapeHtml(record.entrada2)}</td>
                    <td>${escapeHtml(record.salida2)}</td>
                    <td>${escapeHtml(record.entrada3)}</td>
                    <td>${escapeHtml(record.salida3)}</td>
                    <td>${escapeHtml(record.obs)}</td>
                    <td>${escapeHtml(record.mes)}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="openEditModal(${record.id})"><i class="fas fa-edit"></i> Editar</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteRecord(${record.id})"><i class="fas fa-trash"></i> Eliminar</button>
                    </td>
                </tr>`;
            });
        } else {
            tableHTML += '<tr><td colspan="12" class="text-center">No se encontraron registros para hoy.</td></tr>';
        }

        tableHTML += '</tbody></table>';
        todayRecordsTable.innerHTML = tableHTML;
    } catch (error) {
        console.error('Error:', error);
        todayRecordsTable.innerHTML = '<p class="alert alert-danger">Error al cargar los registros: ' + error.message + '</p>';
        throw error;
    }
}

// Versión asíncrona de loadAllRecords
async function loadAllRecordsAsync() {
    try {
        const response = await fetch('get_all_records.php');
        if (!response.ok) {
            throw new Error('Error al cargar todos los registros');
        }
        const html = await response.text();
        const allRecordsSection = document.getElementById('viewAllRecordsSection');
        if (allRecordsSection) {
            allRecordsSection.innerHTML = html;
        }
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

function clearMessages() {
    const alertPlaceholder = document.getElementById('liveAlertPlaceholder');
    if (alertPlaceholder) {
        alertPlaceholder.innerHTML = '';
    }
}

function showMessage(title, message, type) {
    const alertPlaceholder = document.getElementById('liveAlertPlaceholder');
    if (!alertPlaceholder) {
        console.error('Error: No se encontró el contenedor de alertas (liveAlertPlaceholder)');
        alert(`${title}: ${message}`);
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'alert-wrapper mb-3';
    
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <div class="alert-icon me-2">
                    ${getAlertIcon(type)}
                </div>
                <div>
                    <strong>${title}</strong>
                    <div class="alert-message">${message}</div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    wrapper.innerHTML = alertHTML;
    alertPlaceholder.insertBefore(wrapper, alertPlaceholder.firstChild);

    const timeouts = {
        'success': 5000,  // 5 segundos
        'info': 4000,     // 4 segundos
        'warning': 6000,  // 6 segundos
        'danger': 8000    // 8 segundos
    };

    setTimeout(() => {
        const alert = wrapper.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show');
            setTimeout(() => wrapper.remove(), 300);
        }
    }, timeouts[type] || 5000);
}

function getAlertIcon(type) {
    const icons = {
        'success': '<i class="fas fa-check-circle"></i>',
        'info': '<i class="fas fa-info-circle"></i>',
        'warning': '<i class="fas fa-exclamation-triangle"></i>',
        'danger': '<i class="fas fa-times-circle"></i>'
    };
    return icons[type] || icons.info;
}

// Función segura para escapar HTML
function escapeHtml(text) {
    if (text === null || text === undefined) {
        return '';
    }
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function initializeSearchName() {
    const searchInput = document.getElementById('searchName');
    const suggestionsContainer = document.createElement('div');
    suggestionsContainer.className = 'suggestions-container';
    suggestionsContainer.style.display = 'none';
    searchInput.parentNode.appendChild(suggestionsContainer);

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const term = this.value.trim();
        
        if (term.length < 1) {
            suggestionsContainer.style.display = 'none';
            return;
    }

        debounceTimer = setTimeout(() => {
            fetch(`get_name_suggestions.php?term=${encodeURIComponent(term)}`)
                .then(response => response.json())
                .then(suggestions => {
                    if (suggestions.length > 0) {
                        suggestionsContainer.innerHTML = suggestions
                            .map(name => `<div class="suggestion-item">${name}</div>`)
                            .join('');
                        suggestionsContainer.style.display = 'block';
                    } else {
                        suggestionsContainer.style.display = 'none';
                    }
        })
        .catch(error => {
            console.error('Error:', error);
                    suggestionsContainer.style.display = 'none';
                });
        }, 300); // Esperar 300ms después de que el usuario deje de escribir
    });

    // Manejar clic en sugerencia
    suggestionsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('suggestion-item')) {
            searchInput.value = e.target.textContent;
            suggestionsContainer.style.display = 'none';
            searchByName(); // Realizar la búsqueda automáticamente
        }
    });

    // Cerrar sugerencias cuando se hace clic fuera
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });

    // Navegación con teclado
    searchInput.addEventListener('keydown', function(e) {
        const items = suggestionsContainer.getElementsByClassName('suggestion-item');
        const activeItem = suggestionsContainer.querySelector('.suggestion-item.active');
        
        if (items.length === 0) return;

        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (!activeItem) {
                    items[0].classList.add('active');
                } else {
                    const nextItem = activeItem.nextElementSibling;
                    if (nextItem) {
                        activeItem.classList.remove('active');
                        nextItem.classList.add('active');
                    }
                }
                break;
            case 'ArrowUp':
                e.preventDefault();
                if (activeItem) {
                    const prevItem = activeItem.previousElementSibling;
                    if (prevItem) {
                        activeItem.classList.remove('active');
                        prevItem.classList.add('active');
                    }
                }
                break;
            case 'Enter':
                if (activeItem) {
                    e.preventDefault();
                    searchInput.value = activeItem.textContent;
                    suggestionsContainer.style.display = 'none';
                    searchByName();
                }
                break;
            case 'Escape':
                suggestionsContainer.style.display = 'none';
                break;
        }
    });
}

// Modificar la función showSearchSection para inicializar la búsqueda
function showSearchSection() {
    document.querySelectorAll('.section').forEach(section => section.style.display = 'none');
    document.getElementById('searchSection').style.display = 'block';
    initializeSearchName(); // Inicializar la búsqueda con sugerencias
}

function loadNameSuggestions() {
    fetch('get_names.php')
        .then(response => response.json())
        .then(data => {
            const datalist = document.getElementById('namesList');
            datalist.innerHTML = '';
            data.forEach(name => {
                const option = document.createElement('option');
                option.value = name;
                datalist.appendChild(option);
            });
        })
        .catch(error => console.error('Error cargando sugerencias:', error));
}

function searchByName() {
    const searchInput = document.getElementById('searchName');
    const searchTerm = searchInput.value.trim();
    
    if (!searchTerm) {
        showMessage('Advertencia', 'Por favor ingrese un nombre para buscar', 'warning');
                return;
            }

    showMessage('Procesando', 'Buscando registros...', 'info');
    
    fetch('search_records.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'type=name&term=' + encodeURIComponent(searchTerm)
    })
    .then(response => response.text())
    .then(html => {
        const searchResults = document.getElementById('searchResults');
        searchResults.innerHTML = html;
        highlightSearchTerm(searchTerm);
        showMessage('Éxito', 'Búsqueda completada', 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'Error al realizar la búsqueda', 'danger');
    });
}

function searchByDate() {
    const dateInput = document.getElementById('searchDate');
    const searchDate = dateInput.value;
    
    if (!searchDate) {
        showMessage('Advertencia', 'Por favor seleccione una fecha para buscar', 'warning');
        return;
    }

    showMessage('Procesando', 'Buscando registros...', 'info');
    
    fetch('search_records.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'type=date&term=' + encodeURIComponent(searchDate)
    })
    .then(response => response.text())
    .then(html => {
        const searchResults = document.getElementById('searchResults');
        searchResults.innerHTML = html;
        showMessage('Éxito', 'Búsqueda completada', 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'Error al realizar la búsqueda', 'danger');
    });
}

function searchByMonth() {
    const monthSelect = document.getElementById('searchMonth');
    const selectedMonth = monthSelect.value;
    
    if (!selectedMonth) {
        showMessage('Advertencia', 'Por favor seleccione un mes para buscar', 'warning');
        return;
    }

    showMessage('Procesando', 'Buscando registros...', 'info');
    
    fetch('search_records.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'type=month&term=' + encodeURIComponent(selectedMonth)
    })
    .then(response => response.text())
    .then(html => {
        const searchResults = document.getElementById('searchResults');
        searchResults.innerHTML = html;
        showMessage('Éxito', 'Búsqueda completada', 'success');
        })
        .catch(error => {
            console.error('Error:', error);
        showMessage('Error', 'Error al realizar la búsqueda', 'danger');
    });
}

function searchByDateRange() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const searchName = document.getElementById('searchName').value.trim();
    
    if (!startDate || !endDate) {
        showMessage('Advertencia', 'Por favor seleccione ambas fechas', 'warning');
        return;
    }

    if (startDate > endDate) {
        showMessage('Advertencia', 'La fecha inicial no puede ser posterior a la fecha final', 'warning');
        return;
    }

    showLoading();
    showMessage('Procesando', 'Buscando registros...', 'info');
    
    let searchParams = new URLSearchParams();
    searchParams.append('type', 'date_range');
    searchParams.append('start_date', startDate);
    searchParams.append('end_date', endDate);
    if (searchName) {
        searchParams.append('name', searchName);
    }
    
    fetch('search_records.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: searchParams.toString()
    })
    .then(response => response.text())
    .then(html => {
        const searchResults = document.getElementById('searchResults');
        searchResults.innerHTML = html;
        if (searchName) {
            highlightSearchTerm(searchName);
        }
        showMessage('Éxito', 'Búsqueda completada', 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'Error al realizar la búsqueda', 'danger');
    })
    .finally(() => {
        hideLoading();
    });
}

function highlightSearchTerm(term) {
    if (!term) return;
    
    const searchResults = document.getElementById('searchResults');
    const regex = new RegExp(term, 'gi');
    
    const walker = document.createTreeWalker(
        searchResults,
        NodeFilter.SHOW_TEXT,
        null,
        false
    );

    const nodes = [];
    let node;
    while (node = walker.nextNode()) {
        nodes.push(node);
    }

    nodes.forEach(node => {
        const parent = node.parentNode;
        if (parent.tagName !== 'SCRIPT' && parent.tagName !== 'STYLE') {
            const content = node.textContent;
            const replacedContent = content.replace(regex, match => 
                `<span class="search-highlight">${match}</span>`
            );
            if (content !== replacedContent) {
                const span = document.createElement('span');
                span.innerHTML = replacedContent;
                parent.replaceChild(span, node);
            }
        }
    });
}
