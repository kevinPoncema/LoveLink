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
        'description',
        'primary_color',
        'secondary_color',
        'bg_color',
        'bg_image_url',
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
     * Relación: Un tema puede ser usado por múltiples landings
     */
    public function landings(): HasMany
    {
        return $this->hasMany(Landing::class);
    }
}
