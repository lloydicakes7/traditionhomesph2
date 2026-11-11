<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Admin;

/**
 * An officer account appeared — one the office created, or one that registered
 * itself and may be waiting for approval.
 *
 * User management is the Administrator's own surface (AdminPolicy::viewAny), so
 * that is who is told: the ability named here is the page it opens, and nobody
 * else's bell hears about it. The new officer is never a recipient — the event
 * is their own registration — and neither is the officer who created them (see
 * NotificationService::toOfficers).
 */
class OfficerRegisteredNotification extends OfficerNotification
{
    /** @var \App\Models\Admin */
    private $officer;

    public function __construct(Admin $officer)
    {
        $this->officer = $officer;
    }

    public function type(): NotificationType
    {
        return NotificationType::OFFICER();
    }

    public function governedBy(): array
    {
        return ['viewAny', Admin::class];
    }

    protected function title($notifiable): string
    {
        return 'Officer account — ' . $this->officer->name;
    }

    protected function body($notifiable): string
    {
        return ($this->officer->role->name ?? 'No role') . ' · '
            . ($this->officer->is_active ? 'active and may sign in.' : 'waiting for approval.');
    }

    protected function url($notifiable): ?string
    {
        return route('users.index');
    }
}
