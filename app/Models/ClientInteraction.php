<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'type',
        'description',
        'interaction_date',
    ];

    protected $casts = [
        'interaction_date' => 'datetime',
    ];

    /**
     * Relaciones
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attachments()
    {
        return $this->hasMany(ClientInteractionAttachment::class, 'client_interaction_id');
    }

    /**
     * Accesores
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'meeting' => 'Reunión',
            'call' => 'Llamada',
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            'other' => 'Otro',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    public function getTypeIconAttribute()
    {
        $icons = [
            'meeting' => 'fas fa-users',
            'call' => 'fas fa-phone',
            'whatsapp' => 'fab fa-whatsapp',
            'email' => 'fas fa-envelope',
            'other' => 'fas fa-comment-dots',
        ];

        return $icons[$this->type] ?? 'fas fa-comment-dots';
    }

    public function getTypeColorAttribute()
    {
        $colors = [
            'meeting' => 'bg-blue-100 text-blue-800',
            'call' => 'bg-green-100 text-green-800',
            'whatsapp' => 'bg-green-100 text-green-800',
            'email' => 'bg-purple-100 text-purple-800',
            'other' => 'bg-gray-100 text-gray-800',
        ];

        return $colors[$this->type] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Scopes
     */
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('interaction_date', '>=', now()->subDays($days));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('interaction_date', 'desc');
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Asegurar eliminación en cascada de archivos adjuntos al eliminar una interacción
        static::deleting(function ($interaction) {
            // Eliminar todos los archivos adjuntos asociados a la interacción
            $interaction->attachments()->delete();
        });
    }
}
