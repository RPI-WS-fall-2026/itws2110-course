<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Supplied -- leave it alone. user_id is not fillable: the owner always comes from the login
// ($request->user()->todos()->create(...)), never from a form.
class Todo extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'done', 'due', 'notes'];

    protected function casts(): array
    {
        return ['done' => 'boolean', 'due' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
