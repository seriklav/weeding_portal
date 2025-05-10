<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $fillable = [
        'name',
        'capacity',
        'description'
    ];

    protected $appends = ['total_guests'];

    /**
     * Вычисляем общее количество людей за столом
     */
    public function getTotalGuestsAttribute(): int
    {
        return $this->people->sum('people_count');
    }

    /**
     * Связь с гостями за этим столом
     */
    public function people(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(People::class, 'people_tables', 'table_id', 'person_id')
            ->withPivot('seat_number')
            ->withTimestamps();
    }
    //
}
