{{-- Homepage section, included in the order set in Admin → Homepage → Sections (App\Support\HomeSections) --}}
{{-- 10. EXCLUSIVE COUPONS & VOUCHERS: last, after Just for you --}}
@if($coupons->isNotEmpty())
  <section class="mt-12 sm:mt-16 last:mb-12" data-reveal data-home-coupons>
    <div class="flex items-end justify-between mb-4 pb-3">
      <div>
        <h2 class="text-base sm:text-xl font-medium text-stone-900 leading-tight">
          Exclusive Vouchers &amp; Offers
        </h2>
      </div>
      <div class="flex items-center gap-2">
        <button type="button" id="couponPrev" class="h-8 w-8 rounded-full bg-white hover:bg-brand-500 hover:text-white border border-stone-200 hover:border-brand-500 text-stone-700 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Previous Coupon">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button type="button" id="couponNext" class="h-8 w-8 rounded-full bg-white hover:bg-brand-500 hover:text-white border border-stone-200 hover:border-brand-500 text-stone-700 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Next Coupon">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>

    <div class="swiper couponsSwiper !py-1 !px-0.5 -mx-0.5">
      <div class="swiper-wrapper">
        @foreach($coupons as $coupon)
          <div class="swiper-slide">
            <div class="relative overflow-hidden rounded-2xl bg-white border border-stone-200/90 p-4 sm:p-5 shadow-2xs hover:shadow-md hover:border-stone-900 transition-all duration-200 flex flex-col justify-between h-full group">

              {{-- Ticket Cutout Notches --}}
              <div class="absolute -left-2.5 bottom-11 w-5 h-5 bg-stone-50 border-r border-stone-200/90 rounded-full"></div>
              <div class="absolute -right-2.5 bottom-11 w-5 h-5 bg-stone-50 border-l border-stone-200/90 rounded-full"></div>

              <div>
                <div class="flex items-center justify-between gap-2 mb-2.5">
                  <span class="inline-flex items-center gap-1.5 bg-stone-100 border border-dashed border-stone-300 text-stone-900 font-mono font-black text-xs px-2.5 py-1 rounded-lg tracking-wider uppercase">
                    {{ $coupon->code }}
                  </span>
                  <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Active
                  </span>
                </div>

                <div class="flex items-baseline gap-1.5 mt-2">
                  <span class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight leading-none">{{ $coupon->valueLabel() }}</span>
                  <span class="text-[10px] sm:text-xs font-black uppercase text-stone-700 bg-stone-100 px-2 py-0.5 rounded-md leading-none">OFF</span>
                </div>

                <p class="text-xs text-stone-600 mt-2 line-clamp-1 font-medium leading-tight">
                  @if($coupon->description)
                    {{ $coupon->description }}
                  @elseif($coupon->min_order_amount)
                    Min. order {{ money($coupon->min_order_amount) }}
                  @else
                    Valid at checkout
                  @endif
                </p>
              </div>

              <div class="mt-4 pt-3 border-t border-dashed border-stone-200 flex items-center justify-between gap-2">
                <span class="text-[11px] font-medium text-stone-400">At checkout</span>
                <button type="button" onclick="applyAndCopyCoupon('{{ $coupon->code }}')" class="inline-flex items-center gap-1.5 rounded-xl bg-stone-900 hover:bg-black text-white font-bold text-xs px-3.5 py-1.5 shadow-2xs transition-colors cursor-pointer">
                  <span>Use Code</span>
                  <span class="text-xs font-mono">&rarr;</span>
                </button>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif
