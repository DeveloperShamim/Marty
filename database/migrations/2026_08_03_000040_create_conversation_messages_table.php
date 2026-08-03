<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->enum('sender_type', ['customer', 'admin'])->default('customer')->index();
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->enum('type', ['text', 'product', 'order', 'coupon', 'image', 'voice'])->default('text')->index();
            $table->text('message')->nullable();
            $table->string('attachment_url')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
    }
};
