<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements \Filament\Models\Contracts\FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use \Spatie\Activitylog\Traits\LogsActivity;

    /**
     * Define quem pode acessar o painel administrativo do Filament.
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->role === \App\Enums\UserRole::ADMIN && $this->is_active;
        }

        if ($panel->getId() === 'promoter') {
            return $this->role === \App\Enums\UserRole::PROMOTER && $this->is_active;
        }

        if ($panel->getId() === 'validator') {
            return $this->role === \App\Enums\UserRole::VALIDATOR && $this->is_active;
        }

        if ($panel->getId() === 'bilheteria') {
            return $this->role === \App\Enums\UserRole::BILHETERIA && $this->is_active;
        }

        return false;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => \App\Enums\UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Permissões vinculadas a este usuário (se for Promoter).
     * Alias para compatibilidade.
     */
    public function permissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PromoterPermission::class);
    }

    /**
     * Atribuicoes de eventos deste usuario.
     */
    public function eventAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EventAssignment::class);
    }

    /**
     * Retorna os eventos atribuidos a este usuario baseado na sua role.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Event>
     */
    public function getAssignedEvents(): \Illuminate\Database\Eloquent\Collection
    {
        return Event::query()
            ->whereHas('assignments', fn ($query) => $query
                ->where('user_id', $this->id)
                ->where('role', $this->role->value)
            )
            ->where('status', \App\Enums\EventStatus::ACTIVE)
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Convidados cadastrados por este usuário (se for Promoter).
     */
    public function guests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Guest::class, 'promoter_id');
    }

    /**
     * Alias para convidados criados.
     */
    public function guestsCreated(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->guests();
    }

    /**
     * Convidados validados por este usuário (se for Validador).
     */
    public function guestsValidated(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Guest::class, 'checked_in_by');
    }

    /**
     * Configure activity log options.
     */
    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(['name', 'email', 'role', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Usuário foi {$eventName}");
    }
}
