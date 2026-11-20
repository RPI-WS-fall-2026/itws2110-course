<?php

namespace App\Http\Resources;

use App\Support\DueDate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One item as the API sends it: the shape the midterm's fake API already used, so the
 * React pages did not change. `status`, `days_left` and `owner` are worked out on every
 * read and never stored -- with week 3's DueDate class, unchanged.
 */
class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $bestBefore = $this->best_before?->toDateString();
        $today = now()->toDateString();

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'amount' => (float) $this->amount,
            'unit' => $this->unit,
            'location' => $this->location,
            'best_before' => $bestBefore,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => DueDate::status($bestBefore, $today),
            'days_left' => $bestBefore ? DueDate::daysLeft($bestBefore, $today) : null,
            'owner' => $this->user ? ['id' => $this->user->id, 'name' => $this->user->name] : null,
        ];
    }
}
