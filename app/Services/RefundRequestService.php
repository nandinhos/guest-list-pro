<?php

namespace App\Services;

use App\Enums\RefundStatus;
use App\Models\RefundRequest;
use App\Models\TicketSale;
use App\Models\User;
use App\Notifications\NewRefundRequestNotification;
use App\Notifications\RefundRequestStatusNotification;
use Illuminate\Support\Facades\Notification;

class RefundRequestService
{
    public function __construct() {}

    public function createRefundRequest(TicketSale $sale, User $requester, string $reason): RefundRequest
    {
        if ($sale->is_refunded) {
            throw new \InvalidArgumentException('Esta venda já foi estornada.');
        }

        $pendingRequest = RefundRequest::where('ticket_sale_id', $sale->id)
            ->where('status', RefundStatus::PENDING)
            ->first();

        if ($pendingRequest) {
            throw new \InvalidArgumentException('Já existe uma solicitação de estorno pendente para esta venda.');
        }

        $refundRequest = RefundRequest::create([
            'ticket_sale_id' => $sale->id,
            'requester_id' => $requester->id,
            'reason' => $reason,
            'status' => RefundStatus::PENDING,
        ]);

        $this->notifyAdmins($refundRequest);

        return $refundRequest;
    }

    public function approve(RefundRequest $request, User $admin, ?string $notes = null): RefundRequest
    {
        if (! $request->isPending()) {
            throw new \InvalidArgumentException('Esta solicitação já foi revisada.');
        }

        $request->update([
            'status' => RefundStatus::APPROVED,
            'reviewer_id' => $admin->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        $sale = $request->ticketSale;
        $sale->update([
            'is_refunded' => true,
            'refunded_at' => now(),
            'refunded_by' => $admin->id,
            'refund_reason' => $request->reason,
        ]);

        $this->notifyRequester($request);

        return $request;
    }

    public function reject(RefundRequest $request, User $admin, ?string $notes = null): RefundRequest
    {
        if (! $request->isPending()) {
            throw new \InvalidArgumentException('Esta solicitação já foi revisada.');
        }

        $request->update([
            'status' => RefundStatus::REJECTED,
            'reviewer_id' => $admin->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        $this->notifyRequester($request);

        return $request;
    }

    protected function notifyRequester(RefundRequest $request): void
    {
        if ($request->requester) {
            $request->requester->notify(new RefundRequestStatusNotification($request));
        }
    }

    protected function notifyAdmins(RefundRequest $request): void
    {
        $admins = User::where('role', 'admin')->get();

        Notification::send($admins, new NewRefundRequestNotification($request));
    }
}
