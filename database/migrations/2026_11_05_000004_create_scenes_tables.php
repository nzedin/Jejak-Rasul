<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->unsignedBigInteger('published_revision_id')->nullable();
            $table->timestamps();

            $table->index(['chapter_id', 'position']);
        });

        Schema::create('scene_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->longText('body_ms');
            $table->string('label', 20);              // sahih | riwayat | rekaan
            $table->string('status', 20)->default('draf');
            $table->string('change_note')->nullable(); // "Dikemas kini ikut riwayat Bukhari"
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete(); // penyunting terakhir
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['scene_id', 'version']);
            $table->index('status');
        });

        Schema::table('scenes', function (Blueprint $table) {
            $table->foreign('published_revision_id')
                ->references('id')->on('scene_revisions')->nullOnDelete();
        });

        // Sumber dipautkan pada REVISION, bukan pada babak — supaya sejarah
        // rujukan setiap versi kekal.
        Schema::create('scene_revision_source', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scene_revision_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_id')->constrained()->cascadeOnDelete();
            $table->string('note')->nullable();

            $table->unique(['scene_revision_id', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scene_revision_source');
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropForeign(['published_revision_id']);
        });
        Schema::dropIfExists('scene_revisions');
        Schema::dropIfExists('scenes');
    }
};
