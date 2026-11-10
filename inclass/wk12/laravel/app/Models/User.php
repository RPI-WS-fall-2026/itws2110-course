<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// A person who can log in. Two drills live in this file.
//
// DRILL 5: the allow-list below. #[Fillable] names the columns that User::create($array) may
// fill from an array. 'role' is on it, so a request that sends role=admin makes an admin.
// Take 'role' off the list; the database fills it in with its default, 'user'.
#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // DRILL 1: there is no cast on 'password', so the column holds exactly what was
            // typed. Add   'password' => 'hashed',   and Laravel hashes it (bcrypt) every
            // time a password is saved -- the plain text never reaches the database.
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
