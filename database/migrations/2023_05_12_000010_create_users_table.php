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
            $table->unsignedBigInteger('city_id')->nullable();

            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');
            
            $table->unsignedBigInteger('role_id')->default(1);
            

            $table->string('google_id')->nullable();
            $table->string('facebook_id')->nullable();
            $table->string('twitter_id')->nullable();
            $table->string('github_id')->nullable();
            $table->string('linkedin_id')->nullable();
            $table->string('instagram_id')->nullable();


            $table->foreignId('social_media_id')->nullable()->constrained('social_media_links')->onDelete('set null');

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            // $table->rememberToken();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
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
