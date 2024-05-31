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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default(\App\Enum\Role::STUDENT_GUARDIAN)->after('password');
            $table->string('image')->nullable()->after('role');
            $table->string('phone')->nullable()->after('image');
            $table->string('address')->nullable()->after('phone');
            $table->string('marital_status')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'image', 'phone', 'address', 'marital_status',
            ]);
        });
    }
};
