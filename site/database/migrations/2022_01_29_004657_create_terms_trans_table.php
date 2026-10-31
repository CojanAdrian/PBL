<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTermsTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terms_trans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('text');
            $table->string('lang');
            $table->foreign('lang')
                ->references('code')->on('langs')
                ->onDelete('cascade');
            $table->unsignedBigInteger('term_id');
            $table->foreign('term_id')
                ->references('id')->on('terms')
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
        Schema::dropIfExists('terms_trans');
    }
}
