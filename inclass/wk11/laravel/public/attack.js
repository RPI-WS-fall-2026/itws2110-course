// The "other website" submits its hidden form the moment the page loads (drill 3). A file
// rather than an inline script so that it still runs after drill 6's Content-Security-Policy
// (default-src 'self') is in place -- and so the 419 it gets after drill 3 is still visible.
document.getElementById('evil').submit();
