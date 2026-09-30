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
        // 1. Products table: cost_price and barcode
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 12, 2)->default(0)->after('sale_price');
            }
            if (!Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode')->nullable()->index()->after('sku');
            }
        });

        // 2. Product SKUs table: cost_price and barcode
        if (Schema::hasTable('product_skus')) {
            Schema::table('product_skus', function (Blueprint $table) {
                if (!Schema::hasColumn('product_skus', 'cost_price')) {
                    $table->decimal('cost_price', 12, 2)->default(0)->after('sale_price');
                }
                if (!Schema::hasColumn('product_skus', 'barcode')) {
                    $table->string('barcode')->nullable()->index()->after('sku');
                }
            });
        }

        // 3. Orders table: POS, Return, and Courier Scan fields
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type', 20)->default('online')->index()->after('order_number');
            }
            if (!Schema::hasColumn('orders', 'pos_cash_tendered')) {
                $table->decimal('pos_cash_tendered', 12, 2)->nullable()->after('total');
            }
            if (!Schema::hasColumn('orders', 'pos_change_amount')) {
                $table->decimal('pos_change_amount', 12, 2)->nullable()->after('pos_cash_tendered');
            }
            if (!Schema::hasColumn('orders', 'courier_returned_at')) {
                $table->timestamp('courier_returned_at')->nullable()->after('courier_sent_at');
            }
            if (!Schema::hasColumn('orders', 'return_type')) {
                $table->string('return_type', 30)->nullable()->after('courier_returned_at'); // 'paid_delivery' | 'unpaid_delivery'
            }
            if (!Schema::hasColumn('orders', 'courier_loss_amount')) {
                $table->decimal('courier_loss_amount', 12, 2)->default(0)->after('return_type');
            }
            if (!Schema::hasColumn('orders', 'return_reason')) {
                $table->string('return_reason')->nullable()->after('courier_loss_amount');
            }
            if (!Schema::hasColumn('orders', 'return_restocked')) {
                $table->boolean('return_restocked')->default(false)->after('return_reason');
            }
            if (!Schema::hasColumn('orders', 'scanned_by')) {
                $table->foreignId('scanned_by')->nullable()->after('return_restocked')->constrained('users')->nullOnDelete();
            }
        });

        // 4. Order Items table: cost_price snapshot for profit calculation
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'cost_price')) {
                $table->decimal('cost_price', 12, 2)->default(0)->after('unit_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'cost_price')) {
                $table->dropColumn('cost_price');
            }
            if (Schema::hasColumn('products', 'barcode')) {
                $table->dropColumn('barcode');
            }
        });

        if (Schema::hasTable('product_skus')) {
            Schema::table('product_skus', function (Blueprint $table) {
                if (Schema::hasColumn('product_skus', 'cost_price')) {
                    $table->dropColumn('cost_price');
                }
                if (Schema::hasColumn('product_skus', 'barcode')) {
                    $table->dropColumn('barcode');
                }
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            $cols = ['order_type', 'pos_cash_tendered', 'pos_change_amount', 'courier_returned_at', 'return_type', 'courier_loss_amount', 'return_reason', 'return_restocked'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('orders', 'scanned_by')) {
                $table->dropForeign(['scanned_by']);
                $table->dropColumn('scanned_by');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'cost_price')) {
                $table->dropColumn('cost_price');
            }
        });
    }
};
