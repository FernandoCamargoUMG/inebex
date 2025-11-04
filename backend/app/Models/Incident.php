<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    protected $fillable = [
        'user_id', 
        'assigned_user_id', 
        'title', 
        'description', 
        'priority',
        'status',
        'due_date',
        'resolved_at',
        'resolution'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'assigned_user_id' => 'integer',
        'due_date' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the user that reported the incident.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user assigned to the incident.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
