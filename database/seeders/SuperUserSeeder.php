<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperUserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rfc = 'RAJE020226G97';

        DB::table('users')->updateOrInsert(
            ['rfc' => strtoupper($rfc)],
            [
                'name' => 'Elias Abisai Ramos Jacinto',
                'rfc' => strtoupper($rfc),
                'nue' => null,
                'email' => 'abis71562@gmail.com',
                'email_verified_at' => $now,
                'password' => Hash::make('Abisai1789'),
                'must_change_password' => 0,
                'activo' => 1,
                'remember_token' => Str::random(10),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
