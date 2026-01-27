<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'file_path',
        'type',
        'file_size',
    ];

    /**
     * Los atributos que deben ser casteados
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Relación: Media pertenece a múltiples landings via pivot
     */
    public function landings(): BelongsToMany
    {
        return $this->belongsToMany(Landing::class, 'landing_media')
            ->withPivot('sort_order')
            ->orderBy('landing_media.sort_order');
    }

    /**
     * Relación: Media pertenece a múltiples invitations via pivot
     */
    public function invitations(): BelongsToMany
    {
        return $this->belongsToMany(Invitation::class, 'invitation_media');
    }

    /**
     * Verifica si el archivo es una imagen válida
     */
    public function isValidMedia(): bool
    {
        return in_array($this->type, ['image', 'gif']);
    }

    /**
     * Obtiene el tamaño del archivo en MB
     */
    public function getFileSizeInMb(): float
    {
        return round($this->file_size / 1024 / 1024, 2);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
