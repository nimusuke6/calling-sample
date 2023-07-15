<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->createMany([
            ['name' => '武石'],
            ['name' => '平山'],
            ['name' => 'ME'],
            ['name' => '健太郎']
        ]);
    }
}
