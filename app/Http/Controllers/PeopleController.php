<?php

namespace App\Http\Controllers;

use App\Models\People;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Http\JsonResponse;

class PeopleController extends Controller
{
    public function index(): View
    {
        $people = People::all();
        return view('people.index', compact('people'));
    }

    public function download($id)
    {
        $person = People::query()->findOrFail($id);
        
        $files = [
            public_path('photos/' . $person->id . '/1.png'),
            public_path('layout/2.png'),
            public_path('layout/3.png')
        ];

        $zip = new \ZipArchive();
        $zipFileName = storage_path('app/public/downloads/' . $person->id . '_files.zip');

        if ($zip->open($zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $index => $file) {
                $zip->addFile($file, ($index + 1) . '.png');
            }
            $zip->close();

            $person->update(['is_downloaded' => true]);

            return response()->json([
                'success' => true,
                'download_url' => asset('storage/downloads/' . $person->id . '_files.zip')
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Помилка при створенні архіву'], 500);
    }

    public function list(): View
    {
        $people = People::query()
            ->select(['id', 'name', 'status', 'people_count'])
            ->with(['tables' => function($query) {
                $query->select(['tables.id', 'name']);
            }])
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $people->sum('people_count'),
            'confirmed' => $people->where('status', People::STATUS_CONFIRMED)->sum('people_count'),
            'declined' => $people->where('status', People::STATUS_DECLINED)->sum('people_count'),
            'pending' => $people->where('status', People::STATUS_PENDING)->sum('people_count'),
        ];

        return view('people.list', compact('people', 'stats'));
    }

    public function updatePeopleCount($id): JsonResponse
    {
        $person = People::query()->findOrFail($id);
        $count = request('count');

        if (!is_numeric($count) || $count < 1) {
            return response()->json(['success' => false, 'message' => 'Неправильна кількість'], 400);
        }

        $person->update(['people_count' => (int)$count]);

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => People::sum('people_count'),
                'confirmed' => People::where('status', People::STATUS_CONFIRMED)->sum('people_count'),
                'declined' => People::where('status', People::STATUS_DECLINED)->sum('people_count'),
                'pending' => People::where('status', People::STATUS_PENDING)->sum('people_count'),
            ]
        ]);
    }

    public function updateStatus($id): JsonResponse
    {
        $person = People::query()->findOrFail($id);
        $newStatus = request('status');

        if (!in_array($newStatus, [People::STATUS_CONFIRMED, People::STATUS_DECLINED, People::STATUS_PENDING])) {
            return response()->json(['success' => false, 'message' => 'Неправильний статус'], 400);
        }

        $person->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => People::sum('people_count'),
                'confirmed' => People::where('status', People::STATUS_CONFIRMED)->sum('people_count'),
                'declined' => People::where('status', People::STATUS_DECLINED)->sum('people_count'),
                'pending' => People::where('status', People::STATUS_PENDING)->sum('people_count'),
            ]
        ]);
    }
}
