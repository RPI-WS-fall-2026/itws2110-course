<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemRules;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Support\InsufficientStockException;
use App\Support\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * The five endpoints from the midterm design, plus "use some". Each method is short
 * because the work is elsewhere: the Form Requests validate (422), the policy decides who
 * may (403), route model binding finds the item (404), and ItemResource shapes the JSON.
 */
class ItemController extends Controller
{
    /** GET /api/items?location=fridge&status=due-soon&page=1 -- anyone. */
    public function index(Request $request)
    {
        $request->validate([
            'location' => ['sometimes', 'in:'.implode(',', ItemRules::LOCATIONS)],
            'status' => ['sometimes', 'in:expired,due-soon,fresh,no-date'],
        ], [
            'location.in' => 'The selected location is invalid.',
            'status.in' => 'The selected status is invalid.',
        ]);

        $today = now()->toDateString();
        $soon = now()->addDays(5)->toDateString();   // DueDate's warning window

        $items = Item::query()
            ->with('user')   // one query for every owner, not one per item: the N+1 from HW7
            ->when($request->location, fn ($q, $location) => $q->where('location', $location))
            ->when($request->status, fn ($q, $status) => match ($status) {
                'expired' => $q->where('best_before', '<', $today),
                'due-soon' => $q->whereBetween('best_before', [$today, $soon]),
                'fresh' => $q->where('best_before', '>', $soon),
                'no-date' => $q->whereNull('best_before'),
            })
            // What goes off first comes first; undated items last; then by name.
            ->orderByRaw('best_before IS NULL, best_before ASC')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return ItemResource::collection($items);   // { data, links, meta }
    }

    /** GET /api/items/{item} -- anyone. A missing id is a 404 before this runs. */
    public function show(Item $item)
    {
        return new ItemResource($item->load('user'));
    }

    /** POST /api/items -- logged in. */
    public function store(StoreItemRequest $request)
    {
        // The owner comes from the login. A user_id in the body is never read.
        $item = $request->user()->items()->create($request->validated());

        return (new ItemResource($item->load('user')))->response()->setStatusCode(201);
    }

    /** PUT /api/items/{item} -- the owner or the RA. */
    public function update(UpdateItemRequest $request, Item $item)
    {
        $item->update($request->validated());

        return new ItemResource($item->load('user'));
    }

    /** DELETE /api/items/{item} -- the RA. */
    public function destroy(Request $request, Item $item)
    {
        Gate::authorize('delete', $item);
        $item->delete();

        return response()->noContent();   // 204
    }

    /**
     * POST /api/items/{item}/use -- the owner or the RA. { "quantity": 2 }
     *
     * New since the midterm. The prototype worked out the new amount in the browser and
     * sent it with PUT. Now the server does it, with week 3's Stock class, so the rule
     * "you cannot use more than you have" holds however the request is sent.
     */
    public function use(Request $request, Item $item)
    {
        Gate::authorize('update', $item);
        $data = $request->validate(
            ['quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,2']],
            ['quantity.required' => 'Say how much you used.', 'quantity.gt' => 'Amount must be greater than zero']
        );

        $stock = new Stock((float) $item->amount);
        try {
            $stock->consume((float) $data['quantity']);
        } catch (InsufficientStockException $e) {
            throw ValidationException::withMessages(['quantity' => $e->getMessage()]);
        }
        $item->update(['amount' => $stock->amount()]);

        return new ItemResource($item->load('user'));
    }
}
