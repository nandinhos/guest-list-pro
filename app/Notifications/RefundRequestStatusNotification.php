<?php

namespace App\Notifications;

use App\Models\RefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RefundRequestStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public RefundRequest $refundRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isApproved = $this->refundRequest->isApproved();
        $status = $this->refundRequest->status;

        $title = match (true) {
            $isApproved => 'Estorno Aprovado!',
            $status === 'rejected' => 'Estorno Rejeitado',
            default => 'Atualização de Estorno',
        };

        $sale = $this->refundRequest->ticketSale;
        $body = sprintf(
            'Solicitação de estorno para venda #%d foi %s.',
            $sale->id,
            $isApproved ? 'aprovada' : 'rejeitada'
        );

        if ($this->refundRequest->review_notes) {
            $body .= sprintf(' Observação: %s', $this->refundRequest->review_notes);
        }

        return [
            'title' => $title,
            'body' => $body,
            'icon' => 'heroicon-m-arrow-uturn-left',
            'color' => $isApproved ? 'success' : 'danger',
            'format' => 'filament',
            'actions' => [],
        ];
    }
}
