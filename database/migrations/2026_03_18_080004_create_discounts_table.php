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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['long_stay', 'last_minute']);
            $table->unsignedSmallInteger('min_nights')->nullable()->comment('Required nights for long_stay discount');
            $table->unsignedSmallInteger('days_before_checkin')->nullable()->comment('Days before check-in for last_minute discount');
            $table->decimal('percentage', 5, 2)->comment('Discount percentage (0–100)');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
