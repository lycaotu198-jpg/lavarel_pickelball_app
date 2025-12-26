<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('courts', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('location')->nullable();

            $table->decimal('price_per_hour', 10, 2);

            $table->enum('status', ['available', 'maintenance', 'inactive'])
                  ->default('available');

            $table->string('image')->nullable();

            // 📍 ĐỊA CHỈ CHỮ
            $table->string('address')->nullable();

            // 📍 TỌA ĐỘ BẢN ĐỒ
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};
