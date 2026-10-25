<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// `role` is not fillable: nobody can make themselves the RA by sending role=admin.
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'email_verified_at', 'created_at', 'updated_at'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** The items this resident put in the kitchen (HW7: hasMany). */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
