<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    /** Panel kandungan hanya untuk penulis, penyemak dan admin. */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isStaff();
    }

    /** Penulis, penyemak atau admin. */
    public function isStaff(): bool
    {
        return $this->role !== Role::Player;
    }

    public function canWrite(): bool
    {
        return in_array($this->role, [Role::Writer, Role::Admin], true);
    }

    public function canReview(): bool
    {
        return in_array($this->role, [Role::Reviewer, Role::Admin], true);
    }
}
