<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Filament\Resources\ApprovalRequests\Pages\ListApprovalRequests;
use App\Models\ApprovalRequest;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ApprovalRequestResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $promoter;

    private User $validator;

    private Event $event;

    private Sector $sector;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

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

        $this->event = Event::factory()->active()->create();
        $this->sector = Sector::factory()->create(['event_id' => $this->event->id]);
    }

    public function test_admin_can_access_approval_requests_list(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/approval-requests');

        $response->assertOk();
    }

    public function test_list_shows_pending_requests(): void
    {
        $requests = ApprovalRequest::factory()
            ->pending()
            ->count(3)
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->promoter->id,
            ]);

        $this->actingAs($this->admin);

        Livewire::test(ListApprovalRequests::class)
            ->assertCanSeeTableRecords($requests);
    }

    public function test_admin_can_approve_pending_request(): void
    {
        $request = ApprovalRequest::factory()
            ->pending()
            ->guestInclusion()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->promoter->id,
            ]);

        $this->actingAs($this->admin);

        Livewire::test(ListApprovalRequests::class)
            ->callTableAction('approve', $request)
            ->assertNotified('Solicitação Aprovada');

        $request->refresh();
        $this->assertEquals(RequestStatus::APPROVED, $request->status);
        $this->assertNotNull($request->guest_id);

        // Verifica se o Guest foi criado
        $this->assertDatabaseHas('guests', [
            'name' => $request->guest_name,
            'document' => $request->guest_document,
            'event_id' => $this->event->id,
        ]);
    }

    public function test_admin_can_reject_pending_request(): void
    {
        $request = ApprovalRequest::factory()
            ->pending()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->promoter->id,
            ]);

        $this->actingAs($this->admin);

        Livewire::test(ListApprovalRequests::class)
            ->callTableAction('reject', $request, [
                'reason' => 'Documento inválido',
            ])
            ->assertNotified('Solicitação Rejeitada');

        $request->refresh();
        $this->assertEquals(RequestStatus::REJECTED, $request->status);
        $this->assertEquals('Documento inválido', $request->reviewer_notes);
    }

    public function test_admin_can_reconsider_rejected_request(): void
    {
        $request = ApprovalRequest::factory()
            ->rejected()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->promoter->id,
            ]);

        $this->actingAs($this->admin);

        Livewire::test(ListApprovalRequests::class)
            ->callTableAction('reconsider', $request, [
                'notes' => 'Novos documentos apresentados',
            ])
            ->assertNotified('Solicitação Reconsiderada');

        $request->refresh();
        $this->assertEquals(RequestStatus::PENDING, $request->status);
        $this->assertStringContainsString('Reconsiderado', $request->reviewer_notes);
    }

    public function test_admin_can_revert_approved_request(): void
    {
        // Primeiro aprova a solicitação
        $request = ApprovalRequest::factory()
            ->pending()
            ->guestInclusion()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->promoter->id,
            ]);

        $this->actingAs($this->admin);

        // Aprovar
        Livewire::test(ListApprovalRequests::class)
            ->callTableAction('approve', $request);

        $request->refresh();
        $guestId = $request->guest_id;
        $this->assertNotNull($guestId);

        // Reverter
        Livewire::test(ListApprovalRequests::class)
            ->callTableAction('revert', $request, [
                'reason' => 'Aprovação incorreta',
            ])
            ->assertNotified('Aprovação Revertida');

        $request->refresh();
        $this->assertEquals(RequestStatus::PENDING, $request->status);
        $this->assertNull($request->guest_id);

        // Verifica se o Guest foi excluído
        $this->assertDatabaseMissing('guests', ['id' => $guestId]);
    }

    public function test_cannot_revert_if_guest_already_checked_in(): void
    {
        // Criar uma solicitação aprovada do tipo guest_inclusion
        $request = ApprovalRequest::factory()
            ->pending()
            ->guestInclusion()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->promoter->id,
            ]);

        $this->actingAs($this->admin);

        // Aprovar
        Livewire::test(ListApprovalRequests::class)
            ->callTableAction('approve', $request);

        $request->refresh();

        // Fazer check-in manual do Guest
        $guest = Guest::find($request->guest_id);
        $guest->update([
            'is_checked_in' => true,
            'checked_in_at' => now(),
            'checked_in_by' => $this->validator->id,
        ]);

        // Tentar reverter - o botão não deve estar visível
        $this->assertFalse($request->fresh()->canBeReverted());
    }

    public function test_emergency_checkin_creates_guest_with_checkin(): void
    {
        $request = ApprovalRequest::factory()
            ->pending()
            ->emergencyCheckin()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->validator->id,
            ]);

        $this->actingAs($this->admin);

        Livewire::test(ListApprovalRequests::class)
            ->callTableAction('approve', $request);

        $request->refresh();

        // Verificar se o Guest foi criado com check-in
        $guest = Guest::find($request->guest_id);
        $this->assertNotNull($guest);
        $this->assertTrue($guest->is_checked_in);
        $this->assertNotNull($guest->checked_in_at);
    }

    public function test_bulk_approve_works_for_multiple_requests(): void
    {
        $requests = ApprovalRequest::factory()
            ->pending()
            ->guestInclusion()
            ->count(3)
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->promoter->id,
            ]);

        $this->actingAs($this->admin);

        Livewire::test(ListApprovalRequests::class)
            ->callTableBulkAction('approveSelected', $requests)
            ->assertNotified();

        foreach ($requests as $request) {
            $request->refresh();
            $this->assertEquals(RequestStatus::APPROVED, $request->status);
            $this->assertNotNull($request->guest_id);
        }
    }

    public function test_bulk_reject_works_for_multiple_requests(): void
    {
        $requests = ApprovalRequest::factory()
            ->pending()
            ->count(2)
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'requester_id' => $this->promoter->id,
            ]);

        $this->actingAs($this->admin);

        Livewire::test(ListApprovalRequests::class)
            ->callTableBulkAction('rejectSelected', $requests, [
                'reason' => 'Rejeição em lote',
            ])
            ->assertNotified();

        foreach ($requests as $request) {
            $request->refresh();
            $this->assertEquals(RequestStatus::REJECTED, $request->status);
            $this->assertEquals('Rejeição em lote', $request->reviewer_notes);
        }
    }

    public function test_navigation_badge_shows_pending_count(): void
    {
        ApprovalRequest::factory()
            ->pending()
            ->count(5)
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
            ]);

        ApprovalRequest::factory()
            ->approved()
            ->count(3)
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
            ]);

        $badge = \App\Filament\Resources\ApprovalRequests\ApprovalRequestResource::getNavigationBadge();

        $this->assertEquals('5', $badge);
    }

    public function test_navigation_badge_returns_null_when_no_pending(): void
    {
        ApprovalRequest::factory()
            ->approved()
            ->count(3)
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
            ]);

        $badge = \App\Filament\Resources\ApprovalRequests\ApprovalRequestResource::getNavigationBadge();

        $this->assertNull($badge);
    }

    public function test_filter_by_status_pending(): void
    {
        $pending = ApprovalRequest::factory()
            ->pending()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
            ]);

        $approved = ApprovalRequest::factory()
            ->approved()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
            ]);

        $this->actingAs($this->admin);

        Livewire::test(ListApprovalRequests::class)
            ->filterTable('status', 'pending')
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$approved]);
    }

    public function test_filter_by_type_emergency_checkin(): void
    {
        $emergency = ApprovalRequest::factory()
            ->pending()
            ->emergencyCheckin()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
            ]);

        $guestInclusion = ApprovalRequest::factory()
            ->pending()
            ->guestInclusion()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
            ]);

        $this->actingAs($this->admin);

        Livewire::test(ListApprovalRequests::class)
            ->filterTable('type', 'emergency_checkin')
            ->assertCanSeeTableRecords([$emergency])
            ->assertCanNotSeeTableRecords([$guestInclusion]);
    }

    public function test_search_by_guest_name(): void
    {
        $matchingRequest = ApprovalRequest::factory()
            ->pending()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'guest_name' => 'João da Silva',
            ]);

        $otherRequest = ApprovalRequest::factory()
            ->pending()
            ->create([
                'event_id' => $this->event->id,
                'sector_id' => $this->sector->id,
                'guest_name' => 'Maria Santos',
            ]);

        $this->actingAs($this->admin);

        Livewire::test(ListApprovalRequests::class)
            ->searchTable('João')
            ->assertCanSeeTableRecords([$matchingRequest])
            ->assertCanNotSeeTableRecords([$otherRequest]);
    }

    public function test_promoter_cannot_access_admin_approval_requests(): void
    {
        $this->actingAs($this->promoter);

        $response = $this->get('/admin/approval-requests');

        $response->assertForbidden();
    }

    public function test_validator_cannot_access_admin_approval_requests(): void
    {
        $this->actingAs($this->validator);

        $response = $this->get('/admin/approval-requests');

        $response->assertForbidden();
    }
}
