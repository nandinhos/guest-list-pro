<?php

namespace Tests\Unit;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Enums\UserRole;
use App\Models\ApprovalRequest;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use App\Services\ApprovalRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApprovalRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    private ApprovalRequestService $service;

    private User $admin;

    private User $promoter;

    private User $validator;

    private Event $event;

    private Sector $sector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ApprovalRequestService::class);

        // Criar usuários de teste
        $this->admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $this->promoter = User::factory()->create([
            'role' => UserRole::PROMOTER,
            'is_active' => true,
        ]);

        $this->validator = User::factory()->create([
            'role' => UserRole::VALIDATOR,
            'is_active' => true,
        ]);

        // Criar evento e setor de teste
        $this->event = Event::factory()->create();
        $this->sector = Sector::factory()->create(['event_id' => $this->event->id]);

        // Desabilitar notificações durante os testes
        Notification::fake();
    }

    public function test_can_review_returns_true_for_active_admin(): void
    {
        $this->assertTrue($this->service->canReview($this->admin));
    }

    public function test_can_review_returns_false_for_inactive_admin(): void
    {
        $inactiveAdmin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => false,
        ]);

        $this->assertFalse($this->service->canReview($inactiveAdmin));
    }

    public function test_can_review_returns_false_for_non_admin(): void
    {
        $this->assertFalse($this->service->canReview($this->promoter));
        $this->assertFalse($this->service->canReview($this->validator));
    }

    public function test_create_guest_inclusion_request_creates_pending_request(): void
    {
        $guestData = [
            'name' => 'João Silva',
            'document' => '12345678901',
            'document_type' => 'cpf',
            'email' => 'joao@example.com',
        ];

        $request = $this->service->createGuestInclusionRequest(
            $this->promoter,
            $this->event->id,
            $this->sector->id,
            $guestData,
            'Convidado VIP'
        );

        $this->assertInstanceOf(ApprovalRequest::class, $request);
        $this->assertEquals(RequestType::GUEST_INCLUSION, $request->type);
        $this->assertEquals(RequestStatus::PENDING, $request->status);
        $this->assertEquals($this->promoter->id, $request->requester_id);
        $this->assertEquals($guestData['name'], $request->guest_name);
        $this->assertEquals($guestData['document'], $request->guest_document);
    }

    public function test_create_emergency_checkin_request_creates_pending_request(): void
    {
        $guestData = [
            'name' => 'Maria Santos',
            'document' => '98765432100',
            'document_type' => 'cpf',
        ];

        $request = $this->service->createEmergencyCheckinRequest(
            $this->validator,
            $this->event->id,
            $this->sector->id,
            $guestData,
            'Convidado não listado'
        );

        $this->assertInstanceOf(ApprovalRequest::class, $request);
        $this->assertEquals(RequestType::EMERGENCY_CHECKIN, $request->type);
        $this->assertEquals(RequestStatus::PENDING, $request->status);
        $this->assertEquals($this->validator->id, $request->requester_id);
    }

    public function test_approve_creates_guest_and_updates_status(): void
    {
        $request = ApprovalRequest::factory()
            ->pending()
            ->guestInclusion()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->promoter->id,
            ]);

        $approvedRequest = $this->service->approve($request, $this->admin, 'Aprovado');

        $this->assertEquals(RequestStatus::APPROVED, $approvedRequest->status);
        $this->assertEquals($this->admin->id, $approvedRequest->reviewer_id);
        $this->assertNotNull($approvedRequest->reviewed_at);
        $this->assertNotNull($approvedRequest->guest_id);

        // Verificar se o Guest foi criado
        $guest = Guest::find($approvedRequest->guest_id);
        $this->assertNotNull($guest);
        $this->assertEquals($request->guest_name, $guest->name);
        $this->assertFalse($guest->is_checked_in);
    }

    public function test_approve_emergency_checkin_creates_guest_with_checkin(): void
    {
        $request = ApprovalRequest::factory()
            ->pending()
            ->emergencyCheckin()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->validator->id,
            ]);

        $approvedRequest = $this->service->approve($request, $this->admin);

        $guest = Guest::find($approvedRequest->guest_id);
        $this->assertNotNull($guest);
        $this->assertTrue($guest->is_checked_in);
        $this->assertNotNull($guest->checked_in_at);
    }

    public function test_approve_throws_exception_for_non_pending_request(): void
    {
        $request = ApprovalRequest::factory()
            ->rejected()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
            ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Esta solicitação não pode ser aprovada.');

        $this->service->approve($request, $this->admin);
    }

    public function test_approve_throws_exception_for_non_admin(): void
    {
        $request = ApprovalRequest::factory()
            ->pending()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
            ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Você não tem permissão para aprovar solicitações.');

        $this->service->approve($request, $this->promoter);
    }

    public function test_reject_updates_status_with_reason(): void
    {
        $request = ApprovalRequest::factory()
            ->pending()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
            ]);

        $rejectedRequest = $this->service->reject($request, $this->admin, 'Documento inválido');

        $this->assertEquals(RequestStatus::REJECTED, $rejectedRequest->status);
        $this->assertEquals($this->admin->id, $rejectedRequest->reviewer_id);
        $this->assertEquals('Documento inválido', $rejectedRequest->reviewer_notes);
        $this->assertNotNull($rejectedRequest->reviewed_at);
    }

    public function test_cancel_updates_status_for_requester(): void
    {
        $request = ApprovalRequest::factory()
            ->pending()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->promoter->id,
            ]);

        $cancelledRequest = $this->service->cancel($request, $this->promoter);

        $this->assertEquals(RequestStatus::CANCELLED, $cancelledRequest->status);
    }

    public function test_cancel_throws_exception_for_different_user(): void
    {
        $request = ApprovalRequest::factory()
            ->pending()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->promoter->id,
            ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Você não pode cancelar esta solicitação.');

        $this->service->cancel($request, $this->validator);
    }

    public function test_reconsider_returns_rejected_to_pending(): void
    {
        $request = ApprovalRequest::factory()
            ->rejected()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
            ]);

        $reconsideredRequest = $this->service->reconsider($request, $this->admin, 'Novos documentos apresentados');

        $this->assertEquals(RequestStatus::PENDING, $reconsideredRequest->status);
        $this->assertNull($reconsideredRequest->reviewer_id);
        $this->assertNull($reconsideredRequest->reviewed_at);
        $this->assertStringContainsString('Reconsiderado:', $reconsideredRequest->reviewer_notes);
    }

    public function test_reconsider_throws_exception_for_pending_request(): void
    {
        $request = ApprovalRequest::factory()
            ->pending()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
            ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Esta solicitação não pode ser reconsiderada.');

        $this->service->reconsider($request, $this->admin);
    }

    public function test_revert_deletes_guest_and_returns_to_pending(): void
    {
        // Primeiro aprovar para criar o guest
        $request = ApprovalRequest::factory()
            ->pending()
            ->guestInclusion()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->promoter->id,
            ]);

        $approvedRequest = $this->service->approve($request, $this->admin);
        $guestId = $approvedRequest->guest_id;

        // Agora reverter
        $revertedRequest = $this->service->revert($approvedRequest->fresh(), $this->admin, 'Aprovação incorreta');

        $this->assertEquals(RequestStatus::PENDING, $revertedRequest->status);
        $this->assertNull($revertedRequest->guest_id);
        $this->assertNull($revertedRequest->reviewer_id);
        $this->assertStringContainsString('Aprovação revertida:', $revertedRequest->reviewer_notes);

        // Verificar se o guest foi excluído
        $this->assertNull(Guest::find($guestId));
    }

    public function test_revert_throws_exception_for_non_approved_request(): void
    {
        $request = ApprovalRequest::factory()
            ->pending()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
            ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Esta aprovação não pode ser revertida.');

        $this->service->revert($request, $this->admin, 'Teste');
    }

    public function test_get_pending_count_returns_correct_count(): void
    {
        ApprovalRequest::factory()->pending()->count(3)->create([
            'event_id' => $this->event->id,
            'sector_id' => $this->sector->id,
        ]);

        ApprovalRequest::factory()->approved()->count(2)->create([
            'event_id' => $this->event->id,
            'sector_id' => $this->sector->id,
        ]);

        $this->assertEquals(3, $this->service->getPendingCount($this->event->id));
    }

    public function test_check_for_duplicates_detects_existing_guest_by_document(): void
    {
        Guest::factory()->create([
            'event_id' => $this->event->id,
            'sector_id' => $this->sector->id,
            'document' => '12345678901',
        ]);

        $result = $this->service->checkForDuplicates(
            $this->event->id,
            'Outro Nome',
            '12345678901'
        );

        $this->assertNotNull($result);
        $this->assertEquals('document', $result['type']);
        $this->assertEquals('error', $result['level']);
    }

    public function test_check_for_duplicates_returns_null_when_no_duplicates(): void
    {
        $result = $this->service->checkForDuplicates(
            $this->event->id,
            'Nome Único',
            '99999999999'
        );

        $this->assertNull($result);
    }
}
