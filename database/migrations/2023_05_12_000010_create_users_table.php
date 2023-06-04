<?php

use App\Models\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
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
            $table->string('phone_number')->nullable();
            $table->string('email')->unique();
            $table->string('unauthorized_email')->nullable();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('otp')->nullable();

            $table->decimal('trust_score', 4, 1)->unsigned()->default(100.0)->nullable(false);

            $table->text('access_token')->nullable();
            $table->timestamp('access_token_expires_at')->nullable();

            $table->unsignedBigInteger('status_id')->default(Status::getUnpublishedStatus());

            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();

            $table->timestamp('phone_number_verifed_at')->nullable();

            $table->string('profile_photo_path')->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();

            $table->string('password');

            $table->unsignedBigInteger('role_id')->default(1);

            $table->string('google_id')->nullable();

            $table->foreignId('social_media_id')->nullable()->constrained('social_media_links')->onDelete('set null');

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            // $table->rememberToken();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');
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
