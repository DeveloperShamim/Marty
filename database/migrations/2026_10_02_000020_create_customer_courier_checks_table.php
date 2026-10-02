<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Saved courier delivery-history lookups (BD Courier), one row per phone number, reused for days. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_courier_checks', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique();        // 01XXXXXXXXX
            $table->unsignedInteger('total_parcels')->default(0);
            $table->unsignedInteger('delivered')->default(0);
            $table->unsignedInteger('cancelled')->default(0);
            $table->decimal('success_ratio', 5, 2)->nullable();
            $table->json('couriers')->nullable();         // per-courier figures
            $table->json('reports')->nullable();          // fraud reports by other merchants
            $table->timestamp('checked_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_courier_checks');
    }
};
