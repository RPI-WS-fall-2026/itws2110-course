<?php

namespace Tests\Feature\Drills;

use Tests\TestCase;

// Drill 5 -- a dependency with a published advisory. Supplied; leave it alone. The app must
// no longer depend on firebase/php-jwt before 6.0 (5.5.1 has a critical advisory). Remove
// it, or update past the advisory.
class Drill5Test extends TestCase
{
    public function test_no_vulnerable_php_jwt_in_the_lockfile(): void
    {
        $lock = json_decode(file_get_contents(base_path('composer.lock')), true);
        $packages = array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []);

        $jwt = null;
        foreach ($packages as $p) {
            if (($p['name'] ?? '') === 'firebase/php-jwt') {
                $jwt = $p;
                break;
            }
        }

        if ($jwt === null) {
            $this->assertTrue(true);   // removed -- the cleanest fix, since it was unused

            return;
        }

        $version = ltrim($jwt['version'], 'v');
        $this->assertTrue(
            version_compare($version, '6.0.0', '>='),
            "composer.lock still pins firebase/php-jwt {$version}, which has a published advisory. ".
            'Run the docker run ... composer:2 composer remove firebase/php-jwt command from drill 5 on your machine, then wait for the rebuild.'
        );
    }
}
