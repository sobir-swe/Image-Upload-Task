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
        Schema::create('image_site', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('image_id')->constrained('images');
            $table->foreignId('site_id')->constrained('sites');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_site');
    }
};
