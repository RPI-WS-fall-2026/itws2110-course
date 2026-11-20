<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

/**
 * "Who may do what" (PROJECT_v3.pdf, Part 1), in Laravel. Laravel finds this class by its
 * name (Item -> ItemPolicy). List and show are open to anyone, so they need no method here;
 * a guest never reaches these methods, because the routes ask for a login first (401).
 *
 * The midterm prototype had the same table in web/src/rules.js. Now the server decides,
 * and the page only uses its copy to hide buttons.
 */
class ItemPolicy
{
    /** The RA may do anything. Returning null means "no answer here, ask the method". */
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function create(User $user): bool
    {
        return true;   // any logged-in resident
    }

    public function update(User $user, Item $item): bool
    {
        return $item->user_id === $user->id;   // the owner
    }

    public function delete(User $user, Item $item): bool
    {
        return false;   // the RA only (handled in before)
    }
}
