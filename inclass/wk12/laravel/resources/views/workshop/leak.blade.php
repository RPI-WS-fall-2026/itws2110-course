{{-- Drill 1's attack page -- supplied, leave it alone. A real app has no such page; this is
     what a copy of the database looks like to whoever has it. --}}
<x-layout title="The leaked backup">
    <p class="kicker">Drill 1 · the attack</p>
    <h1>The users table, leaked</h1>
    <p>A copy of the <code>users</code> table, exactly as it is stored. Imagine it in a backup
        on a stolen laptop, or pulled out through last week's SQL injection.</p>

    <table class="leak">
        <thead><tr><th>id</th><th>name</th><th>email</th><th>role</th><th>password column</th></tr></thead>
        <tbody>
        @foreach ($users as $user)
            <tr><td>{{ $user->id }}</td><td>{{ $user->name }}</td><td>{{ $user->email }}</td>
                <td>{{ $user->role }}</td><td class="pw">{{ $user->password }}</td></tr>
        @endforeach
        </tbody>
    </table>

    <p class="note">After drill 1 (and <code>migrate:fresh --seed</code>), every row starts with
        <code>$2y$12$</code>: bcrypt, 12 rounds. Alex and Priya chose the same password. Do their
        rows match? Why not?</p>
</x-layout>
