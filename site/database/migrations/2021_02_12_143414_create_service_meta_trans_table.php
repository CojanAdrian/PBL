<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceMetaTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_meta_trans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text');
            $table->text('keywords');
            $table->string('lang');
            $table->foreign('lang')
                ->references('code')->on('langs')
                ->onDelete('cascade');
            $table->unsignedBigInteger('service_meta_id');
            $table->foreign('service_meta_id')
                ->references('id')->on('service_metas')
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
        Schema::dropIfExists('service_meta_trans');
    }
}
