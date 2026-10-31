<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('remember_token');
            $table->foreign('role_id')
                ->references('id')->on('roles')
                ->onDelete('set null');
            $table->boolean('active')->after('role_id')->default(1)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_id_foreign');
            $table->dropColumn('role_id');
            $table->dropIndex('users_active_index');
            $table->dropColumn('active');
        });
    }
}
