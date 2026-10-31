<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Big Ed's products: a pack of one flavour (beef or chicken) or a box of several.
 * Name, tagline and notes are translated in product_trans.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Stable id used by the website and in saved orders, e.g. "beef-texas-bbq".
            $table->string('key')->unique();
            $table->string('type', 8)->default('pack'); // pack | box
            $table->string('meat', 16)->nullable();     // beef | chicken (packs only)
            $table->unsignedInteger('price');            // a pack of the popular size, or the whole box
            $table->unsignedInteger('grams')->nullable(); // what a box weighs
            $table->unsignedTinyInteger('heat')->default(1);
            $table->string('accent', 7)->default('#e0261f');
            $table->string('band', 7)->default('#b8261c');
            $table->string('band_text', 7)->default('#ffffff');
            $table->boolean('active')->default(true);
            $table->integer('order_column')->nullable();
            $table->timestamps();
        });

        Schema::create('product_trans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->text('notes')->nullable();
            $table->string('lang');
            $table->foreign('lang')->references('code')->on('langs')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_trans');
        Schema::dropIfExists('products');
    }
};
