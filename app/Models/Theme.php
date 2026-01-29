<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'primary_color',
        'secondary_color',
        'bg_color',
        'bg_image_url',
        'bg_image_media_id',
        'css_class',
    ];

    /**
     * Los atributos que deben ser casteados
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Atributos adicionales para incluir en JSON
     */
    protected $appends = ['is_system'];

    /**
     * Relación: Un tema puede ser usado por múltiples landings
     */
    public function landings(): HasMany
    {
        return $this->hasMany(Landing::class);
    }

    /**
     * Relación: Un tema pertenece a un usuario (null para temas del sistema)
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Un tema puede tener una imagen de fondo
     */
    public function backgroundImage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Media::class, 'bg_image_media_id');
    }

    /**
     * Scope para temas del sistema
     */
    public function scopeSystem($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope para temas del usuario
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Verifica si el tema es del sistema
     */
    public function isSystemTheme(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Accessor para el atributo is_system
     */
    public function getIsSystemAttribute(): bool
    {
        return $this->isSystemTheme();
    }
}
