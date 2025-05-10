<?php

namespace App\Console\Commands;

use App\Models\People;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImportPeople extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-people {source_path} {layout_path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import people from photos directory';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourcePath = $this->argument('source_path');
        $layoutPath = $this->argument('layout_path');

        if (!File::isDirectory($sourcePath)) {
            $this->error('Source directory not found!');
            return 1;
        }

        if (!File::isDirectory($layoutPath)) {
            $this->error('Layout directory not found!');
            return 1;
        }

        // Копируем файлы layout
        if (!File::isDirectory(public_path('layout'))) {
            File::makeDirectory(public_path('layout'), 0755, true);
        }
        File::copy($layoutPath . '/2.png', public_path('layout/2.png'));
        File::copy($layoutPath . '/3.png', public_path('layout/3.png'));

        // Создаем директорию для фотографий
        if (!File::isDirectory(public_path('photos'))) {
            File::makeDirectory(public_path('photos'), 0755, true);
        }

        // Импортируем фотографии
        $files = File::files($sourcePath);
        foreach ($files as $file) {
            $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            // Создаем запись в базе
            $person = People::create([
                'name' => $name,
                'photo_path' => ''
            ]);

            // Создаем директорию для пользователя
            $userDir = 'photos/' . $person->id;
            if (!File::isDirectory(public_path($userDir))) {
                File::makeDirectory(public_path($userDir), 0755, true);
            }

            // Копируем файл как 1.png
            $newPath = $userDir . '/1.png';
            File::copy($file->getRealPath(), public_path($newPath));

            // Обновляем путь в базе
            $person->update([
                'photo_path' => $newPath
            ]);

            $this->info("Imported: {$name}");
        }

        $this->info('Import completed successfully!');
        return 0;
    }
}
