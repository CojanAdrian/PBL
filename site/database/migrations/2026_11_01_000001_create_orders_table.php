<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Preorders from the website. Items keep the name and price they had when the
 * order was placed, so later product changes never rewrite old orders.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->string('name');
            $table->string('phone', 32);
            $table->string('email')->nullable();
            $table->string('delivery', 16)->default('delivery');
            $table->string('address', 300)->nullable();
            $table->date('wanted_date')->nullable();
            $table->text('note')->nullable();
            $table->json('items');
            $table->unsignedInteger('total_grams');
            $table->unsignedInteger('total_price');
            $table->string('currency', 8);
            $table->string('locale', 8)->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
