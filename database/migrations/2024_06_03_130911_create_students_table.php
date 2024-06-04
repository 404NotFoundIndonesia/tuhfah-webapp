<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id_number')->unique();
            $table->string('name');
            $table->string('nickname')->nullable();
            $table->string('birthplace')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('gender');
            $table->string('status')->default(\App\Enum\StudentStatus::CANDIDATE);
            $table->date('admission_date');
            $table->date('departure_date')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('student_guardian_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
