<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\OrderNumber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::with(['images', 'skus'])->get();
        if ($products->isEmpty()) {
            $this->command?->error('No products found to seed orders.');
            return;
        }

        $insideFee = (float) setting('shipping_inside_dhaka', 70);
        $outsideFee = (float) setting('shipping_outside_dhaka', 130);
        $customerUser = User::where('email', 'customer@vantbd.com')->first();
        $adminUser = User::where('role', 'admin')->first();

        $customerPool = [
            ['Nusrat Jahan', '01711-223344', 'nusrat@gmail.com', 'House 24, Road 7, Dhanmondi', 'Dhaka', 'inside_dhaka'],
            ['Tanvir Ahmed', '01822-334455', 'tanvir@gmail.com', 'Flat 5A, GEC Circle', 'Chattogram', 'outside_dhaka'],
            ['Mim Islam', '01933-445566', 'customer@vantbd.com', 'House 8, Sector 11, Uttara', 'Dhaka', 'inside_dhaka'],
            ['Sakib Hasan', '01644-556677', 'sakib@gmail.com', 'Zindabazar Main Road', 'Sylhet', 'outside_dhaka'],
            ['Farhana Akter', '01755-667788', 'farhana@yahoo.com', 'College Road', 'Rajshahi', 'outside_dhaka'],
            ['Kazi Mahmud', '01866-778899', 'mahmud@gmail.com', 'Shibbari More', 'Khulna', 'outside_dhaka'],
            ['Shuvo Roy', '01977-889900', 'shuvo@gmail.com', 'Chawkbazar', 'Barishal', 'outside_dhaka'],
            ['Tania Sultana', '01588-990011', 'tania@gmail.com', 'CDA Avenue', 'Chattogram', 'outside_dhaka'],
        ];

        // --- 1. Online E-Commerce Orders (Varied statuses from pending to delivered & returned) ---
        $onlineScenarios = [
            // Status, PaymentMethod, PaymentStatus, DaysAgo, ReturnType, CourierName, TrackingCode
            ['delivered',  'bkash',  'verified', 18, null, 'steadfast', 'ST-881201'],
            ['delivered',  'nagad',  'verified', 15, null, 'pathao',    'PT-442190'],
            ['delivered',  'cod',    'verified', 12, null, 'steadfast', 'ST-881345'],
            ['delivered',  'rocket', 'verified', 10, null, 'steadfast', 'ST-881456'],
            ['delivered',  'cod',    'verified',  8, null, 'pathao',    'PT-442301'],
            ['delivered',  'bkash',  'verified',  6, null, 'redx',      'RX-990123'],
            ['shipped',    'cod',    'pending',   3, null, 'steadfast', 'ST-882001'],
            ['shipped',    'bkash',  'verified',  2, null, 'pathao',    'PT-442890'],
            ['shipped',    'cod',    'pending',   2, null, 'steadfast', 'ST-882100'],
            ['processing', 'nagad',  'verified',  1, null, null,        null],
            ['confirmed',  'bkash',  'pending',   1, null, null,        null],
            ['pending',    'cod',    'pending',   0, null, null,        null],
            ['pending',    'bkash',  'pending',   0, null, null,        null],
            ['returned',   'cod',    'rejected',  5, 'paid_delivery', 'steadfast', 'ST-881667'],
        ];

        foreach ($onlineScenarios as $idx => [$status, $method, $payStatus, $daysAgo, $returnType, $courier, $tracking]) {
            $cust = $customerPool[$idx % count($customerPool)];
            [$cName, $cPhone, $cEmail, $cAddr, $cCity, $cZone] = $cust;

            $shippingFee = $cZone === 'inside_dhaka' ? $insideFee : $outsideFee;
            $courierLoss = $returnType === 'unpaid_delivery' ? $shippingFee : 0;
            $orderDate = now()->subDays($daysAgo)->subHours(random_int(1, 8));

            $order = Order::create([
                'user_id'               => $cEmail === 'customer@vantbd.com' ? $customerUser?->id : null,
                'order_number'          => OrderNumber::generate(),
                'order_type'            => 'online',
                'customer_name'         => $cName,
                'customer_phone'        => $cPhone,
                'customer_email'        => $cEmail,
                'shipping_address'      => $cAddr,
                'city'                  => $cCity,
                'postal_code'           => (string) random_int(1000, 9999),
                'shipping_zone'         => $cZone,
                'payment_method'        => $method,
                'payment_status'        => $payStatus,
                'status'                => $status,
                'payment_sender_number' => $method === 'cod' ? null : $cPhone,
                'payment_txn_id'        => $method === 'cod' ? null : strtoupper(Str::random(10)),
                'shipping_charge'       => $shippingFee,
                'courier_name'          => $courier,
                'courier_tracking_code' => $tracking,
                'courier_sent_at'       => $courier ? $orderDate->copy()->addHours(3) : null,
                'courier_returned_at'   => $returnType ? $orderDate->copy()->addDays(3) : null,
                'return_type'           => $returnType,
                'courier_loss_amount'   => $courierLoss,
                'return_reason'         => $returnType === 'paid_delivery' ? 'Doorstep refusal (delivery charge paid)' : null,
                'return_restocked'      => (bool) $returnType,
                'stock_restored'        => (bool) $returnType,
                'created_at'            => $orderDate,
                'updated_at'            => $orderDate,
            ]);

            $subtotal = 0;
            $pickedProducts = $products->random(random_int(1, 2));

            foreach ($pickedProducts as $prod) {
                $qty = random_int(1, 2);
                $sku = $prod->skus->first();
                $unitPrice = $sku ? ($sku->getCalculatedSalePrice() ?: $sku->getCalculatedRegularPrice()) : (float)($prod->sale_price ?: $prod->regular_price);
                $costPrice = $sku ? (float)$sku->getEffectiveCostPrice() : (float)($prod->cost_price ?: round($unitPrice * 0.65, 2));
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;

                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $prod->id,
                    'product_sku_id' => $sku?->id,
                    'product_name'   => $prod->name,
                    'image'          => $prod->primaryImage()?->path,
                    'variant'        => $sku?->attributeLabel() ?: 'Standard',
                    'unit_price'     => $unitPrice,
                    'cost_price'     => $costPrice,
                    'quantity'       => $qty,
                    'line_total'     => $lineTotal,
                    'created_at'     => $orderDate,
                    'updated_at'     => $orderDate,
                ]);
            }

            $order->update([
                'subtotal' => $subtotal,
                'total'    => $subtotal + $shippingFee,
            ]);
        }

        // --- 2. POS Counter Sales (Past 10 days) ---
        $posDays = [9, 7, 5, 3, 1, 0];
        $posPayMethods = ['cash', 'cash', 'bkash', 'card', 'cash', 'cash'];

        foreach ($posDays as $pIdx => $daysAgo) {
            $orderDate = now()->subDays($daysAgo)->subHours(random_int(2, 6));
            $method = $posPayMethods[$pIdx];
            $orderNum = OrderNumber::generate(pos: true);

            $order = Order::create([
                'user_id'            => $adminUser?->id,
                'order_number'       => $orderNum,
                'order_type'         => 'pos',
                'customer_name'      => $pIdx % 2 === 0 ? 'Tanvir Ahmed' : 'Walk-in Customer',
                'customer_phone'     => $pIdx % 2 === 0 ? '01711-223344' : 'N/A',
                'shipping_address'   => 'POS Counter Sale',
                'city'               => 'In-Store',
                'shipping_zone'      => 'inside_dhaka',
                'payment_method'     => $method,
                'payment_status'     => 'verified',
                'status'             => 'delivered',
                'shipping_charge'    => 0,
                'discount_amount'    => $pIdx % 3 === 0 ? 100 : 0,
                'internal_note'      => 'In-store POS counter sale',
                'created_at'         => $orderDate,
                'updated_at'         => $orderDate,
            ]);

            $subtotal = 0;
            $pickedProducts = $products->random(random_int(1, 2));

            foreach ($pickedProducts as $prod) {
                $qty = random_int(1, 2);
                $sku = $prod->skus->first();
                $unitPrice = $sku ? ($sku->getCalculatedSalePrice() ?: $sku->getCalculatedRegularPrice()) : (float)($prod->sale_price ?: $prod->regular_price);
                $costPrice = $sku ? (float)$sku->getEffectiveCostPrice() : (float)($prod->cost_price ?: round($unitPrice * 0.65, 2));
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;

                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $prod->id,
                    'product_sku_id' => $sku?->id,
                    'product_name'   => $prod->name,
                    'image'          => $prod->primaryImage()?->path,
                    'variant'        => $sku?->attributeLabel() ?: 'Standard',
                    'unit_price'     => $unitPrice,
                    'cost_price'     => $costPrice,
                    'quantity'       => $qty,
                    'line_total'     => $lineTotal,
                    'created_at'     => $orderDate,
                    'updated_at'     => $orderDate,
                ]);
            }

            $discount = (float) $order->discount_amount;
            $netTotal = max(0, $subtotal - $discount);
            $cashTendered = $method === 'cash' ? ceil($netTotal / 500) * 500 : $netTotal;
            $changeAmount = max(0, $cashTendered - $netTotal);

            $order->update([
                'subtotal'          => $subtotal,
                'total'             => $netTotal,
                'pos_cash_tendered' => $cashTendered,
                'pos_change_amount' => $changeAmount,
            ]);
        }
    }
}
