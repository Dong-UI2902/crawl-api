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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique(); // đã tạo sẵn index bằng unique
            $table->foreignId('author_id')->nullable()->constrained('authors')->onDelete('set null');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('source_url')->nullable();
            $table->enum('status', ['Đã hoàn thành', 'Đang tiến hành'])->default('Đang tiến hành');
            $table->integer('views')->default(0);
            $table->dateTime('last_synced_at')->default(now());
            $table->timestamps();

            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
