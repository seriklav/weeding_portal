<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список запрошень</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Список запрошень</h1>
            <a href="{{ route('people.list') }}" class="btn btn-outline-primary">Список гостей</a>
        </div>
        
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row">
            @foreach($people as $person)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="{{ asset($person->photo_path) }}" class="card-img-top" alt="{{ $person->name }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $person->name }}</h5>
                            <div class="d-flex align-items-center gap-2">
                                <button onclick="downloadFiles({{ $person->id }}, this)" class="btn btn-primary">Завантажити файли</button>
                                <div id="status-{{ $person->id }}" class="download-status">
                                    @if($person->is_downloaded)
                                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function downloadFiles(id, button) {
            button.disabled = true;
            button.innerHTML = 'Завантаження...';

            fetch(`/download/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Создаем ссылку для скачивания
                        const link = document.createElement('a');
                        link.href = data.download_url;
                        link.download = 'files.zip';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        // Показываем галочку
                        const statusDiv = document.getElementById(`status-${id}`);
                        statusDiv.innerHTML = '<i class="bi bi-check-circle-fill text-success fs-4"></i>';
                    } else {
                        alert('Ошибка при скачивании файлов');
                    }
                })
                .catch(error => {
                    alert('Ошибка при скачивании файлов');
                })
                .finally(() => {
                    button.disabled = false;
                    button.innerHTML = 'Завантажити файли';
                });
        }
    </script>
</body>
</html>
