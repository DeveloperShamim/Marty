<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_token')->nullable()->index();
            $table->string('customer_name')->default('Guest Customer');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open')->index();
            $table->unsignedInteger('unread_admin_count')->default(0);
            $table->unsignedInteger('unread_customer_count')->default(0);
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
