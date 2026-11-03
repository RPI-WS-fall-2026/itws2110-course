<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Supplied -- leave it alone.
class Todo extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'done', 'due', 'notes'];

    protected function casts(): array
    {
        return ['done' => 'boolean', 'due' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
