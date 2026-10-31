<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->integer('order_column')->nullable();
            $table->boolean('active')->default(1);
            $table->timestamps();
        });

        $terms = DB::table('terms')->get();

        foreach($terms as $index => $term) {
            DB::table('terms')
                ->where('id', $term->id)
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
        Schema::dropIfExists('terms');
    }
}
