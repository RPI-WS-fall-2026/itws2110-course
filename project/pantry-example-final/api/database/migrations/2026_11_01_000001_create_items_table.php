<?php

// The core resource, as designed at the midterm (PROJECT.pptx, section A).
// HW7 taught migrations and foreign keys; this is the same thing for items.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            // The owner. nullOnDelete: if the account is deleted the item stays, with no
            // owner, because the food is still in the fridge (midterm decision 3).
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 80);
            $table->decimal('amount', 6, 2);
            $table->string('unit', 10);
            $table->string('location', 10);
            $table->date('best_before')->nullable()->index();   // the shelf is sorted and filtered by it
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['location', 'best_before']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
