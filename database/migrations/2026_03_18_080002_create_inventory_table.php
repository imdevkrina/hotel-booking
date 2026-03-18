<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('price_1_person',  10, 2)->comment('Nightly rate for 1 adult');
            $table->decimal('price_2_persons', 10, 2)->comment('Nightly rate for 2 adults');
            $table->decimal('price_3_persons', 10, 2)->comment('Nightly rate for 3 adults');
            $table->timestamps();

            $table->unique(['room_type_id', 'date']);
            $table->index(['room_type_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
        // room_types dropped by its own migration
    }
};
