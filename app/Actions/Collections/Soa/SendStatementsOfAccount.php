<?php

namespace App\Actions\Collections\Soa;

use App\Enums\SoaStatus;
use App\Models\Admin;
use App\Models\SoaEmail;
use App\Notifications\StatementOfAccountNotification;
use App\Queries\Collections\Soa\StatementDataQuery;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendStatementsOfAccount
{
    private StatementDataQuery $statementData;

    private NotificationService $notifications;

    private ActivityLogService $activity;

    public function __construct(
        StatementDataQuery $statementData,
        NotificationService $notifications,
        ActivityLogService $activity
    ) {
        $this->statementData = $statementData;
        $this->notifications = $notifications;
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): array
    {
        $result = DB::transaction(function () use ($ids, $by) {
            return $this->send($ids, $by);
        });

        $this->activity->log(
            $by,
            'sent',
            'soa_email',
            'Sent ' . $result['sent'] . ' statement(s) of account' . ($result['failed'] ? ', ' . $result['failed'] . ' failed' : ''),
            ['sent' => $result['sent'], 'failed' => $result['failed']]
        );

        return $result;
    }

    // ===== Helpers =====

    private function send(array $ids, Admin $by): array
    {
        $sent = 0;
        $failed = 0;

        foreach ($this->sendable($ids) as $statement) {
            try {
                $this->deliver($statement);

                $statement->update([
                    'status'           => SoaStatus::SENT,
                    'sent_at'          => now(),
                    'error'            => null,
                    'sent_by_admin_id' => $by->id,
                ]);

                $sent++;
            } catch (Throwable $exception) {
                $statement->update([
                    'status'           => SoaStatus::FAILED,
                    'error'            => $exception->getMessage(),
                    'sent_by_admin_id' => $by->id,
                ]);

                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    private function sendable(array $ids)
    {
        return SoaEmail::query()
            ->with(['user.household.property', 'user.resident'])
            ->whereIn('id', $ids)
            ->get()
            ->filter(function (SoaEmail $statement) {
                return $statement->status_enum && $statement->status_enum->canBeSent();
            })
            ->values();
    }

    private function deliver(SoaEmail $statement): void
    {
        $notification = new StatementOfAccountNotification($statement, $this->statementData->summary($statement));

        Notification::route('mail', $statement->email)->notify($notification);
        $this->notifications->toHomeowner($statement->user, $notification);
    }
}
