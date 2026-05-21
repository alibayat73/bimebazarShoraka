<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Lead extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'budget',
        'source',
        'score',
        'priority',
        'additional_data',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'score' => 'integer',
            'additional_data' => 'array',
        ];
    }
}
