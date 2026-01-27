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
        'name',
        'slug',
        'description',
        'primary_color',
        'secondary_color',
        'bg_color',
        'bg_image_url',
        'css_class',
        'is_active',
    ];

    /**
     * Los atributos que deben ser casteados
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relación: Un tema puede ser usado por múltiples landings
     */
    public function landings(): HasMany
    {
        return $this->hasMany(Landing::class);
    }

    /**
     * Scope para obtener solo temas activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessor para el slug único
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
