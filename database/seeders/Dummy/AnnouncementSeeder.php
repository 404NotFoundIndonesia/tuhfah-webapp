<?php

namespace Database\Seeders\Dummy;

use App\Enum\AnnouncementScope;
use App\Enum\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role(Role::ADMINISTRATOR)->first();

        if (! $admin) {
            return;
        }

        $rows = [];

        for ($i = 0; $i < 5; $i++) {
            $rows[] = [
                'title' => fake()->sentence(6),
                'body' => fake()->paragraphs(3, true),
                'scope' => AnnouncementScope::PUBLIC->value,
                'published_at' => now()->subDays(rand(1, 30)),
                'author_id' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        for ($i = 0; $i < 5; $i++) {
            $rows[] = [
                'title' => fake()->sentence(6),
                'body' => fake()->paragraphs(3, true),
                'scope' => AnnouncementScope::INTERNAL->value,
                'published_at' => now()->subDays(rand(1, 30)),
                'author_id' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('announcements')->insert($rows);
    }
}
