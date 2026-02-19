<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ebay_marketplaces', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('site_id')->unique();
            $table->string('marketplace_id', 50)->unique();
            $table->string('global_id', 50);
            $table->string('site_code', 10);
            $table->string('name', 100);
            $table->string('currency', 3);
            $table->string('locale', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebay_marketplaces');
    }
};
