<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppointmentType extends Model
{
    protected $fillable = [
        'name', 
        'description', 
        'duration_minutes', 
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_minutes' => 'integer',
    ];

    /**
     * Get the appointments for the appointment type.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
