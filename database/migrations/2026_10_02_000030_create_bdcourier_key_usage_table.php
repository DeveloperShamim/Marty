<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Searches used per BD Courier API key per day, for the per-key daily limits and failover. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bdcourier_key_usage', function (Blueprint $table) {
            $table->id();
            $table->string('key_id', 40);
            $table->date('day');
            $table->unsignedInteger('used')->default(0);
            $table->string('blocked_reason', 255)->nullable(); // set when BD Courier refused the key today
            $table->timestamps();
            $table->unique(['key_id', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdcourier_key_usage');
    }
};
