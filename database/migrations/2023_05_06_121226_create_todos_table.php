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
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->string('header', 500)->nullable();
            $table->string('body', 1000)->nullable();
            $table->integer('user_id')->default(0);
            $table->string('path_to_file', 1000)->nullable();
            $table->string('path_to_file_preview', 1000)->nullable();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('todo_tag', function (Blueprint $table) {
            $table->id();
            $table->integer('todo_id')->default(0);
            $table->integer('tag_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todo');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('todo_tag');
    }
};
