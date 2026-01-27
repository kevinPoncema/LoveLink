<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Landing extends Model
{
    use HasFactory, SoftDeletes;

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
        'is_published',
    ];

    /**
     * Los atributos que deben ser casteados
     */
    protected function casts(): array
    {
        return [
            'anniversary_date' => 'date',
            'is_published' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
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
     * Relación: Una landing tiene múltiples media
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Relación: Una landing puede tener múltiples invitaciones
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    /**
     * Scope para obtener solo landings publicadas
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
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

    /**
     * Verifica si la landing es visible públicamente
     */
    public function isPubliclyVisible(): bool
    {
        return $this->is_published && ! $this->trashed();
    }
}
