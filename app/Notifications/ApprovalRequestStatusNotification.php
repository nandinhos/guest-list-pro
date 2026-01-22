<?php

namespace App\Notifications;

use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApprovalRequestStatusNotification extends Notification
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
        $isApproved = $this->approvalRequest->isApproved();
        $status = $this->approvalRequest->status;

        $title = match (true) {
            $isApproved => 'Solicitação Aprovada!',
            $status->value === 'rejected' => 'Solicitação Rejeitada',
            $status->value === 'cancelled' => 'Solicitação Cancelada',
            $status->value === 'expired' => 'Solicitação Expirada',
            default => 'Atualização de Solicitação',
        };

        $body = sprintf(
            'Sua solicitação para %s foi %s.',
            $this->approvalRequest->guest_name,
            $status->getLabel()
        );

        if ($this->approvalRequest->reviewer_notes) {
            $body .= sprintf(' Observação: %s', $this->approvalRequest->reviewer_notes);
        }

        return [
            'title' => $title,
            'body' => $body,
            'icon' => $status->getIcon(),
            'color' => $isApproved ? 'success' : 'danger',
            'format' => 'filament',
            'actions' => [],
        ];
    }
}
