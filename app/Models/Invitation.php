<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'user_id',
        'theme_id',
        'slug',
        'title',
        'yes_message',
        'no_messages',
        'is_published',
    ];

    /**
     * Los atributos que deben ser casteados
     */
    protected function casts(): array
    {
        return [
            'no_messages' => 'array',
            'is_published' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Valores por defecto para nuevas instancias
     */
    protected $attributes = [
        'title' => '¿Quieres ser mi San Valentín?',
        'yes_message' => 'Sí',
        'no_messages' => '["No", "Tal vez", "No te arrepentirás", "Piénsalo mejor"]',
        'is_published' => false,
    ];

    /**
     * Relación: Una invitación pertenece a un usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Una invitación pertenece a un tema (opcional)
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * Relación: Una invitación tiene múltiples media via pivot
     */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'invitation_media');
    }

    /**
     * Scope para obtener solo invitaciones publicadas
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Accessor para el slug único en rutas
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Accessor: Obtiene los mensajes de "no" como array
     */
    public function getNoMessagesAttribute($value): array
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return $value ?? [];
    }

    /**
     * Verifica si la invitación es visible públicamente
     */
    public function isPubliclyVisible(): bool
    {
        return $this->is_published && ! $this->trashed();
    }
}
