<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Published into the app by `php artisan vendor:publish --tag=hub-auth-migrations`.
 * The app keeps its own users table (so `items.user_id` still means something), but each
 * row is a mirror of a hub account, found by `hub_id`. Nobody has a password here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('hub_id', 64)->nullable()->unique()->after('id');
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('hub_id');
        });
    }
};
