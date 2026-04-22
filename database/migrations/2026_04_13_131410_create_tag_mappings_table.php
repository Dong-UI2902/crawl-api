<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tag_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('name_variant')->unique(); //đã tạo sẵn index bằng unique
            $table->foreignId('tag_id')
                ->constrained('tags') //đã tạo sẵn index bằng constrained
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_mappings');
    }
};
