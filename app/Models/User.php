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
     */
    public function permissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PromoterPermission::class);
    }

    /**
     * Convidados cadastrados por este usuário (se for Promoter).
     */
    public function guestsCreated(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Guest::class, 'promoter_id');
    }

    /**
     * Convidados validados por este usuário (se for Validador).
     */
    public function guestsValidated(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Guest::class, 'checked_in_by');
    }
}
