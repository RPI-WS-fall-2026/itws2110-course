<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// user_id is not fillable: the owner comes from the login, never from the request.
#[Fillable(['name', 'amount', 'unit', 'location', 'best_before', 'notes'])]
class Item extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'best_before' => 'date:Y-m-d',
        ];
    }

    /** The resident who put it there (HW7: belongsTo). Null if the account was deleted. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
