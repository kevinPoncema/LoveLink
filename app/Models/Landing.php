<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Landing extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'user_id',
        'theme_id',
        'slug',
        'couple_names',
        'anniversary_date',
        'bio_text',
    ];

    /**
     * Los atributos que deben ser casteados
     */
    protected function casts(): array
    {
        return [
            'anniversary_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relación: Una landing pertenece a un usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Una landing pertenece a un tema
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * Relación: Una landing tiene múltiples media via pivot
     */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'landing_media')
            ->withPivot('sort_order')
            ->orderBy('landing_media.sort_order');
    }

    /**
     * Accessor: Calcula años juntos desde la fecha de aniversario
     */
    public function getYearsTogetherAttribute(): int
    {
        return Carbon::parse($this->anniversary_date)->diffInYears(now());
    }

    /**
     * Accessor para el slug único en rutas
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
