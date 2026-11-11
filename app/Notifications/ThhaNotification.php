<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * A THHA notification — the shape every event on both sides shares.
 *
 * It is *stored* rather than broadcast: the notification is a line for the
 * navbar bell and the notifications page (the database channel), so what a
 * recipient was told is still there to read tomorrow. An event that genuinely
 * belongs in an inbox as well (a violation notice, a statement of account)
 * adds the mail channel on top — see the classes that do.
 *
 * The line's words are written here, once: the kind of event, the headline and
 * the page it opens. Its icon and colour are the catalogue's business
 * (App\Enums\NotificationType), so the row stores no presentation and a change
 * of styling never rewrites history.
 *
 * A notification is always *about* a record and always sent from the one write
 * path that owns that record (repeated query and write logic lives in a
 * Service), so a listener here never re-reads a model to describe it.
 */
abstract class ThhaNotification extends Notification
{
    use Queueable;

    /**
     * The catalogue entry this event is — what part of the app it belongs to.
     */
    abstract public function type(): NotificationType;

    /**
     * The headline the recipient reads.
     *
     * @param  mixed  $notifiable
     */
    abstract protected function title($notifiable): string;

    /**
     * The line under the headline.
     *
     * @param  mixed  $notifiable
     */
    abstract protected function body($notifiable): string;

    /**
     * The page the notification opens, on the panel that received it.
     *
     * @param  mixed  $notifiable
     */
    abstract protected function url($notifiable): ?string;

    /**
     * The channels this notification goes out on.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * What the row keeps — the kind, the line, and where it opens.
     *
     * @param  mixed  $notifiable
     * @return array<string, string|null>
     */
    public function toArray($notifiable)
    {
        return [
            'type'  => $this->type()->value,
            'title' => $this->title($notifiable),
            'body'  => $this->body($notifiable),
            'url'   => $this->relativeUrl($this->url($notifiable)),
        ];
    }

    /**
     * Keep only the path (and query/fragment) of a link.
     */
    private function relativeUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['host'])) {
            return $url;
        }

        return ($parts['path'] ?? '/')
            . (isset($parts['query']) ? '?' . $parts['query'] : '')
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }
}
