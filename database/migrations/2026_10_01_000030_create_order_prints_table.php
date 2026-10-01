<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** History of invoice / parcel label prints, used to warn before an order is printed (and packed) twice. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_prints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);   // invoice | label
            $table->string('format', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['order_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_prints');
    }
};
