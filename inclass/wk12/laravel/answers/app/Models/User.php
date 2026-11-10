<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Worked answer. A person who can log in.
//
// DRILL 5 fixed: 'role' is off the allow-list. No array can set it; the database fills in
// 'user', and an admin is made by the people who run the app (see the seeder's forceFill).
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // DRILL 1 fixed: every password is bcrypt-hashed on its way to the database.
            'password' => 'hashed',
        ];
    }

    // One user, many to-dos: $user->todos is that user's list, and $user->todos() is a
    // query that can only ever find that user's rows (drills 6 and 7).
    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
