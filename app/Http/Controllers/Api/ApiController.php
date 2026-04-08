<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\Event;
use App\Models\Guest;
use App\Models\User;
use App\Services\ApprovalRequestService;
use App\Services\GuestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['error' => 'Usuário inativo'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado']);
    }

    public function listEvents(Request $request): JsonResponse
    {
        $events = Event::when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->date, fn ($q) => $q->whereDate('date', $request->date))
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $events]);
    }

    public function createEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'nullable|string',
            'ticket_price' => 'nullable|numeric|min:0',
        ]);

        $event = Event::create($validated);

        return response()->json(['data' => $event, 'message' => 'Evento criado'], 201);
    }

    public function getEvent(int $id): JsonResponse
    {
        $event = Event::with(['sectors', 'guests'])->findOrFail($id);

        return response()->json(['data' => $event]);
    }

    public function updateEvent(Request $request, int $id): JsonResponse
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'start_time' => 'sometimes',
            'end_time' => 'sometimes',
            'location' => 'nullable|string',
            'ticket_price' => 'nullable|numeric|min:0',
            'status' => 'sometimes|string',
        ]);

        $event->update($validated);

        return response()->json(['data' => $event, 'message' => 'Evento atualizado']);
    }

    public function listGuests(Request $request): JsonResponse
    {
        $guests = Guest::query()
            ->when($request->event_id, fn ($q) => $q->where('event_id', $request->event_id))
            ->when($request->sector_id, fn ($q) => $q->where('sector_id', $request->sector_id))
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->with(['event', 'sector', 'promoter'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $guests]);
    }

    public function createGuest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'sector_id' => 'required|exists:sectors,id',
            'name' => 'required|string|max:255',
            'document' => 'required|string|max:50',
            'document_type' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $validated['promoter_id'] = $request->user()->id;

        $guest = Guest::create($validated);

        return response()->json(['data' => $guest, 'message' => 'Convidado criado'], 201);
    }

    public function getGuest(int $id): JsonResponse
    {
        $guest = Guest::with(['event', 'sector', 'promoter', 'validator'])->findOrFail($id);

        return response()->json(['data' => $guest]);
    }

    public function updateGuest(Request $request, int $id): JsonResponse
    {
        $guest = Guest::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'document' => 'sometimes|string|max:50',
            'document_type' => 'nullable|string',
            'email' => 'nullable|email',
            'sector_id' => 'sometimes|exists:sectors,id',
        ]);

        $guest->update($validated);

        return response()->json(['data' => $guest, 'message' => 'Convidado atualizado']);
    }

    public function deleteGuest(int $id): JsonResponse
    {
        $guest = Guest::findOrFail($id);
        $guest->delete();

        return response()->json(['message' => 'Convidado deletado']);
    }

    public function checkinByQr(Request $request): JsonResponse
    {
        $request->validate([
            'qr_token' => 'required|string',
        ]);

        $guestService = new GuestService;
        $result = $guestService->checkinByQrToken($request->qr_token, $request->user());

        if (! $result['success']) {
            return response()->json(['error' => $result['message']], 400);
        }

        return response()->json([
            'data' => $result['guest'],
            'message' => $result['message'],
        ]);
    }

    public function listApprovalRequests(Request $request): JsonResponse
    {
        $requests = ApprovalRequest::query()
            ->when($request->event_id, fn ($q) => $q->where('event_id', $request->event_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->with(['event', 'sector', 'requester', 'reviewer', 'guest'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $requests]);
    }

    public function createApprovalRequest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'sector_id' => 'required|exists:sectors,id',
            'type' => 'required|in:guest_inclusion,emergency_checkin',
            'guest_name' => 'required|string|max:255',
            'guest_document' => 'nullable|string|max:50',
            'guest_email' => 'nullable|email',
            'notes' => 'nullable|string',
        ]);

        $approvalService = app(ApprovalRequestService::class);

        if ($validated['type'] === 'guest_inclusion') {
            $request = $approvalService->createGuestInclusionRequest(
                $request->user(),
                $validated['event_id'],
                $validated['sector_id'],
                [
                    'name' => $validated['guest_name'],
                    'document' => $validated['guest_document'] ?? null,
                    'email' => $validated['guest_email'] ?? null,
                ],
                $validated['notes'] ?? null
            );
        } else {
            $request = $approvalService->createEmergencyCheckinRequest(
                $request->user(),
                $validated['event_id'],
                $validated['sector_id'],
                [
                    'name' => $validated['guest_name'],
                    'document' => $validated['guest_document'] ?? null,
                    'email' => $validated['guest_email'] ?? null,
                ],
                $validated['notes'] ?? null
            );
        }

        return response()->json(['data' => $request, 'message' => 'Solicitação criada'], 201);
    }

    public function getApprovalRequest(int $id): JsonResponse
    {
        $approvalRequest = ApprovalRequest::with(['event', 'sector', 'requester', 'reviewer', 'guest'])
            ->findOrFail($id);

        return response()->json(['data' => $approvalRequest]);
    }

    public function approveRequest(Request $request, int $id): JsonResponse
    {
        $approvalRequest = ApprovalRequest::findOrFail($id);
        $approvalService = app(ApprovalRequestService::class);

        try {
            $result = $approvalService->approve($approvalRequest, $request->user(), $request->notes);

            return response()->json(['data' => $result, 'message' => 'Solicitação aprovada']);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function rejectRequest(Request $request, int $id): JsonResponse
    {
        $request->validate(['notes' => 'required|string']);

        $approvalRequest = ApprovalRequest::findOrFail($id);
        $approvalService = app(ApprovalRequestService::class);

        try {
            $result = $approvalService->reject($approvalRequest, $request->user(), $request->notes);

            return response()->json(['data' => $result, 'message' => 'Solicitação rejeitada']);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getStats(Request $request): JsonResponse
    {
        Log::info('getStats called', ['event_id' => $request->event_id]);

        $stats = [
            'total_events' => Event::count(),
            'active_events' => Event::where('status', 'ongoing')->count(),
            'total_guests' => Guest::count(),
            'checked_in_guests' => Guest::where('is_checked_in', true)->count(),
            'pending_requests' => ApprovalRequest::pending()->count(),
            'approved_requests' => ApprovalRequest::where('status', 'approved')->count(),
            'rejected_requests' => ApprovalRequest::where('status', 'rejected')->count(),
        ];

        if ($request->event_id) {
            $event = Event::findOrFail($request->event_id);
            $stats = [
                'event' => $event->name,
                'total_guests' => $event->guests()->count(),
                'checked_in' => $event->guests()->where('is_checked_in', true)->count(),
                'pending_requests' => $event->approvalRequests()->pending()->count(),
            ];
        }

        return response()->json(['data' => $stats]);
    }
}
