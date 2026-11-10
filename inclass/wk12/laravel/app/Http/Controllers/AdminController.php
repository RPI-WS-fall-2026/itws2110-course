<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

// Supplied -- leave it alone. The admin's page: every account and every list. Only an admin
// may open it, which is why drill 5 matters: an account that can choose its own role can
// choose this page.
class AdminController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Only an admin may see every list.');

        return view('admin.index', ['users' => User::with('todos')->orderBy('id')->get()]);
    }
}
