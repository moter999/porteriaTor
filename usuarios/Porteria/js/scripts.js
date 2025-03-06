// Función para mostrar mensajes al usuario
function showMessage(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed top-4 right-4 p-4 rounded shadow-lg ${type === 'error' ? 'bg-red-500' : 'bg-green-500'} text-white fade-in slide-in`;
    alertDiv.textContent = message;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.classList.remove('fade-in', 'slide-in');
        alertDiv.classList.add('fade-out', 'slide-out');
        setTimeout(() => alertDiv.remove(), 500);
    }, 3000);
}

// Función para validar el formulario
function validateForm(formData) {
    const requiredFields = ['nombre', 'fecha', 'entrada', 'salida', 'mes'];
    for (let field of requiredFields) {
        if (!formData.get(field)) {
            throw new Error(`El campo ${field} es obligatorio`);
        }
    }
}

// Función para enviar el formulario de agregar registro
document.getElementById('addForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    try {
        validateForm(formData);
        
        const response = await fetch('process.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }

        const data = await response.json();
        
        if (data.success) {
            const tableBody = document.getElementById('tableBody');
            const newRow = document.createElement('tr');
            newRow.className = 'hover:bg-gray-100';
            newRow.setAttribute('data-id', data.id);
            
            // Crear el contenido de la fila con los datos del nuevo registro
            newRow.innerHTML = `
                <td class="py-2 px-4 border-b">${data.id}</td>
                <td class="py-2 px-4 border-b">${escapeHtml(formData.get('nombre'))}</td>
                <td class="py-2 px-4 border-b">${formData.get('fecha')}</td>
                <td class="py-2 px-4 border-b">${formData.get('entrada')}</td>
                <td class="py-2 px-4 border-b">${formData.get('salida')}</td>
                <td class="py-2 px-4 border-b">${formData.get('entrada2') || ''}</td>
                <td class="py-2 px-4 border-b">${formData.get('salida2') || ''}</td>
                <td class="py-2 px-4 border-b">${formData.get('entrada3') || ''}</td>
                <td class="py-2 px-4 border-b">${formData.get('salida3') || ''}</td>
                <td class="py-2 px-4 border-b">${escapeHtml(formData.get('obs') || '')}</td>
                <td class="py-2 px-4 border-b">${escapeHtml(formData.get('mes'))}</td>
                <td class="py-2 px-4 border-b flex space-x-2">
                    <button class="text-blue-500 hover:text-blue-700" onclick="viewRecord(${data.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="text-yellow-500 hover:text-yellow-700" onclick="editRecord(${data.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="text-red-500 hover:text-red-700" onclick="deleteRecord(${data.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

            // Aplicar estilos iniciales para la animación
            newRow.style.opacity = '0';
            newRow.style.transform = 'translateY(-20px)';
            newRow.style.transition = 'all 0.5s ease';

            // Insertar la fila al principio de la tabla
            if (tableBody.firstChild) {
                tableBody.insertBefore(newRow, tableBody.firstChild);
            } else {
                tableBody.appendChild(newRow);
            }

            // Activar la animación
            setTimeout(() => {
                newRow.style.opacity = '1';
                newRow.style.transform = 'translateY(0)';
                newRow.style.backgroundColor = '#f0fdf4'; // Color verde suave
                setTimeout(() => {
                    newRow.style.backgroundColor = '';
                }, 1000);
            }, 10);

            showMessage('Registro agregado exitosamente', 'success');
            closeModal('addModal');
            this.reset();
        } else {
            throw new Error(data.message || 'Error al agregar el registro');
        }
    } catch (error) {
        showMessage(error.message, 'error');
        console.error('Error:', error);
    }
});

// Función para cargar los registros de hoy
async function loadTodayRecords() {
    try {
        const tableBody = document.getElementById('tableBody');
        if (!tableBody) {
            console.error('Error: No se encontró el elemento tableBody');
            showMessage('Error en la estructura de la página. Por favor, recargue la página.', 'error');
            return;
        }

        showMessage('Cargando registros...', 'info');
        
        const response = await fetch('get_today_records.php', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`Error del servidor: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Error al cargar los registros');
        }

        const records = data.data || [];
        
        if (records.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center py-8 text-gray-500">
                        <i class="fas fa-info-circle text-2xl mb-2"></i>
                        <p>No se encontraron registros para hoy</p>
                    </td>
                </tr>`;
            return;
        }

        // Actualizar la tabla con los nuevos registros
        tableBody.innerHTML = records.map(record => `
            <tr class="hover:bg-gray-50 transition-colors">
                <td>${record.id || ''}</td>
                <td>${escapeHtml(record.nombre || '')}</td>
                <td>${record.fecha || ''}</td>
                <td>${record.entrada || ''}</td>
                <td>${record.salida || ''}</td>
                <td>${record.entrada2 || ''}</td>
                <td>${record.salida2 || ''}</td>
                <td>${record.entrada3 || ''}</td>
                <td>${record.salida3 || ''}</td>
                <td class="max-w-xs truncate">${escapeHtml(record.obs || '')}</td>
                <td>${escapeHtml(record.mes || '')}</td>
                <td>
                    <div class="action-buttons">
                        <button class="action-button text-blue-500 hover:text-blue-700" onclick="viewRecord(${record.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-button text-yellow-500 hover:text-yellow-700" onclick="editRecord(${record.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-button text-red-500 hover:text-red-700" onclick="deleteRecord(${record.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        showMessage('Registros cargados exitosamente', 'success');
    } catch (error) {
        console.error('Error al cargar los registros:', error);
        showMessage(`Error: ${error.message}`, 'error');
        
        const tableBody = document.getElementById('tableBody');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-circle text-2xl mb-2"></i>
                        <p>Error al cargar los registros</p>
                        <p class="text-sm mt-2">${error.message}</p>
                    </td>
                </tr>`;
        }
    }
}

// Función para ver un registro
async function viewRecord(id) {
    try {
        showMessage('Cargando detalles...', 'info');
        
        const response = await fetch(`get_record_details.php?id=${encodeURIComponent(id)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error en la respuesta:', errorText);
            throw new Error(`Error al obtener los detalles: ${response.status} ${response.statusText}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('La respuesta del servidor no es JSON válido');
        }
        
        const data = await response.json();
        console.log('Datos recibidos:', data);

        if (!data.success) {
            throw new Error(data.message || 'Error al obtener los detalles del registro');
        }

        const recordDetails = document.getElementById('recordDetails');
        if (!recordDetails) {
            throw new Error('No se encontró el elemento para mostrar los detalles');
        }

        // Aplicar animación de desvanecimiento
        recordDetails.style.opacity = '0';
        recordDetails.style.transform = 'translateY(-20px)';
        recordDetails.style.transition = 'all 0.3s ease';

        // Asegurarse de que todos los campos existan
        const record = {
            nombre: data.nombre || '',
            fecha: data.fecha || '',
            entrada: data.entrada || '',
            salida: data.salida || '',
            entrada2: data.entrada2 || '',
            salida2: data.salida2 || '',
            entrada3: data.entrada3 || '',
            salida3: data.salida3 || '',
            obs: data.obs || ''
        };

        // Crear el contenido con un diseño mejorado
        recordDetails.innerHTML = `
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border-b pb-2">
                        <p class="text-gray-600 text-sm">Nombre</p>
                        <p class="font-semibold">${escapeHtml(record.nombre)}</p>
                    </div>
                    <div class="border-b pb-2">
                        <p class="text-gray-600 text-sm">Fecha</p>
                        <p class="font-semibold">${escapeHtml(record.fecha)}</p>
                    </div>
                    <div class="border-b pb-2">
                        <p class="text-gray-600 text-sm">Entrada</p>
                        <p class="font-semibold">${escapeHtml(record.entrada)}</p>
                    </div>
                    <div class="border-b pb-2">
                        <p class="text-gray-600 text-sm">Salida</p>
                        <p class="font-semibold">${escapeHtml(record.salida)}</p>
                    </div>
                    <div class="border-b pb-2">
                        <p class="text-gray-600 text-sm">Entrada 2</p>
                        <p class="font-semibold">${escapeHtml(record.entrada2)}</p>
                    </div>
                    <div class="border-b pb-2">
                        <p class="text-gray-600 text-sm">Salida 2</p>
                        <p class="font-semibold">${escapeHtml(record.salida2)}</p>
                    </div>
                    <div class="border-b pb-2">
                        <p class="text-gray-600 text-sm">Entrada 3</p>
                        <p class="font-semibold">${escapeHtml(record.entrada3)}</p>
                    </div>
                    <div class="border-b pb-2">
                        <p class="text-gray-600 text-sm">Salida 3</p>
                        <p class="font-semibold">${escapeHtml(record.salida3)}</p>
                    </div>
                    <div class="col-span-2 border-b pb-2">
                        <p class="text-gray-600 text-sm">Observaciones</p>
                        <p class="font-semibold">${escapeHtml(record.obs)}</p>
                    </div>
                </div>
            </div>
        `;

        // Mostrar el modal
        openModal('viewModal');

        // Activar la animación después de un breve retraso
        setTimeout(() => {
            recordDetails.style.opacity = '1';
            recordDetails.style.transform = 'translateY(0)';
        }, 50);

        showMessage('Detalles cargados exitosamente', 'success');
    } catch (error) {
        console.error('Error al cargar los detalles:', error);
        showMessage(error.message, 'error');
    }
}

// Función para editar un registro
async function editRecord(id) {
    try {
        const response = await fetch(`get_record_details.php?id=${encodeURIComponent(id)}`);
        if (!response.ok) throw new Error('Error al obtener los detalles del registro');
        
        const data = await response.json();
        if (data.error) throw new Error(data.error);

        // Llenar el formulario de edición
        document.getElementById('editId').value = data.id;
        document.getElementById('editNombre').value = data.nombre;
        document.getElementById('editFecha').value = data.fecha;
        document.getElementById('editEntrada').value = data.entrada || '';
        document.getElementById('editSalida').value = data.salida || '';
        document.getElementById('editEntrada2').value = data.entrada2 || '';
        document.getElementById('editSalida2').value = data.salida2 || '';
        document.getElementById('editEntrada3').value = data.entrada3 || '';
        document.getElementById('editSalida3').value = data.salida3 || '';
        document.getElementById('editObs').value = data.obs || '';
        document.getElementById('editMes').value = data.mes;

        openModal('editModal');
    } catch (error) {
        console.error('Error:', error);
        showMessage(error.message, 'error');
    }
}

// Función para eliminar un registro
async function deleteRecord(id) {
    try {
        if (!confirm('¿Está seguro de que desea eliminar este registro?')) {
            return;
        }

        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (row) {
            row.style.transition = 'all 0.5s ease';
            row.style.backgroundColor = '#fee2e2'; // Color rojo suave
            row.style.opacity = '0';
            row.style.transform = 'translateX(100%)';
        }

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        const response = await fetch('process.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error('Error al eliminar el registro');
        
        const data = await response.json();
        
        if (data.success) {
            setTimeout(() => {
                if (row) row.remove();
                showMessage('Registro eliminado exitosamente', 'success');
            }, 500);
        } else {
            throw new Error(data.message || 'Error al eliminar el registro');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage(error.message, 'error');
    }
}

// Función para escapar HTML y prevenir XSS
function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) return '';
    return String(unsafe)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Función para normalizar texto (remover acentos y convertir a minúsculas)
function normalizeText(text) {
    return text.toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

// Función para resaltar el texto encontrado
function highlightText(text, searchTerm) {
    if (!searchTerm) return text;
    const normalizedText = text.toString();
    const normalizedSearchTerm = searchTerm.toString();
    const regex = new RegExp(normalizedSearchTerm, 'gi');
    return normalizedText.replace(regex, match => `<span class="bg-yellow-200">${match}</span>`);
}

// Función para filtrar la tabla con características mejoradas
function filterTable() {
    const input = document.getElementById('search');
    const searchTerm = normalizeText(input.value);
    const table = document.getElementById('tableBody');
    const tr = table.getElementsByTagName('tr');
    let matchCount = 0;
    const searchResults = document.getElementById('searchResults');
    
    // Si el término de búsqueda está vacío, mostrar todos los registros
    if (!searchTerm) {
        Array.from(tr).forEach(row => {
            row.style.display = '';
            // Restaurar el contenido original sin resaltado
            Array.from(row.getElementsByTagName('td')).forEach(cell => {
                cell.innerHTML = cell.innerHTML.replace(/<span class="bg-yellow-200">(.*?)<\/span>/g, '$1');
            });
        });
        if (searchResults) {
            searchResults.innerHTML = '';
        }
        return;
    }

    // Filtrar y resaltar resultados
    Array.from(tr).forEach(row => {
        const td = row.getElementsByTagName('td');
        let found = false;
        let matchedFields = [];
        
        Array.from(td).forEach((cell, index) => {
            if (!cell.classList.contains('actions')) { // Ignorar columna de acciones
                const originalText = cell.textContent || cell.innerText;
                const normalizedCellText = normalizeText(originalText);
                
                if (normalizedCellText.includes(searchTerm)) {
                    found = true;
                    // Identificar qué campo coincidió
                    const fieldName = getFieldName(index);
                    if (fieldName) {
                        matchedFields.push(fieldName);
                    }
                    // Resaltar el texto encontrado
                    cell.innerHTML = highlightText(originalText, input.value);
                }
            }
        });
        
        if (found) {
            matchCount++;
            row.style.display = '';
            // Agregar un tooltip con los campos coincidentes
            row.title = `Coincidencias encontradas en: ${matchedFields.join(', ')}`;
        } else {
            row.style.display = 'none';
        }
    });

    // Actualizar el contador de resultados
    if (searchResults) {
        searchResults.innerHTML = `
            <div class="text-sm text-gray-600 mt-2">
                ${matchCount} resultado${matchCount !== 1 ? 's' : ''} encontrado${matchCount !== 1 ? 's' : ''}
                ${matchCount > 0 ? `para "${input.value}"` : ''}
            </div>
        `;
    }

    // Mostrar sugerencias si no hay resultados
    if (matchCount === 0) {
        showSearchSuggestions(searchTerm);
    }
}

// Función para obtener el nombre del campo según el índice
function getFieldName(index) {
    const fieldNames = [
        'ID', 'Nombre', 'Fecha', 'Entrada', 'Salida',
        'Entrada 2', 'Salida 2', 'Entrada 3', 'Salida 3',
        'Observaciones', 'Mes'
    ];
    return fieldNames[index] || null;
}

// Función para mostrar sugerencias de búsqueda
function showSearchSuggestions(searchTerm) {
    const searchResults = document.getElementById('searchResults');
    if (!searchResults) return;

    // Obtener todas las celdas visibles de la tabla
    const table = document.getElementById('tableBody');
    const allCells = Array.from(table.getElementsByTagName('td'));
    const allTexts = allCells.map(cell => cell.textContent || cell.innerText);
    
    // Encontrar términos similares
    const suggestions = allTexts
        .filter(text => normalizeText(text).length > 0)
        .filter((text, index, self) => self.indexOf(text) === index) // Eliminar duplicados
        .filter(text => {
            const similarity = calculateSimilarity(searchTerm, normalizeText(text));
            return similarity > 0.3; // Umbral de similitud
        })
        .slice(0, 3); // Limitar a 3 sugerencias

    if (suggestions.length > 0) {
        searchResults.innerHTML = `
            <div class="text-sm text-gray-600 mt-2">
                No se encontraron resultados para "${searchTerm}"<br>
                Sugerencias:
                <ul class="mt-1">
                    ${suggestions.map(sugg => `
                        <li class="cursor-pointer text-blue-500 hover:text-blue-700"
                            onclick="document.getElementById('search').value='${sugg}'; filterTable();">
                            ¿Quisiste buscar "${sugg}"?
                        </li>
                    `).join('')}
                </ul>
            </div>
        `;
    }
}

// Función para calcular la similitud entre dos textos
function calculateSimilarity(text1, text2) {
    text1 = normalizeText(text1);
    text2 = normalizeText(text2);
    
    if (text1.length === 0 || text2.length === 0) return 0;
    if (text1 === text2) return 1;
    
    const length = Math.max(text1.length, text2.length);
    const editDistance = levenshteinDistance(text1, text2);
    
    return 1 - (editDistance / length);
}

// Función para calcular la distancia de Levenshtein
function levenshteinDistance(text1, text2) {
    const matrix = Array(text2.length + 1).fill(null)
        .map(() => Array(text1.length + 1).fill(null));

    for (let i = 0; i <= text1.length; i++) matrix[0][i] = i;
    for (let j = 0; j <= text2.length; j++) matrix[j][0] = j;

    for (let j = 1; j <= text2.length; j++) {
        for (let i = 1; i <= text1.length; i++) {
            const indicator = text1[i - 1] === text2[j - 1] ? 0 : 1;
            matrix[j][i] = Math.min(
                matrix[j][i - 1] + 1,
                matrix[j - 1][i] + 1,
                matrix[j - 1][i - 1] + indicator
            );
        }
    }

    return matrix[text2.length][text1.length];
}

// Agregar event listener para búsqueda en tiempo real
document.getElementById('search')?.addEventListener('input', debounce(filterTable, 300));

// Función debounce para mejorar el rendimiento
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Funciones para manejar los modales
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        document.body.classList.add('modal-active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('opacity-0', 'pointer-events-none');
        document.body.classList.remove('modal-active');
    }
}

// Manejar el formulario de edición
document.getElementById('editForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'edit');

    try {
        validateForm(formData);
        
        const response = await fetch('process.php', {
            method: 'POST',
            body: formData
        });

        let data;
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            // Si no es JSON, intentamos leer como texto
            const text = await response.text();
            console.error('Respuesta no JSON:', text);
            throw new Error('La respuesta del servidor no es JSON válido');
        }

        if (!response.ok) {
            throw new Error(`Error del servidor: ${response.status}`);
        }

        if (data.success) {
            showMessage('Registro actualizado exitosamente', 'success');
            closeModal('editModal');
            
            // Recargar los registros según la vista actual
            const currentView = document.getElementById('currentView').textContent;
            if (currentView === 'Registros de Hoy') {
                await viewTodayRecords();
            } else {
                await viewAllRecords();
            }
        } else {
            throw new Error(data.message || 'Error al actualizar el registro');
        }
    } catch (error) {
        console.error('Error detallado:', error);
        showMessage(`Error: ${error.message}`, 'error');
    }
});

// Cargar los registros al iniciar la página
document.addEventListener('DOMContentLoaded', loadTodayRecords);

// Modificar viewAllRecords para eliminar referencias a filtros
async function viewAllRecords() {
    try {
        document.getElementById('currentView').textContent = 'Todos los Registros';
        showMessage('Cargando todos los registros...', 'info');
        
        const response = await fetch('get_all_records.php');
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
        
        const data = await response.json();
        
        if (data.success) {
            const tableBody = document.getElementById('tableBody');
            tableBody.innerHTML = '';

            if (data.data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="12" class="text-center py-4">No se encontraron registros.</td></tr>';
                showMessage('No se encontraron registros.', 'info');
                return;
            }

            data.data.forEach(record => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-100';
                row.setAttribute('data-id', record.id);
                row.innerHTML = `
                    <td class="py-2 px-4 border-b">${record.id}</td>
                    <td class="py-2 px-4 border-b">${escapeHtml(record.nombre)}</td>
                    <td class="py-2 px-4 border-b">${record.fecha}</td>
                    <td class="py-2 px-4 border-b">${record.entrada}</td>
                    <td class="py-2 px-4 border-b">${record.salida}</td>
                    <td class="py-2 px-4 border-b">${record.entrada2 || ''}</td>
                    <td class="py-2 px-4 border-b">${record.salida2 || ''}</td>
                    <td class="py-2 px-4 border-b">${record.entrada3 || ''}</td>
                    <td class="py-2 px-4 border-b">${record.salida3 || ''}</td>
                    <td class="py-2 px-4 border-b">${escapeHtml(record.obs || '')}</td>
                    <td class="py-2 px-4 border-b">${escapeHtml(record.mes)}</td>
                    <td class="py-2 px-4 border-b flex space-x-2">
                        <button class="text-blue-500 hover:text-blue-700" onclick="viewRecord(${record.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="text-yellow-500 hover:text-yellow-700" onclick="editRecord(${record.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-500 hover:text-red-700" onclick="deleteRecord(${record.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            
            showMessage('Registros cargados exitosamente', 'success');
        } else {
            throw new Error(data.message || 'Error al cargar los registros');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage(`Error al cargar los registros: ${error.message}`, 'error');
    }
}

// Función para ver registros de hoy
async function viewTodayRecords() {
    try {
        document.getElementById('currentView').textContent = 'Registros de Hoy';
        showMessage('Cargando registros de hoy...', 'info');
        
        const response = await fetch('get_today_records.php');
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.success) {
            const tableBody = document.getElementById('tableBody');
            tableBody.innerHTML = '';

            if (data.data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="12" class="text-center py-4">No se encontraron registros para hoy.</td></tr>';
                showMessage('No hay registros para hoy.', 'info');
                return;
            }

            data.data.forEach(record => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-100';
                row.setAttribute('data-id', record.id);
                row.innerHTML = `
                    <td class="py-2 px-4 border-b">${record.id}</td>
                    <td class="py-2 px-4 border-b">${escapeHtml(record.nombre)}</td>
                    <td class="py-2 px-4 border-b">${record.fecha}</td>
                    <td class="py-2 px-4 border-b">${record.entrada}</td>
                    <td class="py-2 px-4 border-b">${record.salida}</td>
                    <td class="py-2 px-4 border-b">${record.entrada2 || ''}</td>
                    <td class="py-2 px-4 border-b">${record.salida2 || ''}</td>
                    <td class="py-2 px-4 border-b">${record.entrada3 || ''}</td>
                    <td class="py-2 px-4 border-b">${record.salida3 || ''}</td>
                    <td class="py-2 px-4 border-b">${escapeHtml(record.obs || '')}</td>
                    <td class="py-2 px-4 border-b">${escapeHtml(record.mes)}</td>
                    <td class="py-2 px-4 border-b flex space-x-2">
                        <button class="text-blue-500 hover:text-blue-700" onclick="viewRecord(${record.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="text-yellow-500 hover:text-yellow-700" onclick="editRecord(${record.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-500 hover:text-red-700" onclick="deleteRecord(${record.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            showMessage('Registros de hoy cargados exitosamente', 'success');
        } else {
            throw new Error(data.message || 'Error al cargar los registros');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage(`Error al cargar los registros: ${error.message}`, 'error');
    }
}

function logout() {
    window.location.href = "/porteria/usuarios/logout.php";
} 