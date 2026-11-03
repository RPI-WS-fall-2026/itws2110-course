<?php

// The app talks to a (pretend) notifications service, and needs an API key to do it.
//
// DRILL 4: the key is written right here, in a file that is committed to git. Anyone who
// can read the repository -- a teammate, a fork, the whole internet if the repo is public --
// can read the key. A secret in git is a leaked secret, even after you delete it: it stays
// in the history. Move it to .env (which is NOT committed) and read it with env() here.
//
// The fix is one line in this file plus one line in .env.example. See the drills page.
return [
    // This is a fake, throwaway key -- but treat it as if it were real.
    'api_key' => 'pantry_demo_key_not_real_0000',
];
