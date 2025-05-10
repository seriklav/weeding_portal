<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\People;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class TableController extends Controller
{
    /**
     * Список всех столов
     */
    /**
     * Получить список гостей, которых можно добавить за стол
     */
    private function getAvailableGuests(): \Illuminate\Database\Eloquent\Collection
    {
        return People::where('status', 'confirmed')
            ->whereDoesntHave('tables')
            ->orderBy('name')
            ->get();
    }

    public function index(): View
    {
        $tables = Table::with('people')->get();
        $availableGuests = $this->getAvailableGuests();
        
        return view('tables.index', compact('tables', 'availableGuests'));
    }

    /**
     * Форма создания стола
     */
    public function create(): View
    {
        return view('tables.create');
    }

    /**
     * Сохранение нового стола
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string'
        ]);

        $table = Table::create($validated);

        return response()->json([
            'success' => true,
            'table' => $table
        ]);
    }

    /**
     * Показать информацию о столе
     */
    public function show(string $id): JsonResponse
    {
        $table = Table::with(['people' => function($query) {
            $query->select(['people.id', 'name', 'people_count']);
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'table' => $table,
            'guests' => $table->people
        ]);
    }

    /**
     * Форма редактирования стола
     */
    public function edit(string $id): View
    {
        $table = Table::findOrFail($id);
        return view('tables.edit', compact('table'));
    }

    /**
     * Обновление информации о столе
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $table = Table::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string'
        ]);

        $table->update($validated);

        return response()->json([
            'success' => true,
            'table' => $table
        ]);
    }

    /**
     * Удаление стола
     */
    public function destroy(string $id): JsonResponse
    {
        $table = Table::findOrFail($id);
        $table->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Добавить гостя за стол
     */
    public function addGuest(Request $request, string $id): JsonResponse
    {
        $table = Table::findOrFail($id);
        
        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
            'seat_number' => 'nullable|integer|min:1'
        ]);

        // Проверяем, что за столом есть место
        if ($table->people()->count() >= $table->capacity) {
            return response()->json([
                'success' => false,
                'message' => 'За столом нет свободных мест'
            ], 400);
        }

        // Проверяем, что гость уже не сидит за этим столом
        if ($table->people()->where('person_id', $validated['person_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Гость уже сидит за этим столом'
            ], 400);
        }

        $table->people()->attach($validated['person_id'], [
            'seat_number' => $validated['seat_number'] ?? null
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Убрать гостя со стола
     */
    public function removeGuest(string $id, string $personId): JsonResponse
    {
        $table = Table::findOrFail($id);
        $table->people()->detach($personId);

        return response()->json(['success' => true]);
    }
}
