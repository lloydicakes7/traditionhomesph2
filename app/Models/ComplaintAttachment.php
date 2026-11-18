<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintAttachment extends Model
{
    protected $fillable = [
        'complaint_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'is_image',
    ];

    protected $casts = [
        'size'     => 'integer',
        'is_image' => 'boolean',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function getSizeLabelAttribute(): string
    {
        if ($this->size >= 1048576) {
            return number_format($this->size / 1048576, 1) . ' MB';
        }

        return number_format($this->size / 1024, 0) . ' KB';
    }
}
