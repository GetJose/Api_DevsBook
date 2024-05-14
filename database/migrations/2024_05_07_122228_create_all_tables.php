<?php

use App\Models\Post;
use App\Models\User;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('password');
            $table->string('name');
            $table->date('birth_date');
            $table->string('city')->nullable();
            $table->string('work')->nullable();
            $table->string('avatar')->default('default.jpg');
            $table->string('cover')->default('cover.jpg');
            $table->string('token')->nullable();
        });

        Schema::create('userrelations', function (Blueprint $table) {
            $table->id();
            $table->integer('user_from');
            $table->integer('user_to');
            // $table->foreign('user_to')->references('id')->on('users');
            // $table->foreign('user_to')->references('id')->on('users');
        });
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->references('id')->on('users');
            $table->string('type');
            $table->text('body');
            $table->dateTime('created_at');
        });
        Schema::create('postlikes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Post::class)->references('id')->on('posts');
            $table->foreignIdFor(User::class)->references('id')->on('users');
            $table->dateTime('created_at');
        });
        Schema::create('postcomments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Post::class)->references('id')->on('posts');
            $table->foreignIdFor(User::class)->references('id')->on('users');
            $table->dateTime('created_at');
            $table->text('body');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('userrelations');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('postlikes');
        Schema::dropIfExists('postcomments');
    }
};
