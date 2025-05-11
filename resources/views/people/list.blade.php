<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список гостей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Список гостей</h1>
            <div>
                <a href="{{ route('tables.index') }}" class="btn btn-outline-primary me-2">
                    <i class="bi bi-grid"></i> Управління столами
                </a>
                <a href="{{ route('people.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-download"></i> Повернутися до запрошень
                </a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Усього гостей</h5>
                        <p class="card-text" id="total-count">{{ $stats['total'] }} осіб</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-success">Підтвердили</h5>
                        <p class="card-text" id="confirmed-count">{{ $stats['confirmed'] }} осіб</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-danger">Відмовились</h5>
                        <p class="card-text" id="declined-count">{{ $stats['declined'] }} осіб</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-warning">Очікують</h5>
                        <p class="card-text" id="pending-count">{{ $stats['pending'] }} осіб</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3 d-flex gap-2 align-items-center">
            <select class="form-select w-auto" id="statusFilter" onchange="filterByStatus(this.value)">
                <option value="all">Всі статуси</option>
                <option value="pending">Очікують</option>
                <option value="confirmed">Підтвердили</option>
                <option value="declined">Відмовились</option>
            </select>
            <select class="form-select w-auto" id="tableFilter" onchange="filterByTable(this.value)">
                <option value="all">Всі столи</option>
                <option value="none">Без столу</option>
                @foreach(\App\Models\Table::orderBy('name')->get() as $table)
                    <option value="{{ $table->id }}">Стіл {{ $table->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ім'я</th>
                        <th>Кількість</th>
                        <th>Статус</th>
                        <th>Столи</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($people as $key => $person)
                    <tr data-status="{{ $person->status }}">
                        <td>{{ $key + 1 }}</td>
                        <td><span class="editable-name" data-id="{{ $person->id }}">{{ $person->name }}</span></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="adjustCount({{ $person->id }}, -1, this)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control text-center" value="{{ $person->people_count }}"
                                       min="1" id="count-{{ $person->id }}"
                                       onchange="updatePeopleCount({{ $person->id }}, this.value)">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="adjustCount({{ $person->id }}, 1, this)">
                                    <i class="bi bi-plus"></i>
                                </button>
                                <span class="input-group-text">осіб</span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $person->status }}">
                                @if($person->status === 'confirmed')
                                    <i class="bi bi-check-circle-fill text-success"></i> Підтвердили
                                @elseif($person->status === 'declined')
                                    <i class="bi bi-x-circle-fill text-danger"></i> Відмовились
                                @else
                                    <i class="bi bi-question-circle-fill text-warning"></i> Очікують
                                @endif
                            </span>
                        </td>
                        <td>
                            @if($person->tables->isNotEmpty())
                                @foreach($person->tables as $table)
                                    <span class="badge bg-info me-1" data-table-id="{{ $table->id }}">Стіл {{ $table->name }}</span>
                                @endforeach
                            @else
                                <span class="badge bg-secondary">Не призначено</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <button onclick="updateStatus({{ $person->id }}, 'confirmed')" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button onclick="updateStatus({{ $person->id }}, 'declined')" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                <button onclick="updateStatus({{ $person->id }}, 'pending')" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-question-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function applyFilters() {
            const status = document.getElementById('statusFilter').value;
            const tableId = document.getElementById('tableFilter').value;

            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                const statusMatch = status === 'all' || row.getAttribute('data-status') === status;
                const tableMatch = tableId === 'all' ||
                    (tableId === 'none' && !row.querySelector('.badge.bg-info')) ||
                    (row.querySelector(`.badge[data-table-id="${tableId}"]`));

                row.style.display = statusMatch && tableMatch ? '' : 'none';
            });
        }

        function filterByStatus(status) {
            applyFilters();
        }

        function filterByTable(tableId) {
            applyFilters();
        }

        function adjustCount(id, delta, button) {
            const input = document.getElementById(`count-${id}`);
            const newValue = Math.max(1, parseInt(input.value) + delta);
            input.value = newValue;
            updatePeopleCount(id, newValue);
        }

        function updatePeopleCount(id, count) {
            fetch(`/people/${id}/count`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ count: count })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем счетчики
                    document.getElementById('total-count').textContent = data.stats.total + ' осіб';
                    document.getElementById('confirmed-count').textContent = data.stats.confirmed + ' осіб';
                    document.getElementById('declined-count').textContent = data.stats.declined + ' осіб';
                    document.getElementById('pending-count').textContent = data.stats.pending + ' осіб';
                } else {
                    alert('Ошибка при обновлении статуса');
                }
            })
            .catch(error => {
                alert('Ошибка при обновлении статуса');
            });
        }

        function updateStatus(id, status) {
            fetch(`/people/${id}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем статус в строке
                    const row = document.querySelector(`tr:has(button[onclick*="${id}"])`);
                    const statusCell = row.querySelector('.status-badge');

                    let newStatusHtml = '';
                    if (status === 'confirmed') {
                        newStatusHtml = '<i class="bi bi-check-circle-fill text-success"></i> Підтвердили';
                    } else if (status === 'declined') {
                        newStatusHtml = '<i class="bi bi-x-circle-fill text-danger"></i> Відмовились';
                    } else {
                        newStatusHtml = '<i class="bi bi-question-circle-fill text-warning"></i> Очікують';
                    }

                    statusCell.innerHTML = newStatusHtml;
                    statusCell.className = `status-badge status-${status}`;

                    // Обновляем счетчики
                    document.getElementById('total-count').textContent = data.stats.total + ' осіб';
                    document.getElementById('confirmed-count').textContent = data.stats.confirmed + ' осіб';
                    document.getElementById('declined-count').textContent = data.stats.declined + ' осіб';
                    document.getElementById('pending-count').textContent = data.stats.pending + ' осіб';
                } else {
                    alert('Ошибка при обновлении статуса');
                }
            })
            .catch(error => {
                alert('Ошибка при обновлении статуса');
            });
        }
    </script>

    <style>
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .status-badge i {
            font-size: 1.2rem;
        }
        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-text {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }
        .input-group-sm {
            width: 180px;
        }
        .input-group-sm input[type="number"] {
            text-align: center;
            width: 60px !important;
            padding: 0.25rem;
        }
        .input-group-sm .btn {
            padding: 0.25rem 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .input-group-sm .btn i {
            font-size: 0.875rem;
        }
        /* Убираем стрелки у input[type=number] */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
        .input-group-text {
            background-color: #f8f9fa;
            font-size: 0.875rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.editable-name').forEach(attachEditHandler);

            function attachEditHandler(span) {
                span.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const currentText = this.textContent.trim();

                    const input = document.createElement('input');
                    input.type = 'text';
                    input.value = currentText;
                    input.className = 'form-control form-control-sm';
                    input.style.width = 'auto';
                    input.style.display = 'inline-block';

                    this.replaceWith(input);
                    input.focus();

                    input.addEventListener('blur', function () {
                        const newName = input.value.trim();

                        const newSpan = document.createElement('span');
                        newSpan.className = 'editable-name';
                        newSpan.dataset.id = id;
                        newSpan.textContent = newName || currentText;

                        input.replaceWith(newSpan);
                        attachEditHandler(newSpan);

                        if (newName && newName !== currentText) {
                            fetch(`/people/${id}/name`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ name: newName })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    document.getElementById('total-count').textContent = data.stats.total + ' осіб';
                                    document.getElementById('confirmed-count').textContent = data.stats.confirmed + ' осіб';
                                    document.getElementById('declined-count').textContent = data.stats.declined + ' осіб';
                                    document.getElementById('pending-count').textContent = data.stats.pending + ' осіб';
                                });
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
