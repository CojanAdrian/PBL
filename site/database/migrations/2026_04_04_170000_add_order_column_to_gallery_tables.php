<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddOrderColumnToGalleryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_galleries', function (Blueprint $table) {
            $table->integer('order_column')->nullable()->after('service_id');
        });

        Schema::table('news_galleries', function (Blueprint $table) {
            $table->integer('order_column')->nullable()->after('news_id');
        });

        foreach (DB::table('service_galleries')->orderBy('id')->get(['id']) as $index => $row) {
            DB::table('service_galleries')
                ->where('id', $row->id)
                ->update(['order_column' => $index + 1]);
        }

        foreach (DB::table('news_galleries')->orderBy('id')->get(['id']) as $index => $row) {
            DB::table('news_galleries')
                ->where('id', $row->id)
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
        Schema::table('service_galleries', function (Blueprint $table) {
            $table->dropColumn('order_column');
        });

        Schema::table('news_galleries', function (Blueprint $table) {
            $table->dropColumn('order_column');
        });
    }
}
