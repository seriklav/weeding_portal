<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список столів</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Список столів</h1>
            <div>
                <a href="{{ route('people.list') }}" class="btn btn-outline-primary me-2">До списку гостей</a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTableModal">
                    <i class="bi bi-plus-lg"></i> Додати стіл
                </button>
            </div>
        </div>

        <div class="row">
            @foreach($tables as $table)
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title d-flex justify-content-between align-items-center">
                            {{ $table->name }}
                            <span class="badge bg-{{ $table->total_guests > $table->capacity ? 'danger' : 'primary' }}">{{ $table->total_guests }}/{{ $table->capacity }}</span>
                        </h5>
                        @if($table->description)
                            <p class="card-text text-muted">{{ $table->description }}</p>
                        @endif
                        <div class="mt-3">
                            <button class="btn btn-sm btn-outline-primary" onclick="showGuests({{ $table->id }})">
                                <i class="bi bi-people"></i> Гості за столом
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="addGuest({{ $table->id }})">
                                <i class="bi bi-person-plus"></i> Додати гостя
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="editTable({{ $table->id }}, `{{ $table->name }}`, {{ $table->capacity }}, `{{ $table->description ?? '' }}`)">
                                <i class="bi bi-pencil"></i> Редагувати
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTable({{ $table->id }})">
                                <i class="bi bi-trash"></i> Видалити стіл
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Модальное окно для редактирования стола -->
    <div class="modal fade" id="editTableModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редагувати стіл</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editTableForm">
                        <input type="hidden" name="table_id" id="editTableId">
                        <div class="mb-3">
                            <label class="form-label">Назва/номер столу</label>
                            <input type="text" class="form-control" name="name" id="editTableName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Місткість</label>
                            <input type="number" class="form-control" name="capacity" id="editTableCapacity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Опис</label>
                            <textarea class="form-control" name="description" id="editTableDescription" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <button type="button" class="btn btn-primary" onclick="submitEditTable()">Зберегти</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для просмотра гостей -->
    <div class="modal fade" id="showGuestsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Гості за столом <span id="showGuestsTableName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ім'я</th>
                                    <th>Кількість осіб</th>
                                    <th>Номер місця</th>
                                    <th>Дії</th>
                                </tr>
                            </thead>
                            <tbody id="guestsList"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для добавления гостя -->
    <div class="modal fade" id="addGuestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Додати гостя за стіл</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addGuestForm">
                        <input type="hidden" name="table_id" id="addGuestTableId">
                        <div class="mb-3">
                            <label class="form-label">Оберіть гостя</label>
                            <select class="form-select" name="person_id" required>
                                @if($availableGuests->isEmpty())
                                    <option value="" disabled>Всі гості вже розміщені за столами</option>
                                @else
                                    @foreach($availableGuests as $person)
                                        <option value="{{ $person->id }}">
                                            {{ $person->name }} ({{ $person->people_count }} осіб)
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Номер місця (необов'язково)</label>
                            <input type="number" class="form-control" name="seat_number" min="1">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <button type="button" class="btn btn-primary" onclick="submitAddGuest()">Додати</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для создания стола -->
    <div class="modal fade" id="createTableModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Додати стіл</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createTableForm">
                        <div class="mb-3">
                            <label class="form-label">Назва/номер столу</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Місткість</label>
                            <input type="number" class="form-control" name="capacity" min="1" value="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Опис</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <button type="button" class="btn btn-primary" onclick="createTable()">Створити</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function createTable() {
            const form = document.getElementById('createTableForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('/tables', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }

        function showGuests(tableId) {
            fetch(`/tables/${tableId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('showGuestsTableName').textContent = data.table.name;
                    const tbody = document.getElementById('guestsList');
                    tbody.innerHTML = '';

                    data.guests.forEach(guest => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${guest.name}</td>
                            <td>${guest.people_count} осіб</td>
                            <td>${guest.pivot.seat_number || '-'}</td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="removeGuest(${tableId}, ${guest.id})">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    new bootstrap.Modal(document.getElementById('showGuestsModal')).show();
                });
        }

        function removeGuest(tableId, personId) {
            if (!confirm('Вы уверены, что хотите убрать гостя со стола?')) {
                return;
            }

            fetch(`/tables/${tableId}/guests/${personId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }

        function addGuest(tableId) {
            document.getElementById('addGuestTableId').value = tableId;
            new bootstrap.Modal(document.getElementById('addGuestModal')).show();
        }

        function editTable(tableId, name, capacity, description) {
            document.getElementById('editTableId').value = tableId;
            document.getElementById('editTableName').value = name;
            document.getElementById('editTableCapacity').value = capacity;
            document.getElementById('editTableDescription').value = description || '';

            new bootstrap.Modal(document.getElementById('editTableModal')).show();
        }

        function submitEditTable() {
            const form = document.getElementById('editTableForm');
            const formData = new FormData(form);
            const tableId = formData.get('table_id');
            const data = {
                name: formData.get('name'),
                capacity: parseInt(formData.get('capacity')),
                description: formData.get('description')
            };

            fetch(`/tables/${tableId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Помилка при збереженні');
                }
            });
        }

        function deleteTable(tableId) {
            if (!confirm('Ви впевнені, що хочете видалити цей стіл? Всі гості будуть відв\'язані від нього.')) {
                return;
            }

            fetch(`/tables/${tableId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Ошибка при удалении стола');
                }
            });
        }

        function submitAddGuest() {
            const form = document.getElementById('addGuestForm');
            const formData = new FormData(form);
            const tableId = formData.get('table_id');
            const data = {
                person_id: formData.get('person_id'),
                seat_number: formData.get('seat_number') || null
            };

            fetch(`/tables/${tableId}/guests`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    </script>
</body>
</html>
