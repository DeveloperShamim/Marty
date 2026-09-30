<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Orders performance indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index('created_at', 'orders_created_at_idx');
            $table->index('courier_sent_at', 'orders_courier_sent_at_idx');
            $table->index('courier_returned_at', 'orders_courier_returned_at_idx');
            if (Schema::hasColumn('orders', 'courier_tracking_code')) {
                $table->index('courier_tracking_code', 'orders_courier_tracking_code_idx');
            }
        });

        // 2. Products SKU index
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'sku')) {
                $table->index('sku', 'products_sku_idx');
            }
        });

        // 3. Product SKUs SKU index
        if (Schema::hasTable('product_skus')) {
            Schema::table('product_skus', function (Blueprint $table) {
                if (Schema::hasColumn('product_skus', 'sku')) {
                    $table->index('sku', 'product_skus_sku_idx');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_created_at_idx');
            $table->dropIndex('orders_courier_sent_at_idx');
            $table->dropIndex('orders_courier_returned_at_idx');
            if (Schema::hasColumn('orders', 'courier_tracking_code')) {
                $table->dropIndex('orders_courier_tracking_code_idx');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'sku')) {
                $table->dropIndex('products_sku_idx');
            }
        });

        if (Schema::hasTable('product_skus')) {
            Schema::table('product_skus', function (Blueprint $table) {
                if (Schema::hasColumn('product_skus', 'sku')) {
                    $table->dropIndex('product_skus_sku_idx');
                }
            });
        }
    }
};
