<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    protected $fillable = [
        'name', 
        'description', 
        'requirements', 
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the records for the profile.
     */
    public function records(): HasMany
    {
        return $this->hasMany(Record::class);
    }
}
