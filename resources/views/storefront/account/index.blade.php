@extends('layouts.storefront')
@php $title = 'My Account'; @endphp

@section('content')
<main class="min-h-[70vh] bg-slate-50/60 py-8 sm:py-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    {{-- Classic Account Profile Banner (Light Theme) --}}
    <div class="rounded-3xl bg-white border border-slate-200/90 p-5 sm:p-7 mb-8 shadow-xs">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4 sm:gap-5 min-w-0">
          @php
            $initials = collect(preg_split('/\s+/', trim((string) $user->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
          @endphp
          <div class="h-16 w-16 sm:h-18 sm:w-18 rounded-2xl bg-brand-50 border border-brand-200 text-brand-700 font-extrabold text-2xl sm:text-3xl grid place-items-center shrink-0 shadow-2xs">
            {{ $initials ?: 'U' }}
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight truncate">{{ $user->name }}</h1>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                Verified Customer
              </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 truncate">{{ $user->email }}</p>
            @if($user->phone)
              <p class="text-xs text-slate-400 mt-0.5 font-mono">{{ $user->phone }}</p>
            @endif
          </div>
        </div>

        {{-- Stats & Logout Action --}}
        <div class="flex flex-wrap items-center gap-4 sm:gap-6 pt-4 md:pt-0 border-t md:border-t-0 border-slate-100">
          <div class="flex items-center gap-6">
            <div class="text-center sm:text-left">
              <p class="text-xs text-slate-500 font-medium">Total Orders</p>
              <p class="text-xl sm:text-2xl font-extrabold text-slate-900 font-mono">{{ $orders->count() }}</p>
            </div>
            <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
            <div class="text-center sm:text-left">
              <p class="text-xs text-slate-500 font-medium">Delivered</p>
              <p class="text-xl sm:text-2xl font-extrabold text-emerald-600 font-mono">{{ $orders->where('status', 'delivered')->count() }}</p>
            </div>
            <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
            <div class="text-center sm:text-left">
              <p class="text-xs text-slate-500 font-medium">My Reviews</p>
              <p class="text-xl sm:text-2xl font-extrabold text-amber-600 font-mono">{{ $userReviews->count() }}</p>
            </div>
          </div>

          <form method="POST" action="{{ route('logout') }}" class="ml-auto md:ml-2">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-600 border border-slate-200 hover:border-rose-200 text-xs font-bold transition-all cursor-pointer shadow-2xs">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
              <span>Sign out</span>
            </button>
          </form>
        </div>
      </div>
    </div>

    {{-- Main 2-Column Layout: Sidebar Nav + Content Panels --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      {{-- Left Sticky Navigation Sidebar (4 Columns) --}}
      <aside class="lg:col-span-4 space-y-4 lg:sticky lg:top-24">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-3 shadow-2xs space-y-1">
          
          <a href="#orders" class="account-nav-btn flex items-center justify-between px-4 py-3 rounded-xl text-sm font-extrabold text-brand-700 bg-brand-50 border border-brand-200/60 transition-all">
            <span class="flex items-center gap-3">
              <span class="text-base">📦</span>
              <span>My Orders</span>
            </span>
            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-brand-200/70 text-brand-800 font-mono">{{ $orders->count() }}</span>
          </a>

          <a href="#reviews" class="account-nav-btn flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold text-slate-700 hover:text-brand-600 hover:bg-slate-50 transition-all">
            <span class="flex items-center gap-3">
              <span class="text-base">⭐</span>
              <span>Product Reviews</span>
            </span>
            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 font-mono">{{ $userReviews->count() }}</span>
          </a>

          <a href="#addresses" class="account-nav-btn flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold text-slate-700 hover:text-brand-600 hover:bg-slate-50 transition-all">
            <span class="flex items-center gap-3">
              <span class="text-base">📍</span>
              <span>Delivery Addresses</span>
            </span>
            <span class="text-slate-400 text-xs">→</span>
          </a>

          <a href="#profile" class="account-nav-btn flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold text-slate-700 hover:text-brand-600 hover:bg-slate-50 transition-all">
            <span class="flex items-center gap-3">
              <span class="text-base">👤</span>
              <span>Profile Details</span>
            </span>
            <span class="text-slate-400 text-xs">→</span>
          </a>

          <a href="#password" class="account-nav-btn flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold text-slate-700 hover:text-brand-600 hover:bg-slate-50 transition-all">
            <span class="flex items-center gap-3">
              <span class="text-base">🔐</span>
              <span>Change Password</span>
            </span>
            <span class="text-slate-400 text-xs">→</span>
          </a>

          <div class="pt-2 border-t border-slate-100 mt-2">
            <a href="{{ route('shop') }}" class="flex items-center justify-between px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:text-brand-600 hover:bg-slate-50 transition-all">
              <span class="flex items-center gap-2">
                <span>🛍️</span>
                <span>Continue Shopping</span>
              </span>
              <span class="text-brand-600 font-bold">Explore →</span>
            </a>
          </div>
        </div>

        {{-- Help & Support Box --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-3">
          <div class="flex items-center gap-2">
            <span class="w-1.5 h-4 bg-brand-600 rounded-full"></span>
            <h4 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Need Any Help?</h4>
          </div>
          <p class="text-xs text-slate-500 leading-relaxed">
            Have questions about an order or delivery? Our support team is here to assist you anytime.
          </p>
          @if(setting('contact_phone'))
            <a href="tel:{{ setting('contact_phone') }}" class="inline-flex items-center gap-2 text-xs font-extrabold text-brand-600 hover:underline">
              <span>📞</span> <span>{{ setting('contact_phone') }}</span>
            </a>
          @endif
        </div>
      </aside>

      {{-- Right Main Content Area (8 Columns) --}}
      <div class="lg:col-span-8 space-y-10">
        
        {{-- SECTION 1: MY ORDERS --}}
        <section id="orders" class="space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <span class="w-1.5 h-5 bg-brand-600 rounded-full"></span>
              <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">My Orders</h2>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $orders->count() }} {{ Str::plural('Order', $orders->count()) }}</span>
          </div>

          @if($orders->isEmpty())
            <div class="rounded-3xl border border-dashed border-slate-200 bg-white p-12 text-center space-y-4 shadow-2xs">
              <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 text-3xl grid place-items-center mx-auto">📦</div>
              <div>
                <h3 class="font-extrabold text-base text-slate-900">No Orders Placed Yet</h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-sm mx-auto">Explore our collection of authentic quality goods and place your first order.</p>
              </div>
              <a href="{{ route('shop') }}" class="btn-shine inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm px-6 py-3 shadow-2xs transition">
                <span>Start Shopping</span>
                <span>→</span>
              </a>
            </div>
          @else
            <div class="space-y-4">
              @foreach($orders as $order)
                @php
                  $status = strtolower($order->status);
                  $statusStyles = match ($status) {
                    'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                    'confirmed', 'processing', 'shipped' => 'bg-sky-50 text-sky-700 border-sky-200/80',
                    'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                    default => 'bg-amber-50 text-amber-800 border-amber-200/80',
                  };
                @endphp
                <article class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs hover:shadow-md transition-all duration-200 overflow-hidden">
                  
                  {{-- Order Header Row --}}
                  <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 bg-slate-50/80 border-b border-slate-100 text-xs">
                    <div class="flex items-center gap-3">
                      <span class="w-1.5 h-4 bg-brand-600 rounded-full"></span>
                      <div>
                        <a href="{{ route('account.orders.show', $order) }}" class="font-mono font-extrabold text-sm text-slate-900 hover:text-brand-600">
                          {{ $order->order_number }}
                        </a>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                      </div>
                    </div>

                    <div class="flex items-center gap-3 ml-auto">
                      <div class="text-right">
                        <p class="font-extrabold text-sm text-slate-900 font-mono">{{ money($order->total) }}</p>
                        <p class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">{{ $order->paymentMethodLabel() }}</p>
                      </div>
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold border {{ $statusStyles }} shadow-2xs">
                        {{ ucfirst($order->status) }}
                      </span>
                    </div>
                  </div>

                  {{-- Purchased Items List --}}
                  <ul class="divide-y divide-slate-100">
                    @foreach($order->items as $item)
                      <li class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 hover:bg-slate-50/40 transition-colors">
                        <div class="flex items-center gap-3.5 min-w-0 flex-1">
                          <img src="{{ $item->imageUrl() }}" alt="{{ $item->product_name }}" class="h-14 w-12 object-cover bg-slate-100 rounded-xl shrink-0 border border-slate-200/60 shadow-2xs" />
                          <div class="min-w-0 flex-1">
                            @if($item->product && $item->product->is_published)
                              <a href="{{ route('product.show', $item->product) }}" class="text-xs sm:text-sm font-extrabold text-slate-900 truncate block hover:text-brand-600 transition-colors">
                                {{ $item->product_name }}
                              </a>
                            @else
                              <p class="text-xs sm:text-sm font-extrabold text-slate-900 truncate">{{ $item->product_name }}</p>
                            @endif
                            <p class="text-xs text-slate-500 mt-0.5">
                              @if($item->variant)<span class="font-medium text-slate-600">{{ $item->variant }}</span> · @endif
                              <span>Qty {{ $item->quantity }}</span> &middot; <span class="font-mono font-semibold">{{ money($item->line_total) }}</span>
                            </p>
                          </div>
                        </div>

                        {{-- Review Action for Delivered Orders --}}
                        @if($status === 'delivered' && $item->product)
                          <div class="sm:shrink-0 pt-2 sm:pt-0">
                            @if(in_array($item->product_id, $reviewedProductIds ?? []))
                              <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span>Reviewed</span>
                              </span>
                            @else
                              <a href="{{ route('product.show', $item->product) }}#reviews" class="btn-shine inline-flex items-center gap-1.5 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 px-3.5 py-1.5 rounded-xl shadow-2xs transition-all">
                                <span>★</span> <span>Rate &amp; Review</span>
                              </a>
                            @endif
                          </div>
                        @endif
                      </li>
                    @endforeach
                  </ul>

                  {{-- Order Footer Action Row --}}
                  <div class="px-5 py-3 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Shipping to {{ $order->city ?: 'Bangladesh' }}</span>
                    <a href="{{ route('account.orders.show', $order) }}" class="font-extrabold text-brand-600 hover:text-brand-700 hover:underline inline-flex items-center gap-1">
                      <span>View Full Order Details</span>
                      <span>→</span>
                    </a>
                  </div>
                </article>
              @endforeach
            </div>
          @endif
        </section>

        {{-- SECTION 2: PRODUCT REVIEWS --}}
        <section id="reviews" class="space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <span class="w-1.5 h-5 bg-brand-600 rounded-full"></span>
              <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">My Product Reviews</h2>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $userReviews->count() }} {{ Str::plural('Review', $userReviews->count()) }}</span>
          </div>

          @if($userReviews->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center space-y-2 shadow-2xs">
              <div class="text-3xl">⭐</div>
              <h3 class="font-extrabold text-sm text-slate-900">No Product Reviews Yet</h3>
              <p class="text-xs text-slate-500 max-w-sm mx-auto">When your orders are delivered, you can rate and share your honest feedback here.</p>
            </div>
          @else
            <div class="space-y-3">
              @foreach($userReviews as $uReview)
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 sm:p-5 shadow-2xs space-y-3">
                  <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                      <div class="flex items-center gap-0.5 text-amber-400 text-sm">
                        @for($s = 1; $s <= 5; $s++)
                          <span>{{ $s <= ($uReview->rating ?: 5) ? '★' : '☆' }}</span>
                        @endfor
                      </div>
                      <span class="text-xs font-extrabold text-slate-700">{{ $uReview->rating }}.0</span>
                    </div>

                    <div class="flex items-center gap-2">
                      @if($uReview->status === 'approved')
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                          ✓ Published
                        </span>
                      @else
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md">
                          ⏳ Under Review
                        </span>
                      @endif
                      <span class="text-[11px] text-slate-400">{{ $uReview->created_at?->format('d M Y') }}</span>
                    </div>
                  </div>

                  @if($uReview->product)
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-800 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                      <span class="text-slate-400">Product:</span>
                      <a href="{{ route('product.show', $uReview->product) }}" class="text-brand-600 hover:underline truncate">
                        {{ $uReview->product->name }}
                      </a>
                    </div>
                  @endif

                  <p class="text-xs sm:text-sm text-slate-600 leading-relaxed italic">
                    “{{ $uReview->body }}”
                  </p>
                </div>
              @endforeach
            </div>
          @endif
        </section>

        {{-- SECTION 4: DELIVERY ADDRESSES --}}
        <section id="addresses" class="space-y-4 scroll-mt-24">
          <div class="flex items-center gap-2.5">
            <span class="w-1.5 h-5 bg-brand-600 rounded-full"></span>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Delivery Address</h2>
          </div>

          <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-7 shadow-2xs space-y-4">
            <div class="flex items-start justify-between gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
              <div class="space-y-1">
                <div class="flex items-center gap-2">
                  <span class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Primary Shipping Address</span>
                  <span class="text-[10px] font-bold text-brand-700 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded-md">Default</span>
                </div>
                <p class="text-sm font-extrabold text-slate-800 pt-1">{{ $user->name }}</p>
                <p class="text-xs sm:text-sm text-slate-600">{{ $user->address ?: 'No default address saved yet.' }}</p>
                <p class="text-xs text-slate-500 font-mono">{{ $user->city ? $user->city . ' ' . $user->postal_code : '' }}</p>
                <p class="text-xs text-slate-500 font-mono pt-1">Phone: {{ $user->phone ?: 'Not provided' }}</p>
              </div>
            </div>

            <p class="text-xs text-slate-500">Update your primary shipping address below:</p>
            <form method="POST" action="{{ route('account.profile') }}" class="space-y-4 pt-1">
              @csrf @method('PUT')
              <input type="hidden" name="name" value="{{ $user->name }}">
              <input type="hidden" name="phone" value="{{ $user->phone }}">
              
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Street Address</label>
                <input name="address" value="{{ old('address', $user->address) }}" placeholder="House, Road, Apartment, Area" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/50 focus:outline-none transition-all" />
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">City / District</label>
                  <input name="city" value="{{ old('city', $user->city) }}" placeholder="e.g. Dhaka" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/50 focus:outline-none transition-all" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Postal Code</label>
                  <input name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" placeholder="e.g. 1212" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/50 focus:outline-none transition-all font-mono" />
                </div>
              </div>

              <div class="flex justify-end pt-2">
                <button type="submit" class="btn-shine rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm px-6 py-2.5 shadow-2xs transition-all cursor-pointer">
                  Save Address
                </button>
              </div>
            </form>
          </div>
        </section>

        {{-- SECTION 5: PROFILE DETAILS --}}
        <section id="profile" class="space-y-4 scroll-mt-24">
          <div class="flex items-center gap-2.5">
            <span class="w-1.5 h-5 bg-brand-600 rounded-full"></span>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Profile Details</h2>
          </div>

          <form method="POST" action="{{ route('account.profile') }}" class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-7 shadow-2xs space-y-4">
            @csrf @method('PUT')
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name <span class="text-red-500">*</span></label>
                <input name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/50 focus:outline-none transition-all" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
                <input name="phone" value="{{ old('phone', $user->phone) }}" placeholder="e.g. 01700000000" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/50 focus:outline-none transition-all font-mono" />
              </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end">
              <button type="submit" class="btn-shine rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm px-6 py-2.5 shadow-2xs transition-all cursor-pointer">
                Save Profile Changes
              </button>
            </div>
          </form>
        </section>

        {{-- SECTION 6: CHANGE PASSWORD --}}
        <section id="password" class="space-y-4 scroll-mt-24">
          <div class="flex items-center gap-2.5">
            <span class="w-1.5 h-5 bg-brand-600 rounded-full"></span>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Change Password</h2>
          </div>

          <form method="POST" action="{{ route('account.password') }}" class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-7 shadow-2xs space-y-4">
            @csrf @method('PUT')
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Current Password <span class="text-red-500">*</span></label>
                <input type="password" name="current_password" required placeholder="••••••••" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/50 focus:outline-none transition-all" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">New Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/50 focus:outline-none transition-all" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm New Password <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/50 focus:outline-none transition-all" />
              </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end">
              <button type="submit" class="btn-shine rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm px-6 py-2.5 shadow-2xs transition-all cursor-pointer">
                Update Password
              </button>
            </div>
          </form>
        </section>

      </div>
    </div>
  </div>
</main>
@endsection
