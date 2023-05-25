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
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('email');
            $table->string('phone_number')->nullable();
            $table->string('website')->nullable();
            $table->dateTime('founded_year');

            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('social_media_id')->nullable()->constrained('social_media_links')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
