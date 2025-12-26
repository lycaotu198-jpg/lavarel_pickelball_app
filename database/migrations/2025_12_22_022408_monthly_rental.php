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
        Schema::create('monthly_rentals', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('court_id')->constrained()->cascadeOnDelete();

        $table->date('start_date');
        $table->date('end_date');

        $table->json('week_days'); // ["mon","wed","fri"]

        $table->time('start_time');
        $table->time('end_time');

        $table->decimal('monthly_price', 10, 2);

        $table->enum('status', ['active', 'expired', 'cancelled'])
            ->default('active');

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
