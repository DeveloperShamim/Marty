{{-- The order form. A template can set $formSection (outer classes), $formWidth (inner box) and $formStacked (one column). --}}
@php($stacked = $formStacked ?? false)
<section id="order" class="scroll-mt-4 {{ $formSection ?? 'bg-gray-50 py-10 sm:py-14' }}" data-lp-section="form">
  <div class="{{ $formWidth ?? 'max-w-5xl mx-auto px-4' }}">
    @if(filled($form['heading']))<h2 class="text-center text-xl sm:text-2xl font-bold">{{ $form['heading'] }}</h2>@endif
    @if(filled($form['subheading']))<p class="mt-1 text-center text-sm text-gray-500">{{ $form['subheading'] }}</p>@endif

    @if($errors->any())
      <div class="mx-auto mt-5 max-w-xl rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert" data-lp-errors>
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
      </div>
    @endif

    <form method="POST" action="{{ route('landing.order', $page->slug) }}" id="lpForm" class="mt-6 grid gap-5 {{ $stacked ? 'grid-cols-1' : 'lg:grid-cols-5' }}" novalidate>
      @csrf
      <input type="hidden" name="sku_id" id="lpSku" value="{{ old('sku_id') }}">
      <input type="hidden" name="qty" id="lpQty" value="{{ $oldQty }}">

      <div class="space-y-5 {{ $stacked ? 'order-2' : 'lg:col-span-3' }}">
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
          <h3 class="font-bold">{{ $form['delivery_heading'] }}</h3>
          <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <label class="block">
              <span class="text-sm font-medium">আপনার নাম <span class="text-rose-600">*</span></span>
              <input name="customer_name" value="{{ old('customer_name') }}" class="lp-input mt-1" required maxlength="120" autocomplete="name">
            </label>
            <label class="block">
              <span class="text-sm font-medium">মোবাইল নম্বর <span class="text-rose-600">*</span></span>
              <input name="customer_phone" value="{{ old('customer_phone') }}" class="lp-input mt-1" required inputmode="tel" placeholder="01XXXXXXXXX" maxlength="16" autocomplete="tel">
            </label>
            <label class="block sm:col-span-2">
              <span class="text-sm font-medium">সম্পূর্ণ ঠিকানা <span class="text-rose-600">*</span></span>
              <textarea name="shipping_address" rows="2" class="lp-input mt-1" required maxlength="255" placeholder="বাসা, রোড, এলাকা, থানা, জেলা" autocomplete="street-address">{{ old('shipping_address') }}</textarea>
            </label>
          </div>
          <p class="mt-4 text-sm font-medium">ডেলিভারি এলাকা <span class="text-rose-600">*</span></p>
          <div class="mt-2 grid grid-cols-2 gap-3">
            <label class="lp-zone flex cursor-pointer items-center gap-2 rounded-xl border border-gray-200 p-3 text-sm">
              <input type="radio" name="shipping_zone" value="inside_dhaka" @checked($oldZone === 'inside_dhaka') required>
              <span>ঢাকার ভিতরে <b class="lp-accent">{{ $shipInside > 0 ? money($shipInside) : 'ফ্রি' }}</b></span>
            </label>
            <label class="lp-zone flex cursor-pointer items-center gap-2 rounded-xl border border-gray-200 p-3 text-sm">
              <input type="radio" name="shipping_zone" value="outside_dhaka" @checked($oldZone === 'outside_dhaka')>
              <span>ঢাকার বাইরে <b class="lp-accent">{{ $shipOutside > 0 ? money($shipOutside) : 'ফ্রি' }}</b></span>
            </label>
          </div>
          <label class="mt-4 block">
            <span class="text-sm font-medium">অর্ডার নোট (ঐচ্ছিক)</span>
            <textarea name="delivery_note" rows="2" class="lp-input mt-1" maxlength="500">{{ old('delivery_note') }}</textarea>
          </label>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5">
          <h3 class="font-bold">পেমেন্ট</h3>
          <div class="mt-3 flex items-center gap-3 rounded-xl border p-3 text-sm" style="border-color: var(--lp); background: color-mix(in srgb, var(--lp) 6%, var(--lp-base));">
            <span class="lp-bg h-4 w-4 shrink-0 rounded-full ring-4 ring-white" style="box-shadow: 0 0 0 1px var(--lp);"></span>
            <span><b>ক্যাশ অন ডেলিভারি</b><br><span class="text-gray-500">পণ্য হাতে পেয়ে মূল্য পরিশোধ করুন</span></span>
          </div>
        </div>
      </div>

      <div class="{{ $stacked ? 'order-1' : 'lg:col-span-2' }}">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 {{ $stacked ? '' : 'lg:sticky lg:top-4' }}">
          <h3 class="font-bold">{{ $form['summary_heading'] }}</h3>
          <div class="mt-4 flex gap-3">
            @if(count($gallery))<img src="{{ $gallery[0] }}" alt="" class="h-16 w-16 shrink-0 rounded-lg border border-gray-100 object-cover">@endif
            <div class="min-w-0 flex-1">
              <p class="font-semibold leading-snug">{{ $product->name }}</p>
              <p class="text-sm lp-accent font-bold mt-0.5">{{ money($price) }}</p>
            </div>
          </div>

          @if($hasOptions)
            <div class="mt-4 space-y-3" id="lpOptions">
              @foreach($groups as $name => $values)
                <div>
                  <p class="text-sm font-medium">{{ $name }} <span class="text-rose-600">*</span></p>
                  <div class="mt-1.5 flex flex-wrap gap-2">
                    @foreach($values as $value)
                      <button type="button" class="lp-opt h-9 min-w-[2.75rem] rounded-lg border border-gray-300 px-3 text-sm font-semibold" data-lp-group="{{ $name }}" data-lp-value="{{ $value }}" aria-pressed="false">{{ $value }}</button>
                    @endforeach
                  </div>
                </div>
              @endforeach
              <p class="hidden text-xs text-rose-600" id="lpOptionNote">এই সাইজ / কালারটি স্টকে নেই।</p>
            </div>
          @endif

          <div class="mt-4 flex items-center justify-between">
            <span class="text-sm font-medium">পরিমাণ</span>
            <div class="flex items-center rounded-lg border border-gray-300">
              <button type="button" class="h-9 w-9 text-lg" data-lp-qty="-1" aria-label="কমান">−</button>
              <span class="w-8 text-center font-semibold" id="lpQtyText">{{ $oldQty }}</span>
              <button type="button" class="h-9 w-9 text-lg" data-lp-qty="1" aria-label="বাড়ান">+</button>
            </div>
          </div>

          <dl class="mt-4 space-y-2 border-t border-gray-100 pt-4 text-sm">
            <div class="flex justify-between"><dt class="text-gray-600">{{ $form['subtotal_label'] }}</dt><dd class="font-semibold" id="lpSubtotal">{{ money($quotes[$oldQty]['inside_dhaka']['subtotal']) }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-600">{{ $form['delivery_label'] }}</dt><dd class="font-semibold" id="lpShipping">{{ $oldZone ? money($quotes[$oldQty][$oldZone]['shipping']) : 'এলাকা বেছে নিন' }}</dd></div>
            <div class="flex justify-between {{ ($quotes[1]['inside_dhaka']['tax'] ?? 0) > 0 ? '' : 'hidden' }}"><dt class="text-gray-600">ট্যাক্স</dt><dd class="font-semibold" id="lpTax">{{ money($quotes[$oldQty][$oldZone ?: 'inside_dhaka']['tax']) }}</dd></div>
            <div class="flex justify-between border-t border-gray-100 pt-3 text-base"><dt class="font-bold">{{ $form['total_label'] }}</dt><dd class="font-extrabold" id="lpTotal">{{ money($oldZone ? $quotes[$oldQty][$oldZone]['total'] : $quotes[$oldQty]['inside_dhaka']['subtotal']) }}</dd></div>
          </dl>

          @if($soldOut)
            <p class="mt-5 rounded-xl bg-gray-100 py-3 text-center font-bold text-gray-600" data-lp-soldout>দুঃখিত, পণ্যটি এখন স্টকে নেই</p>
          @else
            <button type="submit" class="lp-btn mt-5 flex h-12 w-full items-center justify-center rounded-xl text-base font-bold" id="lpSubmit">
              {{ $form['button'] }}
            </button>
          @endif
        </div>
      </div>
    </form>
  </div>
</section>
