<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Theme extends BaseModel
{
    use HasFactory, SoftDeletes;

    public const DEFAULT_LIGHT_SLUG = 'lemonade';

    public const DEFAULT_DARK_SLUG = 'dim';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'appearance',
        'light_palette',
        'dark_palette',
        'font_family',
        'border_radius',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'light_palette' => 'array',
        'dark_palette' => 'array',
        'border_radius' => 'integer',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
