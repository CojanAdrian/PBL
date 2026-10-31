<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestimonialTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('testimonial_trans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('function');
            $table->text('text');
            $table->string('lang');
            $table->string('slug')->index();

            $table->unsignedBigInteger('testimonial_id');

            $table->foreign('testimonial_id')
                ->references('id')->on('testimonials')
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
        Schema::dropIfExists('testimonial_trans');
    }
}
