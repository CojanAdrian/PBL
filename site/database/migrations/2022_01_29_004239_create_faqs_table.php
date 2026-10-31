<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFaqsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->integer('order_column')->nullable();
            $table->boolean('active')->default(1);
            $table->timestamps();
        });

        $faqs = DB::table('faqs')->get();

        foreach($faqs as $index => $faq) {
            DB::table('faqs')
                ->where('id', $faq->id)
                ->update(['order_column' => $index + 1]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('faqs');
    }
}
