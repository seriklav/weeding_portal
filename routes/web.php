<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\TableController;

Route::get('/', [PeopleController::class, 'index'])->name('people.index');
Route::get('/download/{id}', [PeopleController::class, 'download'])->name('people.download');
Route::get('/list', [PeopleController::class, 'list'])->name('people.list');
Route::post('/people/{id}/name', [PeopleController::class, 'updatePeopleName'])->name('people.update-name');
Route::post('/people/{id}/status', [PeopleController::class, 'updateStatus'])->name('people.update-status');
Route::post('/people/{id}/count', [PeopleController::class, 'updatePeopleCount'])->name('people.update-count');

// Маршруты для управления столами
Route::resource('tables', TableController::class);
Route::post('/tables/{id}/guests', [TableController::class, 'addGuest']);
Route::delete('/tables/{id}/guests/{personId}', [TableController::class, 'removeGuest']);
