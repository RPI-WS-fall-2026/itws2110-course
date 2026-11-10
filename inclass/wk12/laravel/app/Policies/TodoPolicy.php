<?php

namespace App\Policies;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

// DRILL 8: "who may do what" with one to-do, written down once.
//
// Laravel finds this class by its name: the model is Todo, so the policy is TodoPolicy, in
// app/Policies. Nothing calls it yet. Drill 8 has two halves:
//   1. make TodoController::findTodo() ask this policy (Gate::authorize), and
//   2. fill in the three methods below from the table. They are not three copies of one
//      method: update() gives the admin a different answer from view() and delete().
//
// The rule, as a table. Read across a row: one action, three kinds of user.
//
//   action   route                        the owner   the admin    anyone else
//   view     GET    /todos/{id}           yes         yes          no -> 404
//   update   PATCH  /todos/{id}/toggle    yes         no -> 403    no -> 404
//   delete   DELETE /todos/{id}           yes         yes          no -> 404
//
// Why the admin? The admin answers support requests ("I can't find my to-do") so must read
// any to-do, and removes to-dos that break the rules. But it is not the admin's list: marking
// someone's to-do done is the owner's job.
//
// Each method gets the logged-in $user and the $todo being asked about, and returns a
// Response:
//   Response::allow()               go ahead
//   Response::deny('Why not.')      403 Forbidden, with that message
//   Response::denyAsNotFound()      404 Not Found, as if the to-do did not exist
//
// Why two kinds of "no"? The admin can see every to-do on /admin, so telling the admin
// "forbidden" gives nothing away. Priya should not even learn that to-do 2 exists: 404, the
// same answer drill 6 gave.
class TodoPolicy
{
    public function view(User $user, Todo $todo): Response
    {
        // DRILL 8: the "view" row of the table. Right now everyone is allowed.
        return Response::allow();
    }

    public function update(User $user, Todo $todo): Response
    {
        // DRILL 8: the "update" row.
        return Response::allow();
    }

    public function delete(User $user, Todo $todo): Response
    {
        // DRILL 8: the "delete" row.
        return Response::allow();
    }
}
