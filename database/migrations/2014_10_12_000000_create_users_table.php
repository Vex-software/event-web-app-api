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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('phone_number');
            $table->string('email')->unique();
            
            $table->string('profile_photo_path')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();

            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');
            $table->enum('role', ['admin', 'user', 'club_manager'])->default('user');
            // $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
