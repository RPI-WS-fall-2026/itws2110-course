<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Supplied -- leave it alone. The todos table: the same five columns as Homework 7,
// plus a user_id so each to-do belongs to someone (it matters in drill 1).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->boolean('done')->default(false);
            $table->date('due')->nullable();
            $table->text('notes')->nullable();   // free text -- the XSS surface in drill 2
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
