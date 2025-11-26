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
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('slug', 32)->unique();
            $table->timestamps();
        });

        // Create FULLTEXT index for search by manufacturer name
        DB::statement('ALTER TABLE manufacturers ADD FULLTEXT INDEX manufacturers_search_idx (title)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FULLTEXT index before dropping table
        DB::statement('ALTER TABLE manufacturers DROP INDEX manufacturers_search_idx');
        Schema::dropIfExists('manufacturers');
    }
};
