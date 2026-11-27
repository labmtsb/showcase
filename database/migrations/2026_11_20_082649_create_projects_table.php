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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('project_name', 125);
            $table->unique(['user_id', 'project_name']);
            $table->string('slug', 225);
            $table->unique(['user_id', 'slug']);
            $table->unsignedTinyInteger('grade')->default(0);
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('comments')->default(0);

            $table->enum('visibility', ['public', 'private', 'unlisted'])->default('public');
            $table->string('entry_point')->default('index.html');
            $table->uuid('share_token_hash')->unique()->nullable();

            $table->string('path', 225);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
