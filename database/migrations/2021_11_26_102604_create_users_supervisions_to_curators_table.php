<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersSupervisionsToCuratorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_supervisions_to_curators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_supervision_id');
            $table->unsignedBigInteger('curator_id');
            $table->timestamps();

            //Relations
            $table->foreign('user_supervision_id')->references('id')->on('users_supervisions');
            $table->foreign('curator_id')->references('id')->on('users');

            //Indexes
            $table->unique(['user_supervision_id', 'curator_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_supervisions_to_curators');
    }
}
