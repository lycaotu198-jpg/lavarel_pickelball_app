<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop foreign key constraint
        DB::statement('ALTER TABLE payments DROP FOREIGN KEY payments_booking_id_foreign');

        // Modify column to be nullable
        DB::statement('ALTER TABLE payments MODIFY booking_id BIGINT UNSIGNED NULL');

        // Re-add foreign key constraint
        DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropColumn('booking_id');
            $table->foreignId('booking_id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }
};
