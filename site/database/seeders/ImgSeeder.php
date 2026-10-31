<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Image;
use Illuminate\Database\Seeder;

class ImgSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $keys = [
            ['key' => 'key_1', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_2', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_3', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_4', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_5', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_6', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_7', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_8', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_9', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_10', 'img' => 'image', 'page_id' => '1'],


            ['key' => 'key_11', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_12', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_13', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_14', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_15', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_16', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_17', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_18', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_19', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_20', 'img' => 'image', 'page_id' => '1'],


            ['key' => 'key_21', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_22', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_23', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_24', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_25', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_26', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_27', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_28', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_29', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_30', 'img' => 'image', 'page_id' => '1'],


            ['key' => 'key_31', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_32', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_33', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_34', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_35', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_36', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_37', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_38', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_39', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_40', 'img' => 'image', 'page_id' => '1'],


            ['key' => 'key_41', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_42', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_43', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_44', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_45', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_46', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_47', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_48', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_49', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_50', 'img' => 'image', 'page_id' => '1'],


            ['key' => 'key_51', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_52', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_53', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_54', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_55', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_56', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_57', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_58', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_59', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_60', 'img' => 'image', 'page_id' => '1'],


            ['key' => 'key_61', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_62', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_63', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_64', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_65', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_66', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_67', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_68', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_69', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_70', 'img' => 'image', 'page_id' => '1'],


            ['key' => 'key_71', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_72', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_73', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_74', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_75', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_76', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_77', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_78', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_79', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_80', 'img' => 'image', 'page_id' => '1'],


            ['key' => 'key_81', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_82', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_83', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_84', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_85', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_86', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_87', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_88', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_89', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_90', 'img' => 'image', 'page_id' => '1'],


            ['key' => 'key_91', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_92', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_93', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_94', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_95', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_96', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_97', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_98', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_99', 'img' => 'image', 'page_id' => '1'],
            ['key' => 'key_100', 'img' => 'image', 'page_id' => '1'],


        ];

        foreach ($keys as $key) {
            Image::create($key);
        }
    }

}
