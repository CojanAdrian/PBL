<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageMetaTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_meta_trans', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('text')->nullable();
            $table->text('keywords')->nullable();
            $table->string('lang')->nullable();
            $table->foreign('lang')
                ->references('code')->on('langs')
                ->onDelete('cascade');
            $table->unsignedBigInteger('page_meta_id')->nullable();
            $table->foreign('page_meta_id')
                ->references('id')->on('page_metas')
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
        Schema::dropIfExists('page_meta_trans');
    }
}
