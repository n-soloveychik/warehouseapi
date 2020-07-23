<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $u = \App\User::create([
            'name' => 'Rege',
            'email' => 'regemdeu@gmail.com',
            'email_verified_at' => \Carbon\Carbon::now(),
            'password' => \Illuminate\Support\Facades\Hash::make('passwd'),
        ]);
        //$u->createToken('main');
        $u = \App\User::create([
            'name' => 'Генеральный Помощьник Генерального',
            'email' => 'dev@nashdvor-sz.ru',
            'email_verified_at' => \Carbon\Carbon::now(),
            'password' => \Illuminate\Support\Facades\Hash::make('burnd'),
        ]);
        //$u->createToken('main');
    }
}
