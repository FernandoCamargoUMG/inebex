<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'user_id', 
        'appointment_type_id', 
        'title', 
        'begin', 
        'end', 
        'status', 
        'observations'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'appointment_type_id' => 'integer',
        'begin' => 'datetime',
        'end' => 'datetime',
    ];

    /**
     * Get the user that owns the appointment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the appointment type for the appointment.
     */
    public function appointmentType(): BelongsTo
    {
        return $this->belongsTo(AppointmentType::class);
    }
}
