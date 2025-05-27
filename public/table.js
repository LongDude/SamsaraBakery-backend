document.addEventListener('DOMContentLoaded', function() {
    // Initialize all tables with the table-container class
    document.querySelectorAll('.table-container').forEach(container => {
        const config = JSON.parse(container.getAttribute('data-table-config'));
        initializeTable(container, config);
    });

    // Handle nav-section link selection
    document.querySelectorAll('.nav-section a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tableName = this.getAttribute('data-table');
            loadTableData(tableName);
        });
    });
});

function initializeTable(container, config) {
    // Initialize search functionality
    const searchInput = container.querySelector('.search-input');
    const searchResults = container.querySelector('.search-results');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            const query = this.value.trim();
            if (query.length > 2) {
                fetch(`${searchInput.dataset.endpoint}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => displaySearchResults(data, searchResults, config))
                    .catch(error => console.error('Search error:', error));
            } else {
                searchResults.classList.add('d-none');
            }
        }, 300));

        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchResults.contains(e.target) && e.target !== searchInput) {
                searchResults.classList.add('d-none');
            }
        });
    }

    // Initialize sorting
    container.querySelectorAll('th.sortable').forEach(header => {
        header.addEventListener('click', function() {
            const field = this.dataset.field;
            const currentOrder = this.getAttribute('data-order') || 'asc';
            const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
            
            // Reset all sort indicators
            container.querySelectorAll('th.sortable i').forEach(icon => {
                icon.className = 'fas fa-sort';
            });
            
            // Set new sort indicator
            const icon = this.querySelector('i');
            icon.className = `fas fa-sort-${newOrder}`;
            this.setAttribute('data-order', newOrder);
            
            // Load sorted data
            loadTableData(config.table_name, {
                sort: field,
                order: newOrder,
                page: 1
            });
        });
    });

    // Initialize pagination
    container.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.dataset.page;
            loadTableData(config.table_name, { page: page });
        });
    });

    // Initialize inline editing
    container.querySelectorAll('td.editable').forEach(cell => {
        cell.addEventListener('click', function() {
            if (this.classList.contains('editing')) return;
            
            const field = this.dataset.field;
            const type = this.dataset.type || 'text';
            const id = this.closest('tr').dataset.id;
            const originalValue = this.textContent.trim();
            
            this.classList.add('editing');
            
            let input;
            if (type === 'select') {
                input = document.createElement('select');
                // You would populate this with options from your config
                // For example: config.columns.find(c => c.field === field).options
            } else {
                input = document.createElement('input');
                input.type = type;
                input.value = originalValue;
            }
            
            input.className = 'form-control form-control-sm';
            
            const saveButton = document.createElement('button');
            saveButton.className = 'btn btn-sm btn-success ms-1';
            saveButton.innerHTML = '<i class="fas fa-check"></i>';
            
            const cancelButton = document.createElement('button');
            cancelButton.className = 'btn btn-sm btn-danger ms-1';
            cancelButton.innerHTML = '<i class="fas fa-times"></i>';
            
            this.innerHTML = '';
            this.appendChild(input);
            this.appendChild(saveButton);
            this.appendChild(cancelButton);
            input.focus();
            
            const saveEdit = () => {
                const newValue = input.value;
                fetch(`/api/${config.table_name}/${id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ [field]: newValue })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.textContent = newValue;
                        this.classList.remove('editing');
                    } else {
                        alert('Error updating: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.innerHTML = originalValue;
                    this.classList.remove('editing');
                });
            };
            
            const cancelEdit = () => {
                this.innerHTML = originalValue;
                this.classList.remove('editing');
            };
            
            saveButton.addEventListener('click', saveEdit);
            cancelButton.addEventListener('click', cancelEdit);
            
            input.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') saveEdit();
                if (e.key === 'Escape') cancelEdit();
            });
        });
    });

    // Initialize actions
    container.querySelectorAll('[data-action]').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            const id = this.dataset.id;
            
            if (action === 'delete') {
                if (confirm('Are you sure you want to delete this item?')) {
                    fetch(`/api/${config.table_name}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('tr').remove();
                        } else {
                            alert('Error deleting: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            } else if (action === 'edit') {
                // Open edit modal
                openEditModal(config, id);
            } else if (action === 'add') {
                // Open add modal
                openAddModal(config);
            }
        });
    });
}

function loadTableData(tableName, params = {}) {
    const url = new URL(`/api/${tableName}`, window.location.origin);
    
    // Add params to URL
    Object.keys(params).forEach(key => {
        url.searchParams.append(key, params[key]);
    });
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            // Update the table with new data
            // You would need to implement this based on your table structure
            updateTable(data);
        })
        .catch(error => console.error('Error loading table data:', error));
}

function updateTable(data) {
    // Implement this function to update your table with new data
    // This would involve rebuilding the table body with the new data
    console.log('Update table with:', data);
}

function displaySearchResults(results, container, config) {
    container.innerHTML = '';
    
    if (results.length === 0) {
        const noResults = document.createElement('div');
        noResults.className = 'result-item';
        noResults.textContent = 'No results found';
        container.appendChild(noResults);
    } else {
        results.forEach(item => {
            const resultItem = document.createElement('div');
            resultItem.className = 'result-item';
            
            // Create a display string based on config
            let displayText = '';
            if (config.search_display) {
                displayText = config.search_display.split(',').map(field => {
                    return item[field.trim()];
                }).join(' - ');
            } else {
                displayText = item[Object.keys(item)[0]];
            }
            
            resultItem.textContent = displayText;
            resultItem.addEventListener('click', function() {
                // Handle selection - could navigate to edit or highlight in table
                console.log('Selected:', item);
                container.classList.add('d-none');
            });
            
            container.appendChild(resultItem);
        });
    }
    
    container.classList.remove('d-none');
}

function openEditModal(config, id) {
    // Fetch the item data
    fetch(`/api/${config.table_name}/${id}`)
        .then(response => response.json())
        .then(item => {
            // Create and show modal with form
            const modal = createModal(`Edit ${config.table_name}`, 'Save Changes');
            
            // Build form fields based on config
            const form = document.createElement('form');
            config.columns.forEach(column => {
                if (column.editable) {
                    const group = document.createElement('div');
                    group.className = 'mb-3';
                    
                    const label = document.createElement('label');
                    label.className = 'form-label';
                    label.textContent = column.label;
                    label.htmlFor = `edit-${column.field}`;
                    
                    let input;
                    if (column.type === 'select') {
                        input = document.createElement('select');
                        input.className = 'form-select';
                        // Populate options
                    } else {
                        input = document.createElement('input');
                        input.type = column.type || 'text';
                        input.className = 'form-control';
                    }
                    
                    input.id = `edit-${column.field}`;
                    input.name = column.field;
                    input.value = item[column.field] || '';
                    
                    group.appendChild(label);
                    group.appendChild(input);
                    form.appendChild(group);
                }
            });
            
            modal.querySelector('.modal-body').appendChild(form);
            
            // Handle form submission
            modal.querySelector('.btn-primary').addEventListener('click', function() {
                const formData = new FormData(form);
                const jsonData = {};
                formData.forEach((value, key) => {
                    jsonData[key] = value;
                });
                
                fetch(`/api/${config.table_name}/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(jsonData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh table or update specific row
                        loadTableData(config.table_name);
                        bootstrap.Modal.getInstance(modal).hide();
                    } else {
                        alert('Error updating: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
            
            // Show modal
            new bootstrap.Modal(modal).show();
        })
        .catch(error => console.error('Error:', error));
}

function openAddModal(config) {
    // Create and show modal with form
    const modal = createModal(`Add New ${config.table_name}`, 'Add');
    
    // Build form fields based on config
    const form = document.createElement('form');
    config.columns.forEach(column => {
        if (column.editable) {
            const group = document.createElement('div');
            group.className = 'mb-3';
            
            const label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = column.label;
            label.htmlFor = `add-${column.field}`;
            
            let input;
            if (column.type === 'select') {
                input = document.createElement('select');
                input.className = 'form-select';
                // Populate options
            } else {
                input = document.createElement('input');
                input.type = column.type || 'text';
                input.className = 'form-control';
            }
            
            input.id = `add-${column.field}`;
            input.name = column.field;
            
            group.appendChild(label);
            group.appendChild(input);
            form.appendChild(group);
        }
    });
    
    modal.querySelector('.modal-body').appendChild(form);
    
    // Handle form submission
    modal.querySelector('.btn-primary').addEventListener('click', function() {
        const formData = new FormData(form);
        const jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });
        
        fetch(`/api/${config.table_name}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(jsonData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh table
                loadTableData(config.table_name);
                bootstrap.Modal.getInstance(modal).hide();
            } else {
                alert('Error adding: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
    
    // Show modal
    new bootstrap.Modal(modal).show();
}

function createModal(title, primaryButtonText) {
    const modalId = 'modal-' + Math.random().toString(36).substr(2, 9);
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = modalId;
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">${primaryButtonText}</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    return modal;
}

function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}