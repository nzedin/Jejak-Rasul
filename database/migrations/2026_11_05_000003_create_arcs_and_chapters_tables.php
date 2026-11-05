<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Arka cerita, cth. "Hijrah".
        Schema::create('arcs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // Bab. Tajuk & ringkasan TIDAK disimpan di sini — ia dalam chapter_revisions,
        // supaya setiap perubahan disemak sebelum pemain nampak.
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arc_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('unlock_day_offset')->default(0); // bab dibuka hari ke-n
            $table->unsignedBigInteger('published_revision_id')->nullable();
            $table->timestamps();

            $table->index(['arc_id', 'position']);
        });

        Schema::create('chapter_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('status', 20)->default('draf');
            $table->string('change_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete(); // penyunting terakhir
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['chapter_id', 'version']);
            $table->index('status');
        });

        Schema::table('chapters', function (Blueprint $table) {
            $table->foreign('published_revision_id')
                ->references('id')->on('chapter_revisions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropForeign(['published_revision_id']);
        });
        Schema::dropIfExists('chapter_revisions');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('arcs');
    }
};
