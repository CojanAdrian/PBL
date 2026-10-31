<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_metas', function (Blueprint $table) {
            $table->id();
            $table->string('img')->nullable();
            $table->unsignedBigInteger('page_id')->nullable();
            $table->foreign('page_id')
                ->references('id')->on('pages')
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
        Schema::dropIfExists('page_metas');
    }
}
