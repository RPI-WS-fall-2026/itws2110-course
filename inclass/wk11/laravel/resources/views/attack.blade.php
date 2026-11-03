<x-layout title="Totally innocent page">
    <h1>Win a free pantry tote! 🎉</h1>
    <p>This page stands in for <strong>another website</strong>: open it at
       <code>http://127.0.0.1:{{ request()->getPort() }}/attack</code>, not <code>localhost</code>.
       To your browser those are two different sites, and the app you use lives on
       <code>localhost</code>. You did not type anything, but the moment this page loaded it sent a
       POST to the lab app's <code>/todos</code>. Go to
       <a href="http://localhost:{{ request()->getPort() }}/todos">To-dos</a>: there is a new one
       you never added.</p>

    <p class="note">In the starter app this works. After drill 3 it fails: the lab app rejects a
       POST with no valid CSRF token (419 Page Expired), so this other site can no longer act as
       you. (Opened at <code>localhost</code> this page is not another site at all, and Laravel 13
       lets a same-origin page through on the browser's word -- the <code>Sec-Fetch-Site</code>
       header -- so you would see nothing change.)</p>

    {{-- No @csrf here on purpose: an attacker's page cannot read your token, so it cannot add one.
         That is exactly why the token stops this. The action is an absolute localhost URL so the
         request is cross-origin when this page is opened at 127.0.0.1. --}}
    <form id="evil" method="POST" action="http://localhost:{{ request()->getPort() }}/todos">
        <input type="hidden" name="title" value="Call the attacker: 555-0100">
    </form>
    {{-- The auto-submit lives in public/attack.js so it still runs after drill 6's
         Content-Security-Policy (default-src 'self'), which blocks inline scripts. --}}
    <script src="/attack.js"></script>
</x-layout>
