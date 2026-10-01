<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The product form used to pre-fill three food-store specification rows, which got
 * saved onto products without anyone typing them. Remove exactly those rows and keep
 * every specification an admin actually wrote.
 */
return new class extends Migration
{
    private const PLACEHOLDERS = [
        ['Purity Standard', '100% Pure & Unadulterated'],
        ['Source / Origin', 'Direct Farm Sourced'],
        ['Shelf Life', '12 Months'],
    ];

    public function up(): void
    {
        DB::table('products')->whereNotNull('specifications')->orderBy('id')->each(function ($product) {
            $rows = json_decode($product->specifications, true);
            if (! is_array($rows)) {
                return;
            }

            $kept = array_values(array_filter($rows, fn ($row) => ! in_array(
                [trim((string) ($row['label'] ?? '')), trim((string) ($row['value'] ?? ''))],
                self::PLACEHOLDERS,
                true
            )));

            if (count($kept) !== count($rows)) {
                DB::table('products')->where('id', $product->id)->update([
                    'specifications' => $kept ? json_encode($kept) : null,
                ]);
            }
        });
    }

    public function down(): void
    {
        // Removed placeholder text is not restored.
    }
};
