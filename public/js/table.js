class TableManager {
    constructor() {
        this.currentTable = null;
        this.currentPage = 1;
        this.currentSort = null;
        this.currentSearch = '';
        this.searchTimeout = null;
        this.lastSearchValue = '';
    }

    initNavigationHandlers() {
        document.querySelectorAll('.nav-link[data-table]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tableType = link.dataset.table;
                if (tableType) {
                    this.loadTable(tableType);
                }
            });
        });
    }

    loadTable(tableType, page = 1, sort = null, search = '') {
        this.currentTable = tableType;
        this.currentPage = page;
        this.currentSort = sort;
        this.currentSearch = search;

        const url = `/api/${tableType}?page=${page}&sort=${sort || ''}&search=${search}`;
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                document.getElementById('tableContainer').innerHTML = html;
                this.initializeTableHandlers();
            })
            .catch(error => console.error('Error loading table:', error));
    }

    initSearch() {
        const searchInput = document.querySelector('.search-input');
        const searchResults = document.querySelector('.search-results');
        
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const searchValue = searchInput.value.trim();
                
                if (this.searchTimeout) {
                    clearTimeout(this.searchTimeout);
                }

                if (searchValue === this.lastSearchValue) {
                    return;
                }

                this.lastSearchValue = searchValue;

                if (!searchValue) {
                    searchResults.classList.add('d-none');
                    return;
                }

                this.searchTimeout = setTimeout(() => {
                    const endpoint = searchInput.dataset.endpoint;
                    if (endpoint) {
                        fetch(`${endpoint}?q=${encodeURIComponent(searchValue)}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.results && data.results.length > 0) {
                                    searchResults.innerHTML = data.results
                                        .map(result => `
                                            <div class="result-item" data-value="${result.name}">
                                                ${result.name}
                                            </div>
                                        `).join('');
                                    searchResults.classList.remove('d-none');
                                } else {
                                    searchResults.classList.add('d-none');
                                }
                            })
                            .catch(error => console.error('Error fetching search results:', error));
                    }
                }, parseInt(searchInput.dataset.delay));
            });

            searchResults.addEventListener('click', (e) => {
                const resultItem = e.target.closest('.result-item');
                if (resultItem) {
                    searchInput.value = resultItem.dataset.value;
                    searchResults.classList.add('d-none');
                    this.loadTable(this.currentTable, 1, this.currentSort, resultItem.dataset.value);
                }
            });

            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('d-none');
                }
            });

            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchResults.classList.add('d-none');
                    this.loadTable(this.currentTable, 1, this.currentSort, searchInput.value.trim());
                }
            });
        }
    }

    initInlineEdit() {
        document.querySelectorAll('.editable').forEach(cell => {
            cell.addEventListener('click', () => {
                const field = cell.dataset.field;
                const type = cell.dataset.type;
                const value = cell.textContent.trim();
                const id = cell.closest('tr').dataset.id;

                const input = document.createElement('input');
                input.type = type;
                input.value = value;
                input.className = 'form-control form-control-sm';

                cell.textContent = '';
                cell.appendChild(input);
                input.focus();

                const saveEdit = () => {
                    const newValue = input.value;
                    fetch(`/api/products/${id}/edit`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            field: field,
                            value: newValue
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            cell.textContent = newValue;
                        } else {
                            cell.textContent = value;
                        }
                    })
                    .catch(() => {
                        cell.textContent = value;
                    });
                };

                input.addEventListener('blur', saveEdit);
                input.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        saveEdit();
                    }
                });
            });
        });
    }

    initDelete() {
        document.querySelectorAll('[data-action="delete"]').forEach(button => {
            button.addEventListener('click', () => {
                if (confirm('Вы уверены, что хотите удалить этот продукт?')) {
                    const id = button.dataset.id;
                    fetch(`/api/products/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            button.closest('tr').remove();
                        }
                    })
                    .catch(error => console.error('Error deleting product:', error));
                }
            });
        });
    }

    initAddProduct() {
        const saveButton = document.getElementById('saveProduct');
        if (saveButton) {
            saveButton.addEventListener('click', () => {
                const form = document.getElementById('addProductForm');
                const formData = new FormData(form);
                const data = {
                    name: formData.get('name'),
                    quantity: parseInt(formData.get('quantity')),
                    productionCost: parseFloat(formData.get('productionCost'))
                };

                fetch('/api/products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
                        modal.hide();
                        form.reset();
                        this.loadTable(this.currentTable);
                    }
                })
                .catch(error => console.error('Error adding product:', error));
            });
        }
    }

    initPagination() {
        document.querySelectorAll('.pagination .page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.dataset.page;
                if (page) {
                    this.loadTable(this.currentTable, page, this.currentSort, this.currentSearch);
                }
            });
        });
    }

    initializeTableHandlers() {
        this.initSearch();
        this.initInlineEdit();
        this.initDelete();
        this.initAddProduct();
        this.initPagination();
    }
}

// Initialize table manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const tableManager = new TableManager();
    tableManager.initNavigationHandlers();
}); 