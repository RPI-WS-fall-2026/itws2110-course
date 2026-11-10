<?php

namespace App\Policies;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

// Worked answer. Drill 8.
//
//   action    the owner   the admin       anyone else
//   view      yes         yes             404
//   update    yes         no -> 403       404
//   delete    yes         yes             404
//
// The pantry's ItemPolicy uses before() to let the admin do everything. That would be wrong
// here: the admin may not update. So each method says what the admin may do, row by row.
class TodoPolicy
{
    public function view(User $user, Todo $todo): Response
    {
        if ($this->owns($user, $todo) || $user->isAdmin()) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    public function update(User $user, Todo $todo): Response
    {
        if ($this->owns($user, $todo)) {
            return Response::allow();
        }

        // The admin knows this to-do exists (it is on /admin), so 403 hides nothing.
        return $user->isAdmin()
            ? Response::deny('Only the owner may change a to-do.')
            : Response::denyAsNotFound();
    }

    public function delete(User $user, Todo $todo): Response
    {
        return $this->view($user, $todo);   // the same row as view: the owner or the admin
    }

    // One place that says what "owns" means. user_id comes from the database; the logged-in id
    // comes from the session. Neither came from the request, so neither can be forged.
    private function owns(User $user, Todo $todo): bool
    {
        return $todo->user_id === $user->id;
    }
}
