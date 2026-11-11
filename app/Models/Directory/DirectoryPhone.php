<?php

namespace App\Models\Directory;

use Illuminate\Database\Eloquent\Model;

class DirectoryPhone extends Model
{
    protected $fillable = [
        'directory_entry_id',
        'label',
        'number',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    public function entry()
    {
        return $this->belongsTo(DirectoryEntry::class, 'directory_entry_id');
    }

    // =====================================================================
    // Display
    // =====================================================================

    public function getLabelDisplayAttribute(): ?string
    {
        return $this->label !== null && $this->label !== '' ? $this->label : null;
    }

    public function getDialableAttribute(): string
    {
        return preg_replace('/[^\d+]/', '', (string) $this->number);
    }
}
