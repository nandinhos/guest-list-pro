<?php

namespace App\Notifications;

use App\Models\RefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRefundRequestNotification extends Notification
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
        $sale = $this->refundRequest->ticketSale;

        return [
            'title' => 'Nova Solicitação de Estorno',
            'body' => sprintf(
                '%s solicitou estorno para venda #%d (R$ %s)',
                $this->refundRequest->requester->name,
                $sale->id,
                number_format($sale->value, 2, ',', '.')
            ),
            'icon' => 'heroicon-m-arrow-uturn-left',
            'color' => 'warning',
            'format' => 'filament',
            'actions' => [],
        ];
    }
}
