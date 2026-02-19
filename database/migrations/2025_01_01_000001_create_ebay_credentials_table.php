<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ebay_credentials', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('environment')->index();
            $table->string('ebay_user_id')->index();
            $table->text('refresh_token');
            $table->timestamp('refresh_token_expires_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['ebay_user_id', 'environment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebay_credentials');
    }
};
