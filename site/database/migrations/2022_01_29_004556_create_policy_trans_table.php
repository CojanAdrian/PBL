<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePolicyTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policy_trans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('text');
            $table->string('lang');
            $table->foreign('lang')
                ->references('code')->on('langs')
                ->onDelete('cascade');
            $table->unsignedBigInteger('policy_id');
            $table->foreign('policy_id')
                ->references('id')->on('policies')
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
        Schema::dropIfExists('policy_trans');
    }
}
