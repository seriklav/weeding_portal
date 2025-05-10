<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    protected $fillable = [
        'name',
        'photo_path',
        'is_downloaded',
        'status',
        'people_count'
    ];

    protected $casts = [
        'is_downloaded' => 'boolean',
        'status' => 'string',
        'people_count' => 'integer'
    ];

    /**
     * Связь со столами, за которыми сидит гость
     */
    public function tables(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Table::class, 'people_tables', 'person_id', 'table_id')
            ->withPivot('seat_number')
            ->withTimestamps();
    }

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_DECLINED = 'declined';
}
