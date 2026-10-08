<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSku;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');
        $brand = Brand::firstOrCreate(
            ['slug' => 'vant'],
            ['name' => 'Vant', 'is_featured' => true]
        );

        $productsData = [
            // ================= 1. WALLETS & CARD HOLDERS (3 products) =================
            [
                'name' => 'Vant Classic Bifold Leather Wallet',
                'slug' => 'vant-classic-bifold-wallet',
                'brand' => 'Vant',
                'category' => 'wallets',
                'regular_price' => 1450,
                'sale_price' => 1190,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown', 'Black', 'Tan'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1606503825008-909a67e63c3d?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Slim Leather Card Holder',
                'slug' => 'vant-slim-card-holder',
                'brand' => 'Vant',
                'category' => 'wallets',
                'regular_price' => 750,
                'sale_price' => 650,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown', 'Black'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1606503825008-909a67e63c3d?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Long Zip Leather Wallet',
                'slug' => 'vant-long-zip-wallet',
                'brand' => 'Vant',
                'category' => 'wallets',
                'regular_price' => 1850,
                'sale_price' => 1590,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown', 'Black'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1606503825008-909a67e63c3d?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 2. BELTS (3 products) =================
            [
                'name' => 'Vant Formal Leather Belt (Pin Buckle)',
                'slug' => 'vant-formal-leather-belt',
                'brand' => 'Vant',
                'category' => 'belts',
                'regular_price' => 1650,
                'sale_price' => 1390,
                'unit' => 'Piece',
                'variant_type' => 'Waist Size',
                'options' => ['32', '34', '36', '38', '40'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1624222247344-550fb60583dc?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1666723043169-22e29545675c?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Casual Tan Leather Belt',
                'slug' => 'vant-casual-tan-belt',
                'brand' => 'Vant',
                'category' => 'belts',
                'regular_price' => 1550,
                'sale_price' => 1290,
                'unit' => 'Piece',
                'variant_type' => 'Waist Size',
                'options' => ['32', '34', '36', '38', '40'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1666723043169-22e29545675c?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1624222247344-550fb60583dc?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Reversible Black & Brown Belt',
                'slug' => 'vant-reversible-belt',
                'brand' => 'Vant',
                'category' => 'belts',
                'regular_price' => 1850,
                'sale_price' => 1590,
                'unit' => 'Piece',
                'variant_type' => 'Waist Size',
                'options' => ['32', '34', '36', '38', '40'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1624222247344-550fb60583dc?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1666723043169-22e29545675c?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 3. WATCHES (2 products) =================
            [
                'name' => 'Vant Classic Leather Strap Watch',
                'slug' => 'vant-classic-leather-watch',
                'brand' => 'Vant',
                'category' => 'watches',
                'regular_price' => 3450,
                'sale_price' => 2990,
                'unit' => 'Piece',
                'variant_type' => 'Strap Color',
                'options' => ['Brown', 'Black'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1524592094714-0f0654e20314?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Chronograph Steel Watch',
                'slug' => 'vant-chronograph-steel-watch',
                'brand' => 'Vant',
                'category' => 'watches',
                'regular_price' => 5450,
                'sale_price' => 4790,
                'unit' => 'Piece',
                'variant_type' => 'Dial Color',
                'options' => ['Black', 'White'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1539874754764-5a96559165b0?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1547996160-81dfa63595aa?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 4. SHOES (2 products) =================
            [
                'name' => 'Vant Leather Oxford Shoes',
                'slug' => 'vant-leather-oxford-shoes',
                'brand' => 'Vant',
                'category' => 'shoes',
                'regular_price' => 4250,
                'sale_price' => 3690,
                'unit' => 'Piece',
                'variant_type' => 'Size',
                'options' => ['39', '40', '41', '42', '43', '44'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1449505278894-297fdb3edbc1?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Double Monk Strap Shoes',
                'slug' => 'vant-double-monk-strap',
                'brand' => 'Vant',
                'category' => 'shoes',
                'regular_price' => 4650,
                'sale_price' => 3990,
                'unit' => 'Piece',
                'variant_type' => 'Size',
                'options' => ['39', '40', '41', '42', '43', '44'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1533867617858-e7b97e060509?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 5. LEATHER MOUSE PADS (2 products) =================
            [
                'name' => 'Vant Leather Desk Mat (80 x 40 cm)',
                'slug' => 'vant-leather-desk-mat',
                'brand' => 'Vant',
                'category' => 'mouse-pads',
                'regular_price' => 1650,
                'sale_price' => 1390,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown', 'Black', 'Tan'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1629429407759-01cd3d7cfb38?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1593062096033-9a26b09da705?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Leather Mouse Pad (30 x 25 cm)',
                'slug' => 'vant-leather-mouse-pad',
                'brand' => 'Vant',
                'category' => 'mouse-pads',
                'regular_price' => 650,
                'sale_price' => 550,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown', 'Black'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1616400619175-5beda3a17896?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1629429407759-01cd3d7cfb38?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
        ];

        $ratings = [4.7, 4.8, 4.9, 5.0];

        foreach ($productsData as $pData) {
            $category = $categories[$pData['category']] ?? null;

            if (! $category) {
                continue;
            }

            $product = Product::updateOrCreate(
                ['slug' => $pData['slug']],
                [
                    'category_id'         => $category->id,
                    'brand_id'            => $brand->id,
                    'name'                => $pData['name'],
                    'sku'                 => 'VANT-' . strtoupper(Str::substr(md5($pData['slug']), 0, 6)),
                    'brand'               => $pData['brand'],
                    'short_description'   => "Genuine leather {$pData['name']}: neat stitching, solid finish, made for everyday use.",
                    'description'         => "The {$pData['name']} is part of the Vant Bangladesh leather collection. Every piece is crafted from premium genuine leather and inspected by hand. Order with cash on delivery and fast nationwide shipping across all 64 districts in Bangladesh.",
                    'regular_price'       => $pData['regular_price'],
                    'sale_price'          => $pData['sale_price'],
                    'cost_price'          => round(($pData['sale_price'] ?: $pData['regular_price']) * 0.65, 2),
                    'barcode'             => 'PRD-' . strtoupper(Str::substr(md5($pData['slug']), 0, 8)),
                    'stock_quantity'      => random_int(25, 80),
                    'unit'                => $pData['unit'],
                    'is_published'        => true,
                    'is_featured'         => $pData['is_featured'],
                    'is_new_arrival'      => $pData['is_new'],
                    'is_best_seller'      => $pData['is_best_seller'],
                    'is_flash_sale'       => false,
                    'flash_sale_position' => 0,
                    'flash_sale_progress' => 50,
                    'rating'              => $ratings[array_rand($ratings)],
                    'reviews_count'       => random_int(14, 52),
                    'meta_title'          => "Buy {$pData['name']} Online in Bangladesh — Vant",
                    'meta_description'    => "Order the {$pData['name']} at Vant Bangladesh. Genuine leather, cash on delivery and fast delivery across Bangladesh.",
                ]
            );

            // Add product images
            ProductImage::where('product_id', $product->id)->delete();
            foreach ($pData['images'] as $p => $imgUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $imgUrl,
                    'alt'        => "{$pData['name']} - View " . ($p + 1),
                    'color'      => null,
                    'is_primary' => $p === 0,
                    'position'   => $p,
                ]);
            }

            // Add Product Variants
            ProductVariant::where('product_id', $product->id)->delete();
            $vPos = 0;
            $attributesMap = [
                $pData['variant_type'] => $pData['options'],
            ];

            foreach ($attributesMap as $attType => $attVals) {
                foreach ($attVals as $val) {
                    ProductVariant::create([
                        'product_id'  => $product->id,
                        'type'        => $attType,
                        'value'       => $val,
                        'price_delta' => 0,
                        'stock'       => random_int(15, 35),
                        'position'    => $vPos++,
                    ]);
                }
            }

            // Add Product SKUs matrix
            ProductSku::where('product_id', $product->id)->delete();
            $catPrefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $category->name), 0, 3)) ?: 'PRD';
            $productSkuBase = ! empty($product->sku) ? $product->sku : $catPrefix;
            $baseReg = (float) $product->regular_price;
            $baseSale = (float) $product->sale_price;

            foreach ($pData['options'] as $optionVal) {
                $skuSuffix = preg_replace('/[^A-Za-z0-9]/', '', explode(' ', $optionVal)[0]);
                $skuReg = $baseReg > 0 ? $baseReg : null;
                $skuSale = $baseSale > 0 ? $baseSale : null;

                ProductSku::create([
                    'product_id'       => $product->id,
                    'sku'              => "{$productSkuBase}-{$skuSuffix}",
                    'barcode'          => 'SKU-' . strtoupper(Str::random(8)),
                    'cost_price'       => round(($skuSale ?: $skuReg) * 0.65, 2),
                    'attributes'       => [$pData['variant_type'] => $optionVal],
                    'price_adjustment' => 0,
                    'regular_price'    => $skuReg,
                    'sale_price'       => $skuSale,
                    'stock_quantity'   => random_int(15, 40),
                    'is_active'        => true,
                ]);
            }

            $product->syncTotalStock();
        }

        // Set top 4 products as Flash Sale items
        $flashProducts = Product::orderBy('id')->take(4)->get();
        foreach ($flashProducts as $pos => $fp) {
            $fp->update([
                'is_flash_sale'       => true,
                'flash_sale_position' => $pos,
                'flash_sale_progress' => random_int(60, 90),
            ]);
        }
    }
}
