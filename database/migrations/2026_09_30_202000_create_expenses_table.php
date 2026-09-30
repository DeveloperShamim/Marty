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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->default('other'); // marketing, sourcing_travel, packaging, rent_utilities, salaries, bank_fees, other
            $table->decimal('amount', 12, 2); // Amount in BDT
            $table->string('currency', 10)->default('BDT'); // BDT or USD
            $table->decimal('currency_amount', 10, 2)->nullable(); // e.g. 50.00 USD
            $table->decimal('currency_rate', 10, 2)->nullable(); // e.g. 125.00
            $table->string('payment_method')->nullable(); // cash, bkash, nagad, bank, card
            $table->date('expense_date')->index();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('receipt_attachment')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['expense_date', 'category']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
