<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database:
     * wards -> admin account -> demo stations / bikes / users / rentals / reviews.
     */
    public function run(): void
    {
        $this->call(WardSeeder::class);

        // Admin account required by the exam: username "admin" / password "12345"
        User::updateOrCreate(
            ['email' => 'admin@bikego.local'],
            [
                'name' => config('bikego.admin_username'),
                'phone' => null,
                'password' => config('bikego.admin_password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        $this->call(DemoDataSeeder::class);
    }
}
