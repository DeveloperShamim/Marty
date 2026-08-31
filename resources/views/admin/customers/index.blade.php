@extends('layouts.admin')
@section('title', 'Customer CRM & Insights')

@section('content')
<div class="space-y-5 sm:space-y-6 max-w-full">

  {{-- Header & Actions Ribbon --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs">
    <div>
      <div class="flex items-center gap-2 flex-wrap">
        <h1 class="text-base sm:text-xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
          <span>👥</span> Customer Relationship Hub
        </h1>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-stone-100 text-stone-700 border border-stone-200">
          {{ number_format($totalCustomersCount) }} Total Customers
        </span>
      </div>
      <p class="text-xs text-stone-500 mt-1">
        Track customer lifetime value (LTV), assign custom VIP / Wholesale segment tags, and manage customer communications.
      </p>
    </div>

    <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
      <a href="{{ route('admin.customers.export', request()->query()) }}" class="px-4 py-2.5 rounded-xl bg-stone-50 hover:bg-stone-100 text-stone-800 border border-stone-200 font-extrabold text-xs shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        <span>Export CSV</span>
      </a>
      <a href="{{ route('admin.orders.index') }}" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
        <span>📦 View Orders</span>
      </a>
    </div>
  </div>

  @if(session('status'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-extrabold px-4 py-3 shadow-2xs flex items-center gap-2">
      <span>✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  {{-- CRM Quick KPI Metric Cards Ribbon --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 sm:gap-4">
    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-stone-500 uppercase tracking-wider block">Total Customer Base</span>
      <p class="text-xl sm:text-2xl font-black text-stone-900 font-mono tracking-tight">{{ number_format($totalCustomersCount) }}</p>
      <span class="text-[10px] text-stone-400 font-semibold block">Unique buyers</span>
    </div>

    <div class="p-4 rounded-2xl sm:rounded-3xl bg-gradient-to-br from-emerald-50/80 via-white to-white border border-emerald-200/80 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-emerald-800 uppercase tracking-wider block">Lifetime Spent</span>
      <p class="text-xl sm:text-2xl font-black text-emerald-700 font-mono tracking-tight">{{ money($totalLifetimeRevenue) }}</p>
      <span class="text-[10px] text-emerald-600 font-semibold block">Across all orders</span>
    </div>

    <div class="p-4 rounded-2xl sm:rounded-3xl bg-gradient-to-br from-amber-50/80 via-white to-white border border-amber-200/80 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-amber-900 uppercase tracking-wider block">VIP Spenders</span>
      <p class="text-xl sm:text-2xl font-black text-amber-800 font-mono tracking-tight">{{ number_format($vipCount) }}</p>
      <span class="text-[10px] text-amber-700 font-semibold block">Tagged VIP by admin</span>
    </div>

    <div class="p-4 rounded-2xl sm:rounded-3xl bg-gradient-to-br from-indigo-50/80 via-white to-white border border-indigo-200/80 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-indigo-900 uppercase tracking-wider block">Repeat Buyers</span>
      <p class="text-xl sm:text-2xl font-black text-indigo-800 font-mono tracking-tight">{{ number_format($repeatCustomersCount) }}</p>
      <span class="text-[10px] text-indigo-600 font-semibold block">2+ Completed orders</span>
    </div>
  </div>

  {{-- Filters, Search & Segment Tabs Card --}}
  <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-stone-100 pb-3.5">
      
      {{-- Segment Tabs --}}
      <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 md:pb-0">
        <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['tab' => 'all'])) }}" class="px-3 py-1.5 text-xs font-black rounded-xl transition whitespace-nowrap {{ $tab === 'all' ? 'bg-stone-900 text-white shadow-2xs' : 'bg-stone-50 text-stone-600 hover:bg-stone-100' }}">
          All Customers
        </a>
        <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['tab' => 'vip'])) }}" class="px-3 py-1.5 text-xs font-black rounded-xl transition whitespace-nowrap {{ $tab === 'vip' ? 'bg-amber-500 text-white shadow-2xs' : 'bg-amber-50 text-amber-900 hover:bg-amber-100' }}">
          🥇 VIP Spenders
        </a>
        <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['tab' => 'repeat'])) }}" class="px-3 py-1.5 text-xs font-black rounded-xl transition whitespace-nowrap {{ $tab === 'repeat' ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-indigo-50 text-indigo-900 hover:bg-indigo-100' }}">
          🔁 Repeat Buyers
        </a>
        <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['tab' => 'new'])) }}" class="px-3 py-1.5 text-xs font-black rounded-xl transition whitespace-nowrap {{ $tab === 'new' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-emerald-50 text-emerald-900 hover:bg-emerald-100' }}">
          ✨ First-Time Buyers
        </a>
        <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['tab' => 'blacklisted'])) }}" class="px-3 py-1.5 text-xs font-black rounded-xl transition whitespace-nowrap {{ $tab === 'blacklisted' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-rose-50 text-rose-900 hover:bg-rose-100' }}">
          🚫 Blacklisted
        </a>
      </div>

      {{-- Search & Sort --}}
      <form method="GET" action="{{ route('admin.customers.index') }}" class="flex items-center gap-2 flex-wrap sm:flex-nowrap w-full md:w-auto">
        <input type="hidden" name="tab" value="{{ $tab }}">
        
        <div class="relative flex-1 sm:w-64">
          <input type="text" name="q" value="{{ $term }}" placeholder="Search customer name, phone, city..." class="w-full pl-8 pr-3 py-2 text-xs font-semibold bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500" />
          <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <select name="sort" onchange="this.form.submit()" class="text-xs font-bold bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-stone-800 focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer shadow-2xs">
          <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Recent Activity</option>
          <option value="spent_desc" {{ $sort === 'spent_desc' ? 'selected' : '' }}>Highest Spent (LTV)</option>
          <option value="orders_desc" {{ $sort === 'orders_desc' ? 'selected' : '' }}>Most Orders</option>
          <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
        </select>

        <button type="submit" class="px-3.5 py-2 rounded-xl bg-stone-900 text-white font-extrabold text-xs hover:bg-stone-800 transition cursor-pointer shadow-2xs">
          Search
        </button>
      </form>
    </div>

    {{-- Desktop Table View (`hidden md:block`) --}}
    <div class="hidden md:block overflow-x-auto rounded-xl border border-stone-200">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-stone-100/90 text-stone-700 font-black border-b border-stone-200 uppercase text-[11px] tracking-wider whitespace-nowrap">
            <th class="py-3 px-4">Customer</th>
            <th class="py-3 px-4">Contact &amp; Location</th>
            <th class="py-3 px-4 text-center">Segment Tag (Admin Selectable)</th>
            <th class="py-3 px-4 text-center">Orders Placed</th>
            <th class="py-3 px-4 text-right">Lifetime Spent (LTV)</th>
            <th class="py-3 px-4 text-right">Last Order</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 bg-white">
          @forelse($customers as $c)
            @php
              $cleanPhone = preg_replace('/[^0-9]/', '', $c->customer_phone);
              if (str_starts_with($cleanPhone, '0')) {
                $waPhone = '88' . $cleanPhone;
              } elseif (str_starts_with($cleanPhone, '880')) {
                $waPhone = $cleanPhone;
              } else {
                $waPhone = '880' . $cleanPhone;
              }
            @endphp
            <tr class="hover:bg-stone-50/80 transition-colors">
              <td class="py-3.5 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl font-black text-xs flex items-center justify-center shrink-0 border {{ $c->tag === 'VIP' ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-stone-100 text-stone-700 border-stone-200' }}">
                    {{ strtoupper(substr($c->customer_name ?: 'C', 0, 2)) }}
                  </div>
                  <div class="min-w-0">
                    <a href="{{ route('admin.customers.show', $c->customer_phone) }}" class="font-extrabold text-stone-900 text-xs sm:text-sm hover:text-brand-600 block truncate">
                      {{ $c->customer_name ?: 'Valued Customer' }}
                    </a>
                    @if($c->is_blacklisted)
                      <span class="inline-flex items-center gap-1 text-[10px] font-black text-rose-600 mt-0.5">
                        <span>🚫 Blacklisted</span>
                      </span>
                    @endif
                  </div>
                </div>
              </td>

              <td class="py-3.5 px-4">
                <div class="space-y-0.5">
                  <p class="font-mono font-bold text-stone-800 text-xs">{{ $c->customer_phone }}</p>
                  @if($c->city)
                    <p class="text-[11px] text-stone-400 font-medium">📍 {{ $c->city }}</p>
                  @endif
                </div>
              </td>

              {{-- Segment Tag Dropdown Selector Form --}}
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                <form method="POST" action="{{ route('admin.customers.update-segment-tag', $c->customer_phone) }}" class="inline-block">
                  @csrf
                  <select name="segment_tag" onchange="this.form.submit()" class="text-[11px] font-black rounded-lg px-2.5 py-1 border transition-all cursor-pointer shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-500 {{ $c->tag === 'VIP' ? 'bg-amber-100 text-amber-950 border-amber-300' : ($c->tag === 'Wholesale' ? 'bg-purple-100 text-purple-950 border-purple-300' : ($c->tag === 'Loyal' ? 'bg-sky-100 text-sky-950 border-sky-300' : ($c->tag === 'Risk' ? 'bg-rose-100 text-rose-950 border-rose-300' : ($c->tag === 'Influencer' ? 'bg-pink-100 text-pink-950 border-pink-300' : ($c->tag === 'Repeat Buyer' ? 'bg-indigo-50 text-indigo-900 border-indigo-200' : 'bg-emerald-50 text-emerald-900 border-emerald-200'))))) }}">
                    <optgroup label="Custom Tag (Admin Selected)">
                      <option value="VIP" {{ ($c->admin_tag === 'VIP') ? 'selected' : '' }}>🥇 VIP Customer</option>
                      <option value="Wholesale" {{ ($c->admin_tag === 'Wholesale') ? 'selected' : '' }}>📦 Wholesale / Bulk</option>
                      <option value="Loyal" {{ ($c->admin_tag === 'Loyal') ? 'selected' : '' }}>🌟 Loyal Client</option>
                      <option value="Influencer" {{ ($c->admin_tag === 'Influencer') ? 'selected' : '' }}>🎬 Influencer / Partner</option>
                      <option value="Risk" {{ ($c->admin_tag === 'Risk') ? 'selected' : '' }}>⚠️ Return Risk</option>
                    </optgroup>
                    <optgroup label="Automatic Default">
                      <option value="auto" {{ empty($c->admin_tag) ? 'selected' : '' }}>
                        Auto ({{ $c->orders_count >= 2 ? 'Repeat Buyer' : 'New Customer' }})
                      </option>
                    </optgroup>
                  </select>
                </form>
              </td>

              <td class="py-3.5 px-4 text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-stone-100 text-stone-800 border border-stone-200 font-mono">
                  {{ number_format($c->orders_count) }} orders
                </span>
                @if($c->delivered_count > 0)
                  <span class="block text-[10px] text-emerald-700 font-bold mt-0.5 font-mono">{{ $c->delivered_count }} delivered</span>
                @endif
              </td>

              <td class="py-3.5 px-4 text-right font-black text-emerald-700 font-mono text-sm">
                {{ money($c->total_spent) }}
              </td>

              <td class="py-3.5 px-4 text-right text-stone-500 font-medium whitespace-nowrap text-xs">
                {{ \Illuminate\Support\Carbon::parse($c->last_order_at)->format('d M, Y') }}
              </td>

              <td class="py-3.5 px-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="h-8 w-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200/90 hover:bg-emerald-100 hover:scale-105 transition shadow-2xs flex items-center justify-center cursor-pointer shrink-0" title="Chat with {{ $c->customer_name ?: 'Customer' }} on WhatsApp">
                    <svg class="w-4 h-4 fill-current text-emerald-700" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm0 18.15c-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.216 8.216 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c.01 4.54-3.68 8.23-8.22 8.23zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.15.17-.25.25-.42.08-.17.04-.31-.02-.43s-.56-1.34-.76-1.84c-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.44.06-.66.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.43 1.03 2.6.12.17 1.78 2.71 4.3 3.8.6.26 1.07.41 1.44.53.61.19 1.16.17 1.6-.07.49-.26 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.07-.12-.23-.19-.48-.31z"/></svg>
                  </a>
                  <a href="tel:{{ $c->customer_phone }}" class="h-8 w-8 rounded-xl bg-stone-50 text-stone-700 border border-stone-200 hover:bg-stone-100 hover:scale-105 transition shadow-2xs flex items-center justify-center cursor-pointer shrink-0" title="Call {{ $c->customer_phone }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                  </a>
                  <a href="{{ route('admin.customers.show', $c->customer_phone) }}" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-stone-50 hover:bg-stone-100 text-stone-800 border border-stone-200 transition shadow-2xs">
                    Profile &rarr;
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-12 text-center text-stone-400 text-xs italic bg-stone-50/50">
                👥 No customers match your filter or search query.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile Cards View (`block md:hidden`) --}}
    <div class="block md:hidden divide-y divide-stone-100 bg-white">
      @forelse($customers as $c)
        @php
          $cleanPhone = preg_replace('/[^0-9]/', '', $c->customer_phone);
          if (str_starts_with($cleanPhone, '0')) {
            $waPhone = '88' . $cleanPhone;
          } elseif (str_starts_with($cleanPhone, '880')) {
            $waPhone = $cleanPhone;
          } else {
            $waPhone = '880' . $cleanPhone;
          }
        @endphp
        <div class="p-4 space-y-3">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl font-black text-xs flex items-center justify-center shrink-0 border {{ $c->tag === 'VIP' ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-stone-100 text-stone-700 border-stone-200' }}">
                {{ strtoupper(substr($c->customer_name ?: 'C', 0, 2)) }}
              </div>
              <div class="min-w-0">
                <a href="{{ route('admin.customers.show', $c->customer_phone) }}" class="font-extrabold text-sm text-stone-900 hover:text-brand-600 block truncate">
                  {{ $c->customer_name ?: 'Valued Customer' }}
                </a>
                <p class="text-xs font-mono font-bold text-stone-700 mt-0.5">{{ $c->customer_phone }}</p>
                @if($c->city)
                  <p class="text-[11px] text-stone-400">📍 {{ $c->city }}</p>
                @endif
              </div>
            </div>

            <div class="text-right shrink-0">
              <p class="text-sm font-black text-emerald-700 font-mono">{{ money($c->total_spent) }}</p>
              <span class="text-[10px] text-stone-400 font-mono font-bold block">{{ $c->orders_count }} orders</span>
            </div>
          </div>

          {{-- Mobile Segment Tag & Quick Switcher --}}
          <div class="flex items-center justify-between gap-2 p-2 bg-stone-50 rounded-xl border border-stone-200/80 text-xs">
            <span class="text-[11px] font-bold text-stone-500">Segment Tag:</span>
            <form method="POST" action="{{ route('admin.customers.update-segment-tag', $c->customer_phone) }}" class="inline-block">
              @csrf
              <select name="segment_tag" onchange="this.form.submit()" class="text-[11px] font-black rounded-lg px-2 py-0.5 border cursor-pointer {{ $c->tag === 'VIP' ? 'bg-amber-100 text-amber-950 border-amber-300' : ($c->tag === 'Wholesale' ? 'bg-purple-100 text-purple-950 border-purple-300' : ($c->tag === 'Loyal' ? 'bg-sky-100 text-sky-950 border-sky-300' : ($c->tag === 'Risk' ? 'bg-rose-100 text-rose-950 border-rose-300' : ($c->tag === 'Repeat Buyer' ? 'bg-indigo-50 text-indigo-900 border-indigo-200' : 'bg-emerald-50 text-emerald-900 border-emerald-200')))) }}">
                <optgroup label="Admin Assigned">
                  <option value="VIP" {{ ($c->admin_tag === 'VIP') ? 'selected' : '' }}>🥇 VIP</option>
                  <option value="Wholesale" {{ ($c->admin_tag === 'Wholesale') ? 'selected' : '' }}>📦 Wholesale</option>
                  <option value="Loyal" {{ ($c->admin_tag === 'Loyal') ? 'selected' : '' }}>🌟 Loyal</option>
                  <option value="Influencer" {{ ($c->admin_tag === 'Influencer') ? 'selected' : '' }}>🎬 Influencer</option>
                  <option value="Risk" {{ ($c->admin_tag === 'Risk') ? 'selected' : '' }}>⚠️ Return Risk</option>
                </optgroup>
                <optgroup label="Auto Fallback">
                  <option value="auto" {{ empty($c->admin_tag) ? 'selected' : '' }}>
                    Auto ({{ $c->orders_count >= 2 ? 'Repeat' : 'New' }})
                  </option>
                </optgroup>
              </select>
            </form>
          </div>

          <div class="flex items-center justify-between gap-2 pt-1">
            <div class="flex items-center gap-1.5">
              <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold flex items-center gap-1">
                <span>💬 WhatsApp</span>
              </a>
              <a href="tel:{{ $c->customer_phone }}" class="px-3 py-1.5 rounded-xl bg-stone-50 text-stone-800 border border-stone-200 text-xs font-bold flex items-center gap-1">
                <span>📞 Call</span>
              </a>
            </div>

            <a href="{{ route('admin.customers.show', $c->customer_phone) }}" class="px-4 py-1.5 text-xs font-bold rounded-xl bg-stone-900 text-white shadow-2xs">
              Profile &rarr;
            </a>
          </div>
        </div>
      @empty
        <div class="p-8 text-center text-xs text-stone-400 bg-stone-50">
          No customers found.
        </div>
      @endforelse
    </div>

    @if($customers->hasPages())
      <div class="p-4 border-t border-stone-100 bg-stone-50/40">{{ $customers->links() }}</div>
    @endif
  </div>

</div>
@endsection
