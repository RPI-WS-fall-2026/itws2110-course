<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/** GET /api/users -- the RA's list of residents, with how many items each has. */
class UserController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Only the RA can see the list of residents.');

        // withCount: one query with a COUNT, not one query per user (HW7, the N+1).
        return ['data' => User::withCount('items')->orderBy('name')->get()];
    }
}
