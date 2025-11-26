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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 16)->nullable()->index();
            $table->string('title');
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('manufacturer_id')->constrained('manufacturers')->restrictOnDelete();
            $table->string('slug', 32)->unique();
            $table->decimal('price', 10, 2);
            $table->text('description');            
            $table->timestamps();

            $table->index('category_id');
            $table->index('manufacturer_id');
            $table->index('price');
        });

        // Create separate FULLTEXT indexes for priority-based search
        DB::statement('ALTER TABLE products ADD FULLTEXT INDEX products_title_idx (title)');
        DB::statement('ALTER TABLE products ADD FULLTEXT INDEX products_description_idx (description)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FULLTEXT indexes before dropping table
        DB::statement('ALTER TABLE products DROP INDEX products_title_idx');
        DB::statement('ALTER TABLE products DROP INDEX products_description_idx');
        Schema::dropIfExists('products');
    }
};
