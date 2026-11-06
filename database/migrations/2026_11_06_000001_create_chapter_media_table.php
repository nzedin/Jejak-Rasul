<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Gambar / video / pautan yang dibenamkan dalam bab.
        Schema::create('chapter_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            // Dipapar selepas babak ini; null = selepas babak terakhir.
            $table->foreignId('after_scene_id')->nullable()->constrained('scenes')->nullOnDelete();
            $table->string('type', 20);              // gambar | video | pautan
            $table->string('title');
            $table->text('caption')->nullable();
            $table->string('file_path')->nullable(); // muat naik (disk public)
            $table->string('url', 2048)->nullable(); // YouTube/Vimeo atau pautan luar
            $table->string('credit')->nullable();    // kredit / hak cipta
            $table->foreignId('source_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('depiction_confirmed')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->string('status', 20)->default('draf');
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['chapter_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_media');
    }
};
