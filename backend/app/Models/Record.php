<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Record extends Model
{
    protected $fillable = [
        'user_id', 
        'profile_id', 
        'record_number', 
        'status', 
        'observations',
        'notes'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'profile_id' => 'integer',
    ];

    protected $attributes = [
        'status' => 'created',
    ];

    /**
     * Get the user that owns the record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the profile associated with the record.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the documents for the record.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
