<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Mohammed Jumaa',
            'email' => 'mohammed.n.jumaa@gmail.com',
            'password' => Hash::make('01230123Moh'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}