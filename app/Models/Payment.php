<?php

namespace App\Models;

use App\Enum\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'period',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'due_date' => 'date:Y-m-d',
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
