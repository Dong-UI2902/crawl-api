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
        Schema::create('crawler_configs', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->string('author_selector')->nullable();
            $table->string('tags_selector')->nullable();
            $table->string('chapter_list_selector')->nullable();
            $table->string('image_selector')->nullable();
            $table->string('description_selector')->nullable();
            $table->string('status_selector')->nullable();
            $table->string('title_selector')->nullable();
            $table->timestamps();

            $table->index('domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crawler_configs');
    }
};
