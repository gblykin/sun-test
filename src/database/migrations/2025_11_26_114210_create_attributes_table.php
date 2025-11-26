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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('slug', 32)->unique();
            $table->unsignedTinyInteger('type_id');
            $table->string('unit', 16)->nullable(); // 'kWh', 'W', 'V', 'A', 'Hz', '°C', '°F', '°K'
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
