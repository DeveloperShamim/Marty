<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'order_number'          => $this->order_number,
            'customer_name'         => $this->customer_name,
            'customer_phone'        => $this->customer_phone,
            'customer_email'        => $this->customer_email,
            'shipping_address'      => $this->shipping_address,
            'city'                  => $this->city,
            'shipping_zone'         => $this->shipping_zone,
            'subtotal'              => (float) $this->subtotal,
            'discount_amount'       => (float) $this->discount_amount,
            'shipping_charge'       => (float) $this->shipping_charge,
            'total'                 => (float) $this->total,
            'payment_method'        => $this->payment_method,
            'payment_method_label'  => $this->paymentMethodLabel(),
            'payment_sender_number' => $this->payment_sender_number,
            'payment_txn_id'        => $this->payment_txn_id,
            'payment_status'        => $this->payment_status,
            'status'                => $this->status,
            'internal_note'         => $this->internal_note,
            'fraud_score'           => (int) $this->fraud_score,
            'fraud_risk'            => $this->fraudRiskLevel(),
            'courier' => $this->isDispatchedToCourier() ? [
                'name'          => $this->courier_name,
                'label'         => $this->courierLabel(),
                'tracking_code' => $this->courier_tracking_code,
                'status'        => $this->courier_status,
                'sent_at'       => $this->courier_sent_at?->toIso8601String(),
                'tracking_url'  => $this->courierTrackingUrl(),
            ] : null,
            'items_count' => $this->whenCounted('items'),
            'items'       => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id'           => $item->id,
                'product_name' => $item->product_name,
                'variant'      => $item->variant,
                'image_url'    => $item->imageUrl(),
                'unit_price'   => (float) $item->unit_price,
                'quantity'     => (int) $item->quantity,
                'line_total'   => (float) $item->line_total,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
