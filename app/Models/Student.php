<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id_number', 'name', 'nickname', 'birthplace', 'birthdate',
        'gender', 'status', 'admission_date', 'departure_date', 'image',
        'student_guardian_id',
    ];

    protected $appends = [
        'image_url',
    ];

    public function imageUrl(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->image) {
                    return asset('storage/'.$this->image);
                }

                return asset('404_Black.jpg');
            }
        );
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Student $student) {
            if (is_null($student->student_id_number)) {
                $studentIdNumber = $student->gender === 'male' ? 'I' : 'A';
                $studentIdNumber .= now()->toHijri()->isoFormat('YYMM');
                $number = Student::where('student_id_number', 'LIKE', $studentIdNumber.'%')->count() + 1;
                $student->student_id_number = $studentIdNumber.str_pad($number, 3, '0', STR_PAD_LEFT);
            }
        });
        static::deleting(function (Student $student) {
            if ($student->image) {
                Storage::delete("public/$student->image");
            }
        });
    }
}
