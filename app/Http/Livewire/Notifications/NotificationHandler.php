<?php

namespace App\Http\Livewire\Notifications;

use Livewire\Component;

/**
 * The app's toast stack.
 *
 * Both panels include it in their layout and neither owns it: a component emits
 * the `notify` event after it saves something, this component shows the message
 * and dismisses it. Being shared is why it is not filed under Admin\ — the
 * homeowner portal needs the same feedback.
 */
class NotificationHandler extends Component
{
    /** @var int Maximum notifications visible at once */
    const MAX_VISIBLE = 4;

    /** @var array Stack of active notifications */
    public $notifications = [];

    protected $listeners = [
        'notify' => 'pushNotification',
        'removeNotification' => 'dismiss',
    ];

    /**
     * Push a new notification onto the stack.
     *
     * @param  string  $type    success|error|warning|info
     * @param  string  $message
     * @return void
     */
    public function pushNotification($type, $message)
    {
        $id = uniqid('notif_');

        // Prepend so newest notifications appear at the top
        array_unshift($this->notifications, [
            'id'      => $id,
            'type'    => $type,
            'message' => $message,
            'title'   => $this->resolveTitle($type),
            'icon'    => $this->resolveIcon($type),
        ]);

        // Enforce max limit — remove the oldest (last) when exceeded
        if (count($this->notifications) > self::MAX_VISIBLE) {
            array_pop($this->notifications);
        }

        // Tell the browser to start the auto-dismiss timer for this notification
        $this->dispatchBrowserEvent('start-notification-timer', ['id' => $id]);
    }

    /**
     * Remove a notification by its unique id.
     *
     * @param  string  $id
     * @return void
     */
    public function dismiss($id)
    {
        $this->notifications = array_values(
            array_filter($this->notifications, fn ($n) => $n['id'] !== $id)
        );
    }

    /**
     * Resolve the title label for a notification type.
     *
     * @param  string  $type
     * @return string
     */
    private function resolveTitle($type): string
    {
        switch ($type) {
            case 'success': return 'Success';
            case 'error':   return 'Error';
            case 'warning': return 'Warning';
            case 'info':    return 'Notice';
            default:        return 'Notification';
        }
    }

    /**
     * Resolve the icon class for a notification type.
     *
     * @param  string  $type
     * @return string
     */
    private function resolveIcon($type): string
    {
        switch ($type) {
            case 'success': return 'fas fa-fw fa-check-circle';
            case 'error':   return 'fas fa-fw fa-exclamation-circle';
            case 'warning': return 'fas fa-fw fa-exclamation-triangle';
            case 'info':    return 'fas fa-fw fa-info-circle';
            default:        return 'fas fa-fw fa-bell';
        }
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.notifications.notification-handler');
    }
}
