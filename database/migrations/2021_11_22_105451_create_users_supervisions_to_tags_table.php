<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersSupervisionsToTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_supervisions_to_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_supervision_id');
            $table->unsignedBigInteger('user_supervision_tag_id');
            $table->timestamps();

            //Relations
            $table->foreign('user_supervision_id')->references('id')->on('users_supervisions');
            $table->foreign('user_supervision_tag_id')->references('id')->on('users_supervisions_tags');

            //Indexes
            $table->unique(['user_supervision_id', 'user_supervision_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_supervisions_to_tags');
    }
}
