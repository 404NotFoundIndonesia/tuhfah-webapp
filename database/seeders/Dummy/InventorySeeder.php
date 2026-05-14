<?php

namespace Database\Seeders\Dummy;

use App\Enum\ItemCondition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Al-Quran Mushaf', 'quantity' => 30, 'condition' => ItemCondition::GOOD->value],
            ['name' => 'Papan Tulis', 'quantity' => 5, 'condition' => ItemCondition::GOOD->value],
            ['name' => 'Meja Belajar', 'quantity' => 20, 'condition' => ItemCondition::GOOD->value],
            ['name' => 'Kursi Santri', 'quantity' => 40, 'condition' => ItemCondition::GOOD->value],
            ['name' => 'Loker Santri', 'quantity' => 10, 'condition' => ItemCondition::DAMAGED->value],
            ['name' => 'Buku Iqra Jilid 1', 'quantity' => 15, 'condition' => ItemCondition::GOOD->value],
            ['name' => 'Buku Iqra Jilid 2', 'quantity' => 12, 'condition' => ItemCondition::DAMAGED->value],
            ['name' => 'Spidol Whiteboard', 'quantity' => 8, 'condition' => ItemCondition::GOOD->value],
            ['name' => 'Proyektor', 'quantity' => 2, 'condition' => ItemCondition::GOOD->value],
            ['name' => 'Tikar Sholat', 'quantity' => 0, 'condition' => ItemCondition::LOST->value],
        ];

        $rows = [];
        foreach ($items as $item) {
            $rows[] = [
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'condition' => $item['condition'],
                'acquisition_date' => now()->subMonths(rand(1, 24))->toDateString(),
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('inventories')->insert($rows);
    }
}
