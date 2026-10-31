<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsMetaTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news_meta_trans', function (Blueprint $table) {
            $table->id();


            $table->string('title');
            $table->text('text');
            $table->text('keywords');
            $table->string('lang')->index();

            $table->unsignedBigInteger('news_meta_id');

            $table->foreign('news_meta_id')
                ->references('id')->on('news_metas')
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
        Schema::dropIfExists('news_meta_trans');
    }
}
