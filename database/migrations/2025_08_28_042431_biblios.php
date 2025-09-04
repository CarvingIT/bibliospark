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
            Schema::create('biblios', function (Blueprint $table) {
                $table->id(); 
                $table->string('external_id')->unique();
                $table->string('isbn_10', 10)->nullable()->unique();
                $table->string('isbn_13', 13)->nullable()->unique();
                $table->string('issn', 8)->nullable()->unique();
                $table->string('title');
                $table->text('authors')->nullable();
                $table->text('publication')->nullable();
                $table->string('published_date')->nullable();
                $table->string('edition')->nullable();
                $table->text('cover_images')->nullable();
                $table->text('all_details');
                $table->timestamps(); 
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biblios');
    }
};
