<?php
    $title = $product->name;
    $rawTitle = $product->meta_title ?: null;
    $metaDescription = $product->meta_description ?: $product->short_description;
    $metaKeywords = $product->meta_keywords;
    $ogImage = $product->imageUrl();
    $ogType = 'product';
    $bulletSpecs = collect($product->specificationBullets(6));
    if ($bulletSpecs->isEmpty()) {
        $bulletSpecs = collect(preg_split('/\r\n|\r|\n/', (string) ($product->short_description ?: '')))
            ->map(fn ($l) => trim($l, " \t-•"))
            ->filter()
            ->take(6)
            ->values();
    }
    $specRows = $product->specificationRows();
    $whatsapp = preg_replace('/\D+/', '', (string) setting('contact_phone', ''));
    $isOutOfStock = (int) $product->stock_quantity <= 0;
    $savings = $product->on_sale ? ($product->regular_price - $product->price) : 0;
?>

<?php $__env->startSection('content'); ?>
<main class="max-w-7xl mx-auto px-4 sm:px-5 py-6">
  
  <nav class="flex items-center gap-2 text-xs sm:text-sm text-stone-500 mb-6 flex-wrap">
    <a href="<?php echo e(route('home')); ?>" class="hover:text-brand-600 transition-colors">Home</a>
    <span class="text-stone-300">/</span>
    <?php if($product->category): ?>
      <a href="<?php echo e(route('shop.category', $product->category)); ?>" class="hover:text-brand-600 transition-colors"><?php echo e($product->category->name); ?></a>
      <span class="text-stone-300">/</span>
    <?php endif; ?>
    <span class="text-stone-800 font-semibold truncate max-w-[220px] sm:max-w-xs"><?php echo e($product->name); ?></span>
  </nav>

  
  <div class="bg-white rounded-2xl border border-stone-200/80 p-5 sm:p-8 flex flex-col md:flex-row gap-8 items-start shadow-sm">
    
    <div class="flex flex-col-reverse sm:flex-row gap-4 items-start w-full md:w-1/2 shrink-0">
      <?php if($product->images->count() > 0): ?>
        <div class="flex sm:flex-col gap-3 overflow-x-auto sm:overflow-y-auto w-full sm:w-20 shrink-0 pb-1 sm:pb-0 max-h-[460px] no-scrollbar">
          <?php $__currentLoopData = $product->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button type="button" data-thumb="<?php echo e($img->url()); ?>" class="gallery-thumb-btn w-16 h-16 sm:w-20 sm:h-20 rounded-xl border <?php echo e($loop->first ? 'border-brand-500' : 'border-stone-200 opacity-80 hover:opacity-100'); ?> shrink-0 bg-white overflow-hidden relative transition-colors focus:outline-none">
              <img src="<?php echo e($img->url()); ?>" loading="lazy" decoding="async" class="w-full h-full object-cover" alt="<?php echo e($img->alt); ?>">
              <?php if($loop->first): ?>
                <span data-active-check class="absolute inset-0 flex items-center justify-center pointer-events-none"><span class="w-6 h-6 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-xs">✓</span></span>
              <?php endif; ?>
            </button>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      <?php endif; ?>

      <div class="flex-1 relative border border-stone-200/80 rounded-2xl aspect-square w-full bg-white overflow-hidden shadow-xs flex items-center justify-center">
        <?php if($isOutOfStock): ?>
          <span class="absolute top-4 left-4 z-10 bg-stone-800/90 text-white font-extrabold text-xs tracking-wider uppercase px-3 py-1.5 rounded-lg shadow-sm">Out of Stock</span>
        <?php elseif($product->on_sale): ?>
          <span class="absolute top-4 left-4 z-10 bg-red-500 text-white font-extrabold text-xs tracking-wider uppercase px-3 py-1.5 rounded-lg shadow-sm"><?php echo e($product->discount_percent); ?>% OFF</span>
        <?php endif; ?>

        <?php if($product->images->count() > 1): ?>
          <button type="button" id="pdPrevImg" class="absolute left-2 top-1/2 -translate-y-1/2 z-10 p-1 text-brand-500 hover:text-brand-600 transition-colors focus:outline-none bg-transparent" aria-label="Previous Image">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <button type="button" id="pdNextImg" class="absolute right-2 top-1/2 -translate-y-1/2 z-10 p-1 text-brand-500 hover:text-brand-600 transition-colors focus:outline-none bg-transparent" aria-label="Next Image">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </button>
        <?php endif; ?>

        <img id="galleryMain" data-gallery-main src="<?php echo e($product->imageUrl()); ?>" loading="lazy" decoding="async" class="w-full h-full object-contain <?php echo e($isOutOfStock ? 'opacity-75 grayscale-[30%]' : ''); ?>" alt="<?php echo e($product->name); ?>" />
      </div>
    </div>

    
    <div class="w-full md:w-1/2 space-y-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-stone-900 leading-snug mb-3"><?php echo e($product->name); ?></h1>

        
        <?php
          $skusPayload = $product->skus->map(fn($s) => [
            'id'               => $s->id,
            'attributes'       => $s->getAttributesData(),
            'stock'            => (int) $s->stock_quantity,
            'price_adjustment' => (float) $s->price_adjustment,
          ])->values();
        ?>

        
        <div class="flex items-center gap-3 flex-wrap">
          <span id="pdPrice" class="text-3xl font-extrabold text-brand-500" data-base-price="<?php echo e((float) $product->price); ?>"><?php echo e(money($product->price)); ?></span>
          <?php if($product->on_sale): ?>
            <span id="pdRegularPrice" class="text-stone-400 line-through text-lg font-normal" data-base-regular="<?php echo e((float) $product->regular_price); ?>"><?php echo e(money($product->regular_price)); ?></span>
            <span class="bg-emerald-500 text-white font-extrabold text-xs px-2.5 py-1 rounded shadow-xs">Save <?php echo e($product->discount_percent); ?>%</span>
          <?php endif; ?>
        </div>
      </div>

      <hr class="border-stone-100 my-4" />

      
      <?php if($colors->isNotEmpty()): ?>
        <div data-variant-group="Color">
          <p class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">Color</p>
          <div class="flex flex-wrap gap-2">
            <?php $__currentLoopData = $colors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <button type="button" class="variant-btn px-4 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-all <?php echo e($loop->first ? 'border-2 border-brand-500 text-brand-600 bg-brand-50/40 is-selected' : 'border border-stone-200 text-stone-700 hover:border-stone-300'); ?>" data-type="Color" data-value="<?php echo e($v->value); ?>"><?php echo e($v->value); ?></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if($sizes->isNotEmpty()): ?>
        <div data-variant-group="Size">
          <p class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">Size</p>
          <div class="flex flex-wrap gap-2">
            <?php $__currentLoopData = $sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <button type="button" class="variant-btn px-4 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-all <?php echo e($loop->first ? 'border-2 border-brand-500 text-brand-600 bg-brand-50/40 is-selected' : 'border border-stone-200 text-stone-700 hover:border-stone-300'); ?>" data-type="Size" data-value="<?php echo e($v->value); ?>"><?php echo e($v->value); ?></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if($weights->isNotEmpty()): ?>
        <div data-variant-group="Weight">
          <p class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">Option</p>
          <div class="flex flex-wrap gap-2">
            <?php $__currentLoopData = $weights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <button type="button" class="variant-btn px-4 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-all <?php echo e($loop->first ? 'border-2 border-brand-500 text-brand-600 bg-brand-50/40 is-selected' : 'border border-stone-200 text-stone-700 hover:border-stone-300'); ?>" data-type="Weight" data-value="<?php echo e($v->value); ?>"><?php echo e($v->value); ?></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </div>
      <?php endif; ?>

      
      <div class="flex items-center gap-3 py-1">
        <span class="text-sm font-semibold text-stone-600">Quantity:</span>
        <div data-qty data-stepper class="inline-flex items-center border border-stone-300 rounded-lg overflow-hidden bg-white shadow-xs">
          <button type="button" data-step="-1" data-dec class="px-3.5 py-1.5 text-stone-500 hover:bg-stone-100 font-bold text-sm transition-colors">−</button>
          <input id="pdQty" value="1" class="w-10 text-center border-0 font-bold text-stone-800 focus:outline-none text-sm bg-transparent" readonly />
          <button type="button" data-step="1" data-inc class="px-3.5 py-1.5 text-stone-500 hover:bg-stone-100 font-bold text-sm transition-colors">+</button>
        </div>
      </div>

      
      <div class="space-y-3 pt-1">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <button type="button" id="pdAddToCart" data-product-id="<?php echo e($product->id); ?>" data-title="<?php echo e($product->name); ?>" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-extrabold py-3.5 px-4 rounded-xl shadow transition-all flex items-center justify-center gap-2 text-xs sm:text-sm uppercase tracking-wide cursor-pointer disabled:bg-stone-200 disabled:text-stone-400 disabled:cursor-not-allowed" <?php if($isOutOfStock): echo 'disabled'; endif; ?>>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span id="pdAddToCartText"><?php echo e($isOutOfStock ? 'OUT OF STOCK' : setting('default_cta_text', 'ADD TO CART')); ?></span>
          </button>

          <button type="button" id="pdBuyNow" data-buy-now data-product-id="<?php echo e($product->id); ?>" data-title="<?php echo e($product->name); ?>" data-checkout-url="<?php echo e(route('checkout.show')); ?>" class="w-full bg-[#0B2523] hover:bg-black text-white font-extrabold py-3.5 px-4 rounded-xl shadow transition-all flex items-center justify-center text-xs sm:text-sm uppercase tracking-wide disabled:opacity-50 disabled:cursor-not-allowed" <?php if($isOutOfStock): echo 'disabled'; endif; ?>>
            BUY NOW
          </button>
        </div>

        <?php if($whatsapp): ?>
          <a href="https://wa.me/<?php echo e($whatsapp); ?>?text=<?php echo e(urlencode('Hi, I want to buy: '.$product->name)); ?>" target="_blank" rel="noopener" class="w-full bg-[#10B981] hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-xl shadow transition-all flex items-center justify-center gap-2 text-sm">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.147 4.19 4.18-1.096z"/></svg>
            Order On WhatsApp
          </a>
        <?php endif; ?>
      </div>

      
      <?php if($product->brand): ?>
        <div class="pt-2">
          <div class="inline-flex items-center gap-2 border border-stone-200/80 rounded-xl px-4 py-2 text-xs sm:text-sm font-semibold text-stone-700 bg-white shadow-xs">
            Brand: <span class="font-bold text-stone-900 flex items-center gap-1.5">🐝 <?php echo e($product->brand); ?></span>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  
  <div class="bg-white rounded-2xl border border-stone-200/80 p-6 sm:p-8 mt-8 shadow-sm space-y-6">
    <div class="border-b border-stone-200 pb-4">
      <h2 class="font-extrabold text-xl text-stone-900">Product Details &amp; Specifications</h2>
    </div>

    <?php if(! empty($specRows)): ?>
      <div>
        <h3 class="font-extrabold text-base text-stone-900 mb-3">Specifications</h3>
        <div class="overflow-x-auto rounded-xl border border-stone-200/80">
          <table class="w-full text-sm text-left">
            <tbody>
              <tr class="bg-stone-50 border-b border-stone-200/80"><td colspan="2" class="px-4 py-2.5 font-bold text-stone-700">Basic Information</td></tr>
              <tr class="border-b border-stone-100"><td class="px-4 py-2.5 w-60 font-semibold text-stone-500">Brand</td><td class="px-4 py-2.5 text-stone-800"><?php echo e($product->brand ?: "—"); ?></td></tr>
              <tr class="border-b border-stone-100"><td class="px-4 py-2.5 font-semibold text-stone-500">Category</td><td class="px-4 py-2.5 text-stone-800"><?php echo e($product->category?->name); ?></td></tr>
              <tr class="border-b border-stone-100"><td class="px-4 py-2.5 font-semibold text-stone-500">Unit</td><td class="px-4 py-2.5 text-stone-800"><?php echo e($product->unit ?: "—"); ?></td></tr>
              <tr class="border-b border-stone-100"><td class="px-4 py-2.5 font-semibold text-stone-500">SKU</td><td class="px-4 py-2.5 text-stone-800"><?php echo e($product->sku ?: 'N/A'); ?></td></tr>
              <tr class="bg-stone-50 border-b border-stone-200/80"><td colspan="2" class="px-4 py-2.5 font-bold text-stone-700">Product Specifications</td></tr>
              <?php $__currentLoopData = $specRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-b border-stone-100">
                  <td class="px-4 py-2.5 font-semibold text-stone-500"><?php echo e($row['label'] !== '' ? $row['label'] : 'Detail'); ?></td>
                  <td class="px-4 py-2.5 text-stone-800"><?php echo e($row['value'] !== '' ? $row['value'] : "—"); ?></td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <?php if($product->description): ?>
      <div>
        <h3 class="font-extrabold text-base text-stone-900 mb-3">Description</h3>
        <div class="text-stone-600 leading-relaxed max-w-4xl space-y-3 text-sm sm:text-base">
          <?php $__currentLoopData = preg_split('/\n\n+/', (string) $product->description); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $para): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(trim($para)): ?><p><?php echo e($para); ?></p><?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  
  <?php if($related->isNotEmpty()): ?>
    <section class="mt-10">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900">Related Products</h2>
        <a href="<?php echo e(route('shop')); ?>" class="inline-flex items-center gap-1 text-sm font-bold text-brand-600 hover:underline">
          See All <span class="text-base">→</span>
        </a>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
        <?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php echo $__env->make('storefront.partials.product-card', ['product' => $rel], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </section>
  <?php endif; ?>
  
  <div id="imageLightboxModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4 hidden transition-opacity duration-300 opacity-0" aria-hidden="true">
    <div class="relative max-w-4xl w-full max-h-[90vh] bg-white rounded-2xl p-4 overflow-hidden flex flex-col items-center justify-center shadow-2xl">
      <button type="button" id="closeLightbox" class="absolute top-3 right-3 z-10 w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 font-extrabold flex items-center justify-center text-base transition-colors focus:outline-none" aria-label="Close Lightbox">✕</button>
      <img id="lightboxImg" src="" class="max-h-[82vh] w-auto h-auto object-contain rounded-xl" alt="Enlarged product image" />
    </div>
  </div>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function() {
  const thumbBtns = Array.from(document.querySelectorAll('.gallery-thumb-btn'));
  const mainImg = document.getElementById('galleryMain');
  const prevBtn = document.getElementById('pdPrevImg');
  const nextBtn = document.getElementById('pdNextImg');
  const modal = document.getElementById('imageLightboxModal');
  const lightboxImg = document.getElementById('lightboxImg');
  const closeBtn = document.getElementById('closeLightbox');

  if (!thumbBtns.length || !mainImg) return;

  let currentIndex = 0;

  function setActiveImage(index) {
    if (index < 0) index = thumbBtns.length - 1;
    if (index >= thumbBtns.length) index = 0;
    currentIndex = index;

    const btn = thumbBtns[currentIndex];
    const src = btn.getAttribute('data-thumb');
    if (src) mainImg.src = src;

    thumbBtns.forEach((x, i) => {
      const check = x.querySelector('[data-active-check]');
      if (i === currentIndex) {
        x.classList.add('border-brand-500');
        x.classList.remove('border-stone-200', 'opacity-80');
        if (!check) {
          const checkWrap = document.createElement('span');
          checkWrap.setAttribute('data-active-check', 'true');
          checkWrap.className = 'absolute inset-0 flex items-center justify-center pointer-events-none';
          checkWrap.innerHTML = '<span class="w-6 h-6 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-xs">✓</span>';
          x.appendChild(checkWrap);
        }
      } else {
        x.classList.remove('border-brand-500');
        x.classList.add('border-stone-200', 'opacity-80');
        if (check) check.remove();
      }
    });
  }

  thumbBtns.forEach((btn, index) => {
    btn.addEventListener('click', () => setActiveImage(index));
  });

  if (prevBtn) prevBtn.addEventListener('click', () => setActiveImage(currentIndex - 1));
  if (nextBtn) nextBtn.addEventListener('click', () => setActiveImage(currentIndex + 1));

  if (mainImg && modal && lightboxImg) {
    mainImg.classList.add('cursor-pointer');
    mainImg.addEventListener('click', () => {
      lightboxImg.src = mainImg.src;
      modal.classList.remove('hidden');
      setTimeout(() => modal.classList.remove('opacity-0'), 10);
    });

    const hideModal = () => {
      modal.classList.add('opacity-0');
      setTimeout(() => modal.classList.add('hidden'), 300);
    };

    if (closeBtn) closeBtn.addEventListener('click', hideModal);
    modal.addEventListener('click', (e) => {
      if (e.target === modal) hideModal();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) hideModal();
    });
  }
})();

window.productSkus = <?php echo $skusPayload->toJson(); ?>;

function findPdpMatchingSku(skus, selectedAttrs) {
  if (!skus || !skus.length) return null;
  const selectedKeys = Object.keys(selectedAttrs);
  if (!selectedKeys.length) return null;

  return skus.find(s => {
    if (!s.attributes) return false;
    const attrs = s.attributes;
    const attrMap = {};
    Object.keys(attrs).forEach(k => {
      attrMap[String(k).trim().toLowerCase()] = String(attrs[k]).trim().toLowerCase();
    });

    return selectedKeys.every(k => {
      const kLower = String(k).trim().toLowerCase();
      const expectedVal = String(selectedAttrs[k]).trim().toLowerCase();
      return attrMap[kLower] === expectedVal;
    });
  });
}

function syncPdpVariantStockAndPrice() {
  const priceEl = document.getElementById('pdPrice');
  const regEl = document.getElementById('pdRegularPrice');
  const addBtn = document.getElementById('pdAddToCart');
  const buyBtn = document.getElementById('pdBuyNow');
  const btnText = document.getElementById('pdAddToCartText');

  const basePrice = parseFloat(priceEl?.getAttribute('data-base-price') || '0');
  const baseReg = regEl ? parseFloat(regEl.getAttribute('data-base-regular') || '0') : 0;

  // Selected attributes
  const selectedAttrs = {};
  document.querySelectorAll('[data-variant-group]').forEach(group => {
    const activeBtn = group.querySelector('.variant-btn.is-selected');
    if (activeBtn) {
      const type = activeBtn.getAttribute('data-type') || group.getAttribute('data-variant-group');
      const val = activeBtn.getAttribute('data-value');
      if (type && val) {
        selectedAttrs[type] = val;
      }
    }
  });

  const selectedColor = selectedAttrs['Color'] || selectedAttrs['color'] || null;

  // Update Size availability based on selected Color
  if (selectedColor && window.productSkus && window.productSkus.length > 0) {
    const sizeGroup = document.querySelector('[data-variant-group="Size"]');
    if (sizeGroup) {
      sizeGroup.querySelectorAll('.variant-btn').forEach(btn => {
        const sizeVal = btn.getAttribute('data-value');
        const matchingSku = findPdpMatchingSku(window.productSkus, { Color: selectedColor, Size: sizeVal });

        if (matchingSku) {
          if (matchingSku.stock > 0) {
            btn.disabled = false;
            btn.classList.remove('opacity-40', 'line-through', 'cursor-not-allowed');
            btn.title = '';
          } else {
            btn.disabled = true;
            btn.classList.add('opacity-40', 'line-through', 'cursor-not-allowed');
            btn.title = `Size ${sizeVal} is out of stock for ${selectedColor}`;
          }
        }
      });
    }
  }

  // Find exact matching SKU combination
  const matchedSku = findPdpMatchingSku(window.productSkus, selectedAttrs);

  let finalPrice = basePrice;
  let isAvailable = true;

  if (matchedSku) {
    const adj = parseFloat(matchedSku.price_adjustment) || 0;
    if (adj > 0) {
      if (adj >= (basePrice * 0.4)) {
        finalPrice = adj;
      } else {
        finalPrice = basePrice + adj;
      }
    } else if (adj < 0) {
      finalPrice = Math.max(0, basePrice + adj);
    }
    isAvailable = matchedSku.stock > 0;
    if (addBtn) addBtn.dataset.skuId = matchedSku.id;
  }

  const availStock = matchedSku ? matchedSku.stock : 99;
  const maxAllowed = Math.min(3, availStock);
  const qtyInput = document.getElementById('pdQty');
  if (qtyInput && parseInt(qtyInput.value, 10) > maxAllowed) {
    qtyInput.value = Math.max(1, maxAllowed);
  }

  if (priceEl) {
    priceEl.textContent = '৳' + finalPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  if (regEl && baseReg > 0) {
    const finalReg = Math.max(0, baseReg + (matchedSku ? (parseFloat(matchedSku.price_adjustment) || 0) : 0));
    regEl.textContent = '৳' + finalReg.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  if (addBtn) {
    addBtn.disabled = !isAvailable;
    if (btnText) btnText.textContent = isAvailable ? 'ADD TO CART' : 'OUT OF STOCK';
  }
  if (buyBtn) {
    buyBtn.disabled = !isAvailable;
  }
}

document.querySelectorAll('[data-variant-group] .variant-btn').forEach((b) => b.addEventListener('click', (e) => {
  if (b.disabled) {
    e.preventDefault();
    return;
  }
  const group = b.closest('[data-variant-group]');
  group.querySelectorAll('.variant-btn').forEach((x) => {
    x.classList.remove('is-selected', 'border-2', 'border-brand-500', 'text-brand-600', 'bg-brand-50/40');
    x.classList.add('border', 'border-stone-200', 'text-stone-700');
  });
  b.classList.add('is-selected', 'border-2', 'border-brand-500', 'text-brand-600', 'bg-brand-50/40');
  b.classList.remove('border-stone-200', 'text-stone-700');

  syncPdpVariantStockAndPrice();
}));

// Run initial sync on load
syncPdpVariantStockAndPrice();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.storefront', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/unilifeb/UnilifeBD/resources/views/storefront/product.blade.php ENDPATH**/ ?>