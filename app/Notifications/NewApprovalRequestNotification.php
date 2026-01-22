<?php

namespace App\Notifications;

use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewApprovalRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ApprovalRequest $approvalRequest
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nova Solicitação de Aprovação',
            'body' => sprintf(
                '%s: %s solicita aprovação para %s',
                $this->approvalRequest->type->getLabel(),
                $this->approvalRequest->requester->name,
                $this->approvalRequest->guest_name
            ),
            'icon' => $this->approvalRequest->type->getIcon(),
            'color' => 'warning',
            'format' => 'filament',
            'actions' => [],
        ];
    }
}
