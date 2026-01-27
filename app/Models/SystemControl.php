<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemControl extends Model
{
    use HasFactory;

    /**
     * Los timestamps que usa este modelo
     */
    public $timestamps = false;

    protected $dates = ['updated_at'];

    /**
     * Nombre de la tabla
     */
    protected $table = 'system_control';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'max_images_per_landing',
        'max_file_size_mb',
        'allowed_mime',
        'thumbnails_enabled',
        'gif_enabled',
    ];

    /**
     * Los atributos que deben ser casteados
     */
    protected function casts(): array
    {
        return [
            'allowed_mime' => 'array',
            'max_images_per_landing' => 'integer',
            'max_file_size_mb' => 'integer',
            'thumbnails_enabled' => 'boolean',
            'gif_enabled' => 'boolean',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Obtiene la instancia singleton de configuración del sistema
     */
    public static function getInstance(): self
    {
        return self::first() ?? self::create([
            'max_images_per_landing' => 50,
            'max_file_size_mb' => 5,
            'allowed_mime' => ['image/jpeg', 'image/png', 'image/webp'],
            'thumbnails_enabled' => false,
            'gif_enabled' => false,
        ]);
    }

    /**
     * Verifica si un tipo MIME está permitido
     */
    public function isMimeAllowed(string $mimeType): bool
    {
        return in_array($mimeType, $this->allowed_mime);
    }

    /**
     * Obtiene el tamaño máximo de archivo en bytes
     */
    public function getMaxFileSizeInBytes(): int
    {
        return $this->max_file_size_mb * 1024 * 1024;
    }

    /**
     * Verifica si los GIFs están habilitados
     */
    public function areGifsEnabled(): bool
    {
        return $this->gif_enabled;
    }

    /**
     * Verifica si los thumbnails están habilitados
     */
    public function areThumbnailsEnabled(): bool
    {
        return $this->thumbnails_enabled;
    }
}
