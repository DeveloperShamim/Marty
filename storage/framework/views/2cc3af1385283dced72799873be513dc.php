<?php
  $img = $product->imageUrl();
  $secondaryImg = ($product->images && $product->images->count() > 1) ? image_url($product->images->get(1)?->path, $product->slug) : null;
  $discount = $product->discount_percent;
  $flashCard = $flashCard ?? false;
  $cta = setting('default_cta_text', 'Add to Cart');
  $isOutOfStock = (int) $product->stock_quantity <= 0;
  $variantsGrouped = $product->variants ? $product->variants->groupBy('type')->map(fn($items) => $items->pluck('value')->unique()->values()) : collect();
  if ($variantsGrouped->isEmpty() && $product->skus && $product->skus->isNotEmpty()) {
      $skusGrouped = [];
      foreach ($product->skus as $sku) {
          $attrs = $sku->getAttributesData();
          foreach ($attrs as $type => $val) {
              $skusGrouped[$type][] = $val;
          }
      }
      $variantsGrouped = collect($skusGrouped)->map(fn($vals) => collect($vals)->unique()->values());
  }
  $hasVariants = $variantsGrouped->isNotEmpty();
?>

<article class="fk-card product-card group">
  <?php if($isOutOfStock): ?>
    <span class="absolute top-2.5 right-2.5 z-10 bg-stone-800/90 text-white font-extrabold text-[10px] tracking-wide uppercase px-2.5 py-1 rounded-md shadow-sm">Out of Stock</span>
  <?php elseif($discount > 0): ?>
    <span class="fk-badge z-10"><?php echo e($discount); ?>% OFF</span>
  <?php endif; ?>

  <a href="<?php echo e(route('product.show', $product)); ?>" class="fk-card-media relative overflow-hidden block rounded-none aspect-square bg-stone-100 group/img">
    <img src="<?php echo e($img); ?>" alt="<?php echo e($product->name); ?>" loading="lazy" decoding="async" class="w-full h-full object-cover transition-all duration-500 ease-out group-hover/img:scale-110 <?php echo e($secondaryImg ? 'group-hover/img:opacity-0' : ''); ?> <?php echo e($isOutOfStock ? 'opacity-75 grayscale-[30%]' : ''); ?>" />
    <?php if($secondaryImg): ?>
      <img src="<?php echo e($secondaryImg); ?>" alt="<?php echo e($product->name); ?>" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover transition-all duration-500 ease-out opacity-0 group-hover/img:opacity-100 group-hover/img:scale-110 <?php echo e($isOutOfStock ? 'grayscale-[30%]' : ''); ?>" />
    <?php endif; ?>
  </a>

  <div class="fk-card-body">
    <a href="<?php echo e(route('product.show', $product)); ?>" class="fk-card-title"><?php echo e($product->name); ?></a>

    <div class="fk-card-price product-card-price">
      <?php if($product->on_sale): ?>
        <span class="whitespace-nowrap fk-price"><?php echo e(money($product->price)); ?></span>
        <span class="whitespace-nowrap fk-price-was"><?php echo e(money($product->regular_price)); ?></span>
      <?php else: ?>
        <span class="whitespace-nowrap fk-price"><?php echo e(money($product->price)); ?></span>
      <?php endif; ?>
    </div>

    <?php if($flashCard && $product->flash_sale_progress !== null): ?>
      <?php $progress = max(0, min(100, (int) $product->flash_sale_progress)); ?>
      <div class="sold-track mb-2 relative mx-auto w-full h-2 rounded bg-stone-100 overflow-hidden">
        <div class="sold-fill absolute inset-y-0 left-0 bg-brand-500" style="width:<?php echo e($progress); ?>%"></div>
      </div>
      <p class="text-[10px] font-bold text-stone-500 -mt-1"><?php echo e($progress); ?>% sold</p>
    <?php endif; ?>

    <?php if($isOutOfStock): ?>
      <button type="button" disabled class="w-full py-2.5 px-3 rounded-lg bg-stone-100 text-stone-400 font-bold text-xs cursor-not-allowed border border-stone-200" aria-disabled="true">
        Out of Stock
      </button>
    <?php else: ?>
      <button type="button" class="fk-add-btn add-to-cart" 
              data-product-id="<?php echo e($product->id); ?>" 
              data-title="<?php echo e($product->name); ?>" 
              data-price="<?php echo e(money($product->price)); ?>"
              data-raw-price="<?php echo e((float) $product->price); ?>"
              data-regular-price="<?php echo e($product->on_sale ? money($product->regular_price) : ''); ?>"
              data-discount="<?php echo e($discount); ?>"
              data-image="<?php echo e($img); ?>"
              data-url="<?php echo e(route('product.show', $product)); ?>"
              data-has-variants="<?php echo e($hasVariants ? 'true' : 'false'); ?>"
              data-variants="<?php echo e(json_encode($variantsGrouped)); ?>"
              data-skus="<?php echo e(json_encode($product->skus ? $product->skus->map(fn($s) => ['id' => $s->id, 'attributes' => $s->getAttributesData(), 'stock' => (int) $s->stock_quantity, 'price_adjustment' => (float) $s->price_adjustment])->values() : [])); ?>">
        <span class="fk-add-plus">+</span> <?php echo e($cta); ?>

      </button>
    <?php endif; ?>
  </div>
</article>
<?php /**PATH /Users/mohammadshamimhossain/Desktop/appFinal/resources/views/storefront/partials/product-card.blade.php ENDPATH**/ ?>