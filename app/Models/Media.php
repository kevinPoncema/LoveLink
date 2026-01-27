<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory;

    /**
     * Los timestamps que usa este modelo
     */
    public $timestamps = false;

    protected $dates = ['created_at'];

    /**
     * Nombre de la tabla
     */
    protected $table = 'media';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'landing_id',
        'file_path',
        'public_url',
        'type',
        'mime_type',
        'file_size',
        'sort_order',
        'is_active',
    ];

    /**
     * Los atributos que deben ser casteados
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Relación: Un media pertenece a una landing
     */
    public function landing(): BelongsTo
    {
        return $this->belongsTo(Landing::class);
    }

    /**
     * Scope para obtener solo media activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Verifica si el archivo es una imagen válida
     */
    public function isValidImage(): bool
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

        return in_array($this->mime_type, $allowedMimes);
    }

    /**
     * Obtiene el tamaño del archivo en MB
     */
    public function getFileSizeInMb(): float
    {
        return round($this->file_size / 1024 / 1024, 2);
    }
}
