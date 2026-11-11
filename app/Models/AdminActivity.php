<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivity extends Model
{
    protected $table = 'admin_activities';

    protected $fillable = [
        'admin_id',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    /**
     * Get the admin who performed this activity.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * The AdminLTE badge theme this activity's action reads in — one place for
     * what "created", "updated" and the rest look like, so no view decides it.
     */
    public function getActionThemeAttribute(): string
    {
        $themes = [
            'created'  => 'success',
            'updated'  => 'primary',
            'deleted'  => 'danger',
            'imported' => 'info',
            'exported' => 'secondary',
            'toggled'  => 'warning',
        ];

        return $themes[$this->action] ?? 'secondary';
    }

    /**
     * The action as it reads on a badge.
     */
    public function getActionLabelAttribute(): string
    {
        return ucfirst((string) $this->action);
    }

    /**
     * Scope to filter activities by module.
     */
    public function scopeModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    /**
     * Scope to filter activities by action.
     */
    public function scopeAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }
}
