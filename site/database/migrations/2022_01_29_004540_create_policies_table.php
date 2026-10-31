<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->integer('order_column')->nullable();
            $table->boolean('active')->default(1);
            $table->timestamps();
        });

        $policies = DB::table('policies')->get();

        foreach($policies as $index => $policy) {
            DB::table('policies')
                ->where('id', $policy->id)
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
        Schema::dropIfExists('policies');
    }
}
