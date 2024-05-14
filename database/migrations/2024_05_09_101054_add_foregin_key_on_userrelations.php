<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // /**
    //  * Run the migrations.
    //  */
    // public function up(): void
    // {
    //     Schema::table('userrelations' , function (Blueprint $table){
    //         $table->foreign('user_from')->references('id')->on('users')->onDelete('SET NULL');
    //         $table->foreign('user_to')->references('id')->on('users')->onDelete('SET NULL');
    //     });
    // }

    // /**
    //  * Reverse the migrations.
    //  */
    // public function down(): void
    // {
    //     Schema::table('userrelations', function (Blueprint $table) {
    //         $table->dropForeign('userrelations_user_to_foreign');
    //         $table->dropForeign('userrelations_user_from_foreign');
    //     });
    // }   
};