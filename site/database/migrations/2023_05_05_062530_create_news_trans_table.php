<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news_trans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('sub_title');
            $table->text('excerpt');
            $table->text('text');
            $table->string('lang');
            $table->string('slug')->index();

            $table->unsignedBigInteger('news_id');

            $table->foreign('news_id')
                ->references('id')->on('news')
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
        Schema::dropIfExists('news_trans');
    }
}
