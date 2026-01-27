<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationMedia extends Model
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
    protected $table = 'invitation_media';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'invitation_id',
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
     * Relación: Un medio pertenece a una invitación
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    /**
     * Scope para obtener solo medios activos
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
     * Scope para obtener solo imágenes
     */
    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    /**
     * Scope para obtener solo GIFs
     */
    public function scopeGifs($query)
    {
        return $query->where('type', 'gif');
    }

    /**
     * Verifica si el archivo es un GIF
     */
    public function isGif(): bool
    {
        return $this->type === 'gif';
    }

    /**
     * Verifica si el archivo es una imagen
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Verifica si el tipo MIME es válido según el tipo
     */
    public function isValidMimeType(): bool
    {
        $imageMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $gifMimes = ['image/gif'];

        return match ($this->type) {
            'image' => in_array($this->mime_type, $imageMimes),
            'gif' => in_array($this->mime_type, $gifMimes),
            default => false
        };
    }

    /**
     * Obtiene el tamaño del archivo en MB
     */
    public function getFileSizeInMb(): float
    {
        return round($this->file_size / 1024 / 1024, 2);
    }

    /**
     * Verifica si el GIF excede el límite de 10MB
     */
    public function isGifWithinLimit(): bool
    {
        if (! $this->isGif()) {
            return true;
        }

        return $this->file_size <= (10 * 1024 * 1024); // 10MB en bytes
    }
}
