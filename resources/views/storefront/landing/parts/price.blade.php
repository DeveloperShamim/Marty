{{-- Regular / offer price boxes, the order button and the cash-on-delivery line --}}
<div class="relative mt-5 grid {{ $compare ? 'grid-cols-2' : 'grid-cols-1' }} rounded-2xl border border-gray-200 bg-white text-center">
  @if($discountText)
    <span class="absolute -top-3 right-4 rounded-full bg-rose-600 px-3 py-0.5 text-xs font-bold text-white">{{ $discountText }}</span>
  @endif
  @if($compare)
    <div class="p-4 border-r border-gray-100">
      <p class="text-xs text-gray-500">রেগুলার প্রাইস</p>
      <p class="mt-1 text-xl font-bold text-gray-400 line-through">{{ money($compare) }}</p>
    </div>
  @endif
  <div class="p-4">
    <p class="text-xs text-gray-500">অফার প্রাইস</p>
    <p class="mt-1 text-2xl font-extrabold lp-accent" data-lp-price>{{ money($price) }}</p>
  </div>
</div>
<a href="#order" class="lp-btn mt-4 flex h-12 items-center justify-center gap-2 rounded-xl text-base font-bold shadow-sm" data-lp-cta>
  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
  {{ $buttonText }}
</a>
<p class="mt-3 flex flex-wrap justify-center gap-x-4 gap-y-1 text-[13px] text-gray-600">
  <span>✓ ক্যাশ অন ডেলিভারি</span>
  <span>✓ পণ্য দেখে মূল্য পরিশোধ</span>
</p>
