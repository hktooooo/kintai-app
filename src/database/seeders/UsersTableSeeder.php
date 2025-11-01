<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $params = [
            [
                'name' => '西 伶奈',
                'email' => 'reina.n@coachtech.com',
                'password' => Hash::make('password1'),
            ],
            [
                'name' => '山田 太郎',
                'email' => 'taro.y@coachtech.com',
                'password' => Hash::make('password2'),
            ],
            [
                'name' => '増田 一世',
                'email' => 'issei.m@coachtech.com',
                'password' => Hash::make('password3'),
            ],
            [
                'name' => '山本 敬吉',
                'email' => 'keikichi.y@coachtech.com',
                'password' => Hash::make('password4'),
            ],
            [
                'name' => '秋田 朋美',
                'email' => 'tomomi.a@coachtech.com',
                'password' => Hash::make('password5'),
            ],
            [
                'name' => '中西 教夫',
                'email' => 'norio.n@coachtech.com',
                'password' => Hash::make('password6'),
            ],
        ];

        DB::table('users')->insert($params);
    }
}
