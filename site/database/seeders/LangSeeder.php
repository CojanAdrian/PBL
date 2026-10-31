<?php

namespace Database\Seeders;

use App\Models\Lang;
use Illuminate\Database\Seeder;

class LangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $langs = [
            ['name' => 'English', 'code' => 'en'],
            ['name' => 'Русский', 'code' => 'ru'],
            ['name' => 'Română', 'code' => 'ro'],
        ];

        foreach ($langs as $lang) {
            Lang::create($lang);
        }
    }
}
