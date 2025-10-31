<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('coupons')->insert([
            [
                'code' => 'WELCOME10',
                'type' => 'percent', // or 'fixed'
                'value' => 10,
                'usage_limit' => 100,
                'expiry_at' => now()->addDays(30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'FLAT100',
                'type' => 'fixed',
                'value' => 100,
                'usage_limit' => 50,
                'expiry_at' => now()->addDays(15),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SAVE20',
                'type' => 'percent',
                'value' => 20,
                'usage_limit' => 200,
                'expiry_at' => now()->addDays(45),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
