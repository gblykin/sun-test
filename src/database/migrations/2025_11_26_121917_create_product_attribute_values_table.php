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
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->foreignId('attribute_option_id')->nullable()->constrained('attribute_options')->nullOnDelete();
            $table->text('value_text')->nullable();
            $table->decimal('value_decimal', 10, 2)->nullable();
            
            $table->index(['product_id', 'attribute_id']);
            $table->index(['attribute_id', 'attribute_option_id']);
            $table->index(['attribute_id', 'value_decimal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};
