<?php

namespace App\Notifications;

/**
 * A notification only the officers who control its part of the panel receive.
 *
 * Who that is, is the Policy's answer and nobody else's: the event names the
 * same ability the page it opens is gated on, and the service that sends it
 * asks the Gate for every officer in turn (App\Services\Notifications\
 * NotificationService::toOfficers). An officer whose role the page is closed to
 * is therefore never told about it — a notification can never widen what
 * somebody may see.
 */
abstract class OfficerNotification extends ThhaNotification
{
    /**
     * The ability that decides which officers may be told, as the Gate ability
     * and the model it is asked about — the same pair the sidebar's own menu
     * entry carries (`can` / `model`).
     *
     * @return array{0: string, 1: string}
     */
    abstract public function governedBy(): array;
}
