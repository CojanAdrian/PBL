<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->integer('order_column')->nullable();
            $table->string('img');
            $table->boolean('active')->default(1)->index();
            $table->timestamps();
        });

        $services = DB::table('services')->get();

        foreach($services as $index => $service) {
            DB::table('services')
                ->where('id', $service->id)
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
        Schema::dropIfExists('services');
    }
}
