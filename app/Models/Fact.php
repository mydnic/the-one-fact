<?php

namespace App\Models;

use Database\Factories\FactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fact extends Model
{
    /** @use HasFactory<FactFactory> */
    use HasFactory;

    protected $fillable = [
        'fact_date',
        'title',
        'content',
        'tags',
        'source_url',
        'source_title',
        'metadata',
    ];

    /**
     * Get the most recently dated fact, i.e. the "fact of the day".
     */
    public static function ofTheDay(): ?self
    {
        return static::query()->orderByDesc('fact_date')->first();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fact_date' => 'date',
            'tags' => 'array',
            'metadata' => 'array',
        ];
    }
}
