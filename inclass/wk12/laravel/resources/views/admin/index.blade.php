{{-- Supplied -- leave it alone. Only an admin reaches this page (AdminController). --}}
<x-layout title="Admin">
    <h1>Every account, every list</h1>
    <p class="note">You are seeing this because your account's <code>role</code> is <code>admin</code>.</p>

    @foreach ($users as $user)
        <h2>{{ $user->name }} <span class="file">{{ $user->email }} · {{ $user->role }}</span></h2>
        <ul class="todos">
            @forelse ($user->todos as $todo)
                <li class="{{ $todo->done ? 'done' : '' }}"><span>{{ $todo->title }}</span>
                    <span class="status">#{{ $todo->id }}</span></li>
            @empty
                <li class="empty">No to-dos.</li>
            @endforelse
        </ul>
    @endforeach
</x-layout>
