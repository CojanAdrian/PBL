<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('visitor', 32)->index();
            $table->string('path', 512);
            $table->string('referrer')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('device', 16);
            $table->string('browser', 32);
            $table->string('os', 32);
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
