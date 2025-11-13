<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an admin user if not exists
        if (!User::where('email', 'admin@recipeapp.test')->exists()) {
            User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@recipeapp.test',
                'role' => 'admin',
                'password' => bcrypt('admin123'),
            ]);
        }
        $this->command->info('Admin user created...');
        $this->command->info('U: admin@recipeapp.test');
        $this->command->info("P: admin123\n");

        // Create 5 regular users
        User::factory(5)->create();
        $this->command->info('5 other regular users also created...');
        $this->command->info("P: password\n");
    }
}
