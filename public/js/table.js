class TableManager {
    constructor(config = {}) {
        // Базовые настройки
        this.config = {
            tableType: config.tableType || '',
            apiEndpoint: config.apiEndpoint || '/api',
            searchEndpoint: config.searchEndpoint || '/api/search',
            searchDelay: config.searchDelay || 300,
            deleteConfirmMessage: config.deleteConfirmMessage || 'Вы уверены, что хотите удалить этот элемент?',
            addModalId: config.addModalId || 'addItemModal',
            addFormId: config.addFormId || 'addItemForm',
            addButtonId: config.addButtonId || 'saveItem',
            ...config
        };

        // Состояние таблицы
        this.currentTable = this.config.tableType;
        this.currentPage = 1;
        this.currentSort = {
            field: null,
            direction: 'ASC'
        };
        this.currentSearch = '';
        this.searchTimeout = null;
        this.lastSearchValue = '';

        // Инициализация при создании
        this.initializeTableHandlers();
    }

    initNavigationHandlers() {
        document.querySelectorAll('.nav-link[data-table]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tableType = link.dataset.table;
                if (tableType) {
                    // Обновляем конфигурацию для нового типа таблицы
                    this.config.tableType = tableType;
                    this.currentTable = tableType;
                    
                    // Загружаем таблицу
                    this.loadTable(tableType);
                }
            });
        });
    }

    initSort() {
        document.querySelectorAll('th.sortable').forEach(header => {
            header.addEventListener('click', () => {
                const field = header.dataset.sort;
                if (!field) return;

                // Определяем направление сортировки
                if (this.currentSort.field === field) {
                    this.currentSort.direction = this.currentSort.direction === 'ASC' ? 'DESC' : 'ASC';
                } else {
                    this.currentSort.field = field;
                    this.currentSort.direction = 'ASC';
                }

                // Обновляем иконки сортировки
                document.querySelectorAll('th.sortable i').forEach(icon => {
                    icon.className = 'fas fa-sort';
                });

                const icon = header.querySelector('i');
                if (icon) {
                    icon.className = `fas fa-sort-${this.currentSort.direction === 'ASC' ? 'up' : 'down'}`;
                }

                // Загружаем таблицу с новой сортировкой
                this.loadTable(
                    this.currentTable,
                    this.currentPage,
                    this.currentSort,
                    this.currentSearch
                );
            });
        });
    }

    loadTable(tableType, page = 1, sort = null, search = '') {
        this.currentTable = tableType;
        this.currentPage = page;
        
        // Обновляем состояние сортировки
        if (sort) {
            this.currentSort = sort;
        }
        
        this.currentSearch = search;

        // Формируем URL с параметрами сортировки
        const params = new URLSearchParams({
            page: page,
            search: search
        });

        if (this.currentSort.field) {
            params.append('sortfield', this.currentSort.field);
            params.append('sortorder', this.currentSort.direction);
        }

        const url = `${this.config.apiEndpoint}/${tableType}?${params.toString()}`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                const tableContainer = document.getElementById('tableContainer');
                if (tableContainer) {
                    tableContainer.innerHTML = html;
                    this.initializeTableHandlers();
                } else {
                    console.error('Table container not found');
                }
            })
            .catch(error => {
                console.error('Error loading table:', error);
                const tableContainer = document.getElementById('tableContainer');
                if (tableContainer) {
                    tableContainer.innerHTML = '<div class="alert alert-danger">Ошибка загрузки таблицы</div>';
                }
            });
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
                    const endpoint = searchInput.dataset.endpoint || this.config.searchEndpoint;
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
                }, this.config.searchDelay);
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
                    fetch(`${this.config.apiEndpoint}/${this.currentTable}/${id}/edit`, {
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
                if (confirm(this.config.deleteConfirmMessage)) {
                    const id = button.dataset.id;
                    fetch(`${this.config.apiEndpoint}/${this.currentTable}/${id}`, {
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
                    .catch(error => console.error('Error deleting item:', error));
                }
            });
        });
    }

    initAddItem() {
        const saveButton = document.getElementById(this.config.addButtonId);
        if (saveButton) {
            saveButton.addEventListener('click', () => {
                const form = document.getElementById(this.config.addFormId);
                if (!form) return;

                const formData = new FormData(form);
                const data = {};
                
                // Собираем все поля формы в объект
                for (let [key, value] of formData.entries()) {
                    // Пытаемся преобразовать числовые значения
                    if (!isNaN(value) && value.trim() !== '') {
                        data[key] = Number(value);
                    } else {
                        data[key] = value;
                    }
                }

                fetch(`${this.config.apiEndpoint}/${this.currentTable}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById(this.config.addModalId));
                        modal.hide();
                        form.reset();
                        this.loadTable(this.currentTable);
                    }
                })
                .catch(error => console.error('Error adding item:', error));
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
        this.initAddItem();
        this.initPagination();
        this.initSort();
    }
}

// Инициализация всех таблиц на странице
document.addEventListener('DOMContentLoaded', () => {
    // Создаем основной экземпляр TableManager
    const mainTableManager = new TableManager({
        tableType: 'products', // значение по умолчанию
        apiEndpoint: '/api'
    });

    // Инициализируем обработчики навигации
    mainTableManager.initNavigationHandlers();

    // Находим все контейнеры таблиц
    document.querySelectorAll('.table-container').forEach(container => {
        try {
            // Получаем конфигурацию из data-атрибута
            const config = JSON.parse(container.dataset.tableConfig || '{}');
            
            // Создаем экземпляр TableManager для каждой таблицы
            const tableManager = new TableManager(config);
            
            // Инициализируем обработчики навигации
            tableManager.initNavigationHandlers();
        } catch (error) {
            console.error('Error initializing table:', error);
        }
    });
});