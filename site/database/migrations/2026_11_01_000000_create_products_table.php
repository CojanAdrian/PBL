<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Big Ed's shop: meat types (beef, chicken, …), pack types (pack, box, sack, …),
 * the products (a flavour of one meat in one pack type, or a mixed box), and
 * the ingredients listed in the story. Names and texts are translated in the
 * `*_trans` tables, like the rest of the admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meats', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            // Pouch drawing colours for this meat.
            $table->string('pouch_top', 7)->default('#2a2624');
            $table->string('pouch_bottom', 7)->default('#0d0b0a');
            $table->string('pouch_cap', 7)->default('#0a0908');
            $table->boolean('active')->default(true);
            $table->integer('order_column')->nullable();
            $table->timestamps();
        });
        $this->translations('meat_trans', 'meat_id', 'meats', fn (Blueprint $table) => $table->string('name'));

        Schema::create('pack_types', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('style', 16)->default('pouch'); // pouch | box | sack: how it is drawn
            // A mix holds several flavours (like the taster box): no meat, no heat, a fixed price.
            $table->boolean('mix')->default(false);
            $table->boolean('active')->default(true);
            $table->integer('order_column')->nullable();
            $table->timestamps();
        });
        $this->translations('pack_type_trans', 'pack_type_id', 'pack_types', fn (Blueprint $table) => $table->string('name'));

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Stable id used by the website and in saved orders, e.g. "beef-texas-bbq".
            $table->string('key')->unique();
            $table->foreignId('pack_type_id')->constrained('pack_types')->restrictOnDelete();
            $table->foreignId('meat_id')->nullable()->constrained('meats')->restrictOnDelete();
            $table->unsignedInteger('price');
            $table->unsignedInteger('grams');
            $table->unsignedTinyInteger('heat')->default(1);
            $table->string('accent', 7)->default('#e0261f');
            $table->string('band', 7)->default('#b8261c');
            $table->string('band_text', 7)->default('#ffffff');
            $table->boolean('active')->default(true);
            $table->integer('order_column')->nullable();
            $table->timestamps();
        });
        $this->translations('product_trans', 'product_id', 'products', function (Blueprint $table) {
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->text('notes')->nullable();
        });

        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(true);
            $table->integer('order_column')->nullable();
            $table->timestamps();
        });
        $this->translations('ingredient_trans', 'ingredient_id', 'ingredients', fn (Blueprint $table) => $table->string('name'));
    }

    private function translations(string $name, string $foreignKey, string $parent, Closure $fields): void
    {
        Schema::create($name, function (Blueprint $table) use ($foreignKey, $parent, $fields) {
            $table->id();
            $fields($table);
            $table->string('lang');
            $table->foreign('lang')->references('code')->on('langs')->onDelete('cascade');
            $table->foreignId($foreignKey)->constrained($parent)->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['ingredient_trans', 'ingredients', 'product_trans', 'products', 'pack_type_trans', 'pack_types', 'meat_trans', 'meats'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
