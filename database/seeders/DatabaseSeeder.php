<?php

namespace Database\Seeders;

use App\Enum\Role;
use App\Models\User;
use Database\Seeders\Dummy\AnnouncementSeeder;
use Database\Seeders\Dummy\AttendanceSeeder;
use Database\Seeders\Dummy\HonorariumSeeder;
use Database\Seeders\Dummy\InventorySeeder;
use Database\Seeders\Dummy\LearningProgressSeeder;
use Database\Seeders\Dummy\PaymentSeeder;
use Database\Seeders\Dummy\StudentSeeder;
use Database\Seeders\Dummy\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Tuhfah Owner',
            'email' => 'owner@tuhfah.com',
            'role' => Role::OWNER,
        ]);

        $this->call([
            UserSeeder::class,
            StudentSeeder::class,
            AttendanceSeeder::class,
            LearningProgressSeeder::class,
            PaymentSeeder::class,
            HonorariumSeeder::class,
            AnnouncementSeeder::class,
            InventorySeeder::class,
        ]);
    }
}
