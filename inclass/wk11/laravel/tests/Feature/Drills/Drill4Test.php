<?php

namespace Tests\Feature\Drills;

use Tests\TestCase;

// Drill 4 -- a secret in code. Supplied; leave it alone. The API key must not be written in
// config/pantry.php (a file git tracks); it must be read from the environment, and the
// variable's name must be recorded in .env.example.
class Drill4Test extends TestCase
{
    public function test_the_key_is_not_hardcoded_in_the_config_file(): void
    {
        $src = file_get_contents(config_path('pantry.php'));

        $this->assertStringNotContainsString(
            'pantry_demo_key_not_real_0000',
            $src,
            'The API key is still written in config/pantry.php. Move it to .env and read it with env().'
        );
        $this->assertMatchesRegularExpression(
            '/env\(\s*[\'"]PANTRY_API_KEY[\'"]/',
            $src,
            "config/pantry.php should read the key with env('PANTRY_API_KEY')."
        );
    }

    public function test_the_variable_name_is_recorded_in_env_example(): void
    {
        $this->assertStringContainsString(
            'PANTRY_API_KEY',
            file_get_contents(base_path('.env.example')),
            'Add PANTRY_API_KEY to .env.example so teammates know the secret exists.'
        );
    }
}
