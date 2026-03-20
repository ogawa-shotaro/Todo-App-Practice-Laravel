<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // TodoSeeder を実行する
        // シーダーの実行順序はここで管理する
        $this->call([
            TodoSeeder::class,
        ]);
    }
}
