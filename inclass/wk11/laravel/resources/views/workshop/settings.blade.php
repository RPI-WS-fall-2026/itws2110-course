<x-layout title="Settings">
    <h1>Settings</h1>

    <h2>Notifications API key <span class="tag">drill 4</span></h2>
    <p>The app is using this key right now:</p>
    <pre class="secret">{{ $key ?: '(not set)' }}</pre>
    <p class="note">In the starter app this value comes from <code>config/pantry.php</code>, a file
       in git. After drill 4 it comes from <code>.env</code>, and this page looks the same -- which
       is the point: nothing about the app changes, only where the secret lives.</p>

    <h2>Dependencies <span class="tag">drill 5</span></h2>
    <p>The app ships <code>firebase/php-jwt</code>:
       <strong>{{ $hasJwt ? 'installed' : 'not installed' }}</strong>.
       Run <code>docker compose exec app composer audit</code> to see its advisory.</p>
</x-layout>
