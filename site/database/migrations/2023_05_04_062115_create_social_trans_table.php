<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_trans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('lang');

            $table->unsignedBigInteger('social_id');

            $table->foreign('social_id')
                ->references('id')->on('socials')
                ->onDelete('cascade');

            $table->foreign('lang')
                ->references('code')->on('langs')
                ->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('social_trans');
    }
}
