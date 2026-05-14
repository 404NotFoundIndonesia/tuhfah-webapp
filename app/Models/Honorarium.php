<?php

namespace App\Models;

use App\Enum\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Honorarium extends Model
{
    use HasFactory;

    protected $table = 'honorariums';

    protected $fillable = [
        'teacher_id',
        'period',
        'amount',
        'status',
        'paid_at',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
