<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Worked answer. Drill 5 (if you finish).
return new class extends Migration
{
    // Schema::table changes a table that is already there. Every row it already holds stays,
    // and gets the default: 'normal'. Without a default, MySQL would have to refuse (or
    // invent a value for) the rows that exist.
    public function up(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->string('priority')->default('normal');
        });
    }

    // The way back takes away only what up() added.
    public function down(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
