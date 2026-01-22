<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Enums\UserRole;
use App\Models\ApprovalRequest;
use App\Models\Event;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApprovalRequest>
 */
class ApprovalRequestFactory extends Factory
{
    protected $model = ApprovalRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'sector_id' => Sector::factory(),
            'type' => fake()->randomElement(RequestType::cases()),
            'status' => RequestStatus::PENDING,
            'requester_id' => User::factory(),
            'guest_name' => fake()->name(),
            'guest_document' => fake()->cpf(false),
            'guest_document_type' => DocumentType::CPF,
            'guest_email' => fake()->optional()->safeEmail(),
            'requester_notes' => fake()->optional()->sentence(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    /**
     * State: Solicitação pendente.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RequestStatus::PENDING,
            'reviewer_id' => null,
            'reviewed_at' => null,
            'reviewer_notes' => null,
        ]);
    }

    /**
     * State: Solicitação aprovada.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RequestStatus::APPROVED,
            'reviewer_id' => User::factory()->state(['role' => UserRole::ADMIN]),
            'reviewed_at' => now(),
            'reviewer_notes' => fake()->optional()->sentence(),
        ]);
    }

    /**
     * State: Solicitação rejeitada.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RequestStatus::REJECTED,
            'reviewer_id' => User::factory()->state(['role' => UserRole::ADMIN]),
            'reviewed_at' => now(),
            'reviewer_notes' => fake()->sentence(),
        ]);
    }

    /**
     * State: Solicitação cancelada.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RequestStatus::CANCELLED,
        ]);
    }

    /**
     * State: Solicitação expirada.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RequestStatus::EXPIRED,
            'expires_at' => now()->subHour(),
        ]);
    }

    /**
     * State: Tipo inclusão de convidado (Promoter).
     */
    public function guestInclusion(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => RequestType::GUEST_INCLUSION,
            'requester_id' => User::factory()->state(['role' => UserRole::PROMOTER]),
        ]);
    }

    /**
     * State: Tipo check-in emergencial (Validator).
     */
    public function emergencyCheckin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => RequestType::EMERGENCY_CHECKIN,
            'requester_id' => User::factory()->state(['role' => UserRole::VALIDATOR]),
        ]);
    }

    /**
     * State: Com expiração configurada.
     */
    public function withExpiration(?\DateTime $expiresAt = null): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $expiresAt ?? now()->addDay(),
        ]);
    }
}
