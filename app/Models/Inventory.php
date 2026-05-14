<?php

namespace App\Models;

use App\Enum\ItemCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'quantity',
        'condition',
        'acquisition_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'condition' => ItemCondition::class,
            'acquisition_date' => 'date:Y-m-d',
            'quantity' => 'integer',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }
}
