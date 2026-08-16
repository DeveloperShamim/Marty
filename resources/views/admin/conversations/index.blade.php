@extends('layouts.admin')
@section('title', 'Live Support Chat Inbox')

@section('content')
<div class="space-y-4 sm:space-y-6">
  {{-- Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 pb-3 sm:pb-4">
    <div>
      <h2 class="text-xl sm:text-2xl font-extrabold text-ink tracking-tight flex items-center gap-2">
        <span>💬</span> Live Customer Support Chat
      </h2>
      <p class="text-xs text-gray-500 mt-0.5">Manage real-time customer inquiries, voice notes, product recommendations, and order tracking.</p>
    </div>
    
    <div class="flex items-center gap-1.5 flex-wrap">
      <a href="{{ route('admin.conversations.index', ['status' => 'open']) }}" class="px-3 py-1.5 text-xs font-bold rounded-xl {{ $status === 'open' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-gray-700 border border-gray-200' }}">
        Open ({{ \App\Models\Conversation::where('status', 'open')->count() }})
      </a>
      <a href="{{ route('admin.conversations.index', ['status' => 'closed']) }}" class="px-3 py-1.5 text-xs font-bold rounded-xl {{ $status === 'closed' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-gray-700 border border-gray-200' }}">
        Closed
      </a>
      <a href="{{ route('admin.conversations.index', ['status' => 'all']) }}" class="px-3 py-1.5 text-xs font-bold rounded-xl {{ $status === 'all' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-gray-700 border border-gray-200' }}">
        All
      </a>
      <button type="button" onclick="document.getElementById('storageCleanupModal').classList.remove('hidden')" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition flex items-center gap-1 cursor-pointer">
        <span>🧹</span> Clean
      </button>
    </div>
  </div>

  {{-- Main Inbox Grid --}}
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6 h-[calc(100vh-200px)] min-h-[560px] lg:h-[740px] max-h-[86vh]">
    
    {{-- Left Panel: Conversations List --}}
    <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-200 {{ $hasExplicitChat ? 'hidden lg:flex' : 'flex' }} flex-col overflow-hidden shadow-xs h-full">
      {{-- Search --}}
      <div class="p-3 border-b border-gray-100 bg-gray-50/50">
        <form method="GET" action="{{ route('admin.conversations.index') }}">
          <input type="hidden" name="status" value="{{ $status }}" />
          <input type="text" name="q" value="{{ $search }}" placeholder="Search customer, phone..." class="inp text-xs w-full bg-white shadow-2xs" />
        </form>
      </div>

      {{-- List --}}
      <div class="flex-1 overflow-y-auto divide-y divide-gray-100 no-scrollbar">
        @forelse($conversations as $conv)
          @php $isActive = $hasExplicitChat && $activeConversation && $activeConversation->id === $conv->id; @endphp
          <a href="{{ route('admin.conversations.index', ['status' => $status, 'chat' => $conv->id, 'q' => $search]) }}" class="block p-3.5 hover:bg-slate-50 transition {{ $isActive ? 'bg-brand-50/60 border-l-4 border-brand-600' : '' }}">
            <div class="flex items-center justify-between mb-1">
              <span class="font-extrabold text-xs text-ink truncate max-w-[140px]">{{ $conv->customer_name }}</span>
              <span class="text-[10px] font-semibold text-gray-400 shrink-0">{{ $conv->last_message_at ? $conv->last_message_at->diffForHumans(null, true) : '' }}</span>
            </div>

            <div class="flex items-center justify-between gap-2">
              <p class="text-xs text-gray-500 truncate max-w-[160px]">
                {{ $conv->lastMessage ? $conv->lastMessage->message : 'No messages yet' }}
              </p>
              @if($conv->unread_admin_count > 0)
                <span class="px-2 py-0.5 text-[10px] font-extrabold bg-emerald-600 text-white rounded-full shrink-0 shadow-2xs">
                  {{ $conv->unread_admin_count }}
                </span>
              @endif
            </div>

            @if($conv->customer_phone || $conv->customer_email)
              <div class="mt-1 text-[10px] font-medium text-gray-400 flex items-center gap-2">
                @if($conv->customer_phone) <span>📞 {{ $conv->customer_phone }}</span> @endif
                @if($conv->customer_email) <span class="truncate">✉️ {{ $conv->customer_email }}</span> @endif
              </div>
            @endif
          </a>
        @empty
          <div class="p-8 text-center text-xs text-gray-400 space-y-2">
            <span class="text-2xl block">💬</span>
            <p class="font-bold text-gray-600">No conversations found</p>
          </div>
        @endforelse
      </div>

      @if($conversations->hasPages())
        <div class="p-2 border-t border-gray-100 text-xs">
          {{ $conversations->links() }}
        </div>
      @endif
    </div>

    {{-- Center Panel: Active Chat Thread --}}
    <div class="lg:col-span-6 bg-white rounded-2xl border border-gray-200 {{ $hasExplicitChat ? 'flex' : 'hidden lg:flex' }} flex-col overflow-hidden shadow-xs h-full">
      @if($activeConversation)
        {{-- Thread Header --}}
        <div class="p-3 sm:p-3.5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50 gap-2">
          <div class="flex items-center gap-2 min-w-0">
            {{-- Mobile Back Button --}}
            <a href="{{ route('admin.conversations.index', ['status' => $status, 'q' => $search]) }}" class="lg:hidden p-1.5 rounded-xl bg-white border border-gray-200 text-stone-700 font-extrabold text-xs hover:bg-gray-100 shrink-0 flex items-center gap-1 shadow-2xs" title="Back to List">
              <span>←</span> <span class="hidden sm:inline">Chats</span>
            </a>

            <div class="h-8 w-8 sm:h-9 sm:w-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-extrabold text-xs border border-brand-200 shrink-0">
              {{ strtoupper(substr($activeConversation->customer_name, 0, 1)) }}
            </div>

            <div class="min-w-0">
              <h3 class="font-extrabold text-xs text-ink flex items-center gap-1.5 truncate">
                <span class="truncate">{{ $activeConversation->customer_name }}</span>
                @if($activeConversation->status === 'open')
                  <span class="px-2 py-0.5 text-[9px] font-extrabold bg-emerald-100 text-emerald-800 rounded-full border border-emerald-200 shrink-0">Open</span>
                @else
                  <span class="px-2 py-0.5 text-[9px] font-extrabold bg-gray-100 text-gray-600 rounded-full border border-gray-200 shrink-0">Closed</span>
                @endif
              </h3>
              <p class="text-[10px] text-gray-400 mt-0.5 truncate">
                @if($activeConversation->customer_phone) <span>📞 {{ $activeConversation->customer_phone }}</span> @endif
                @if($activeConversation->customer_email) <span class="hidden sm:inline">| ✉️ {{ $activeConversation->customer_email }}</span> @endif
              </p>
            </div>
          </div>

          <div class="flex items-center gap-1.5 shrink-0">
            {{-- Mobile Customer Info Toggle --}}
            <button type="button" onclick="openMobileCustomerInfoModal()" class="lg:hidden px-2.5 py-1.5 text-xs font-extrabold rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 border border-stone-200 transition shadow-2xs cursor-pointer flex items-center gap-1">
              <span>👤 Info</span>
            </button>

            <form method="POST" action="{{ route('admin.conversations.toggle-status', $activeConversation) }}">
              @csrf
              @if($activeConversation->status === 'open')
                <button type="submit" class="px-2.5 sm:px-3 py-1.5 text-xs font-extrabold rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition shadow-2xs cursor-pointer flex items-center gap-1">
                  <span>🔒</span> <span class="hidden sm:inline">Close Chat</span><span class="sm:hidden">Close</span>
                </button>
              @else
                <button type="submit" class="px-2.5 sm:px-3 py-1.5 text-xs font-extrabold rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition shadow-2xs cursor-pointer flex items-center gap-1">
                  <span>🔓</span> <span class="hidden sm:inline">Re-open Chat</span><span class="sm:hidden">Open</span>
                </button>
              @endif
            </form>
          </div>
        </div>

        {{-- Quick Action & Reply Toolbar --}}
        <div class="px-3 py-2 border-b border-gray-100 bg-slate-50/80 flex items-center gap-2 overflow-x-auto no-scrollbar">
          <button type="button" onclick="openProductPickerModal()" class="px-2.5 py-1 text-[11px] font-extrabold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition shrink-0 cursor-pointer flex items-center gap-1 shadow-2xs">
            <span>🛍️ Attach Product</span>
          </button>

          <button type="button" onclick="openCouponPickerModal()" class="px-2.5 py-1 text-[11px] font-extrabold bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition shrink-0 cursor-pointer flex items-center gap-1 shadow-2xs">
            <span>🎟️ Send Coupon</span>
          </button>

          <span class="text-gray-300 text-xs">|</span>
          <button type="button" onclick="insertQuickReply('Hello! How can I assist you today?')" class="px-2.5 py-1 text-[11px] font-semibold bg-white border border-gray-200 rounded-lg hover:border-brand-500 hover:text-brand-600 transition shrink-0 cursor-pointer">👋 Greeting</button>
          <button type="button" onclick="insertQuickReply('Yes, this product is in stock and ready for fast delivery!')" class="px-2.5 py-1 text-[11px] font-semibold bg-white border border-gray-200 rounded-lg hover:border-brand-500 hover:text-brand-600 transition shrink-0 cursor-pointer">📦 Stock Info</button>
        </div>

        {{-- Thread Messages List --}}
        <div id="adminMessageList" class="flex-1 p-3.5 sm:p-5 overflow-y-auto space-y-4 bg-stone-50/60 no-scrollbar">
          @forelse($activeConversation->messages as $msg)
            @php $isAdmin = $msg->sender_type === 'admin'; @endphp
            <div class="flex flex-col {{ $isAdmin ? 'items-end' : 'items-start' }} group/msg">
              
              @if($msg->type === 'product' && $msg->metadata)
                @php $meta = $msg->metadata; @endphp
                <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl p-3.5 bg-white border border-stone-200/90 shadow-sm text-stone-800 space-y-2.5">
                  <div class="flex items-center gap-3">
                    <img src="{{ $meta['image_url'] ?? logo_url() }}" class="h-12 w-12 object-cover rounded-xl border border-stone-100 bg-stone-50 shrink-0" alt="" />
                    <div class="min-w-0">
                      <span class="text-[9px] font-extrabold text-indigo-600 uppercase tracking-wider block">Product Recommendation</span>
                      <h5 class="font-extrabold text-xs text-ink truncate">{{ $meta['name'] ?? '' }}</h5>
                      <p class="text-xs font-black text-brand-600 mt-0.5">{{ $meta['formatted_price'] ?? money($meta['price'] ?? 0) }}</p>
                    </div>
                  </div>
                  <a href="{{ $meta['url'] ?? '#' }}" target="_blank" class="block w-full py-1.5 text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-extrabold text-xs rounded-xl transition">View Product ↗</a>
                </div>

              @elseif($msg->type === 'order' && $msg->metadata)
                @php $meta = $msg->metadata; @endphp
                <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl p-3.5 bg-white border border-stone-200/90 shadow-sm text-stone-800 space-y-2.5">
                  <div class="border-b border-stone-100 pb-2 flex items-center justify-between gap-2">
                    <span class="font-extrabold text-xs text-stone-900 truncate">Order {{ $meta['order_number'] ?? '' }}</span>
                    <span class="px-2.5 py-0.5 text-[9px] font-extrabold bg-emerald-100 text-emerald-800 rounded-full shrink-0">{{ $meta['delivery_status'] ?? '' }}</span>
                  </div>
                  @if(!empty($meta['items_summary']))
                    <p class="text-xs font-medium text-stone-700">📦 {{ $meta['items_summary'] }}</p>
                  @endif
                  <p class="text-xs text-stone-600">Total: <strong class="text-stone-900">{{ $meta['formatted_total'] ?? '' }}</strong> ({{ $meta['items_count'] ?? 1 }} items)</p>
                  <a href="{{ $meta['tracking_url'] ?? '#' }}" target="_blank" class="block w-full py-1.5 text-center bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-extrabold text-xs rounded-xl transition">Tracking Link ↗</a>
                </div>

              @elseif($msg->type === 'coupon' && $msg->metadata)
                @php $meta = $msg->metadata; @endphp
                <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl p-3.5 bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200/90 shadow-sm text-amber-950 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-[9.5px] font-extrabold text-amber-800 uppercase tracking-wider">🎟️ Discount Coupon Card</span>
                    <span class="font-black text-xs text-amber-900 bg-white/80 px-2 py-0.5 rounded-full border border-amber-200">{{ $meta['discount_text'] ?? '' }}</span>
                  </div>
                  <div class="bg-white p-2 rounded-xl border border-dashed border-amber-300 font-mono text-xs font-black text-center tracking-widest text-amber-900 shadow-2xs">
                    CODE: {{ $meta['code'] ?? '' }}
                  </div>
                </div>

              @elseif($msg->type === 'voice' && $msg->attachment_url)
                <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl p-3 bg-white border border-stone-200/80 shadow-xs text-stone-800 space-y-1.5">
                  <div class="flex items-center gap-1.5 text-xs font-bold text-brand-600">
                    <span>🎙️ Voice Note</span>
                  </div>
                  <audio controls class="w-56 sm:w-64 h-8 rounded-lg max-w-full">
                    <source src="{{ $msg->attachment_url }}" type="audio/webm">
                    <source src="{{ $msg->attachment_url }}" type="audio/mp3">
                  </audio>
                </div>

              @elseif($msg->type === 'image' || $msg->attachment_url)
                <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl p-1 bg-white border border-stone-200/80 shadow-xs overflow-hidden">
                  <a href="{{ $msg->attachment_url }}" target="_blank">
                    <img src="{{ $msg->attachment_url }}" class="max-h-52 w-full object-cover rounded-xl" alt="Attachment" />
                  </a>
                </div>

              @else
                <div class="flex items-end gap-2 max-w-[85%] sm:max-w-[75%]">
                  @if(!$isAdmin)
                    <div class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-[10px] font-extrabold shrink-0 border border-brand-200">
                      {{ strtoupper(substr($activeConversation->customer_name, 0, 1)) }}
                    </div>
                  @endif
                  <div class="rounded-2xl px-4 py-2.5 text-xs sm:text-sm font-medium leading-relaxed shadow-2xs {{ $isAdmin ? 'bg-brand-600 text-white rounded-br-xs' : 'bg-white text-stone-800 border border-stone-200/90 rounded-bl-xs' }}">
                    {{ $msg->message }}
                  </div>
                </div>
              @endif

              <div class="text-[9.5px] font-bold text-stone-400 mt-1 px-1 flex items-center gap-1">
                <span>{{ $isAdmin ? 'Support Agent' : $activeConversation->customer_name }}</span>
                <span>•</span>
                <span>{{ $msg->created_at->format('h:i A') }}</span>
                @if($isAdmin)
                  <span class="text-brand-600 font-extrabold ml-0.5">✓✓</span>
                @endif
              </div>
            </div>
          @empty
            <div class="text-center text-xs text-stone-400 py-12">No message history available.</div>
          @endforelse
        </div>

        {{-- Audio Recording Overlay Bar --}}
        <div id="adminRecordingOverlay" class="hidden p-2.5 bg-rose-50 border-t border-rose-100 flex items-center justify-between gap-2 animate-pulse">
          <div class="flex items-center gap-2 text-rose-700 text-xs font-extrabold">
            <span class="w-2.5 h-2.5 bg-rose-600 rounded-full animate-ping"></span>
            <span class="truncate">Recording Voice Note...</span>
            <span id="adminRecordTimer" class="font-mono text-rose-900 shrink-0">00:00</span>
          </div>
          <div class="flex items-center gap-1.5 shrink-0">
            <button type="button" id="adminCancelRecordBtn" class="px-2 py-1 bg-white border border-rose-200 text-rose-700 font-bold text-[10px] rounded-lg">Cancel</button>
            <button type="button" id="adminStopSendRecordBtn" class="px-2.5 py-1 bg-rose-600 text-white font-extrabold text-[10px] rounded-lg shadow-2xs">Send ➔</button>
          </div>
        </div>

        {{-- Admin Reply Form --}}
        <form method="POST" action="{{ route('admin.conversations.reply', $activeConversation) }}" enctype="multipart/form-data" class="p-3 bg-white border-t border-stone-100 flex items-center gap-2">
          @csrf
          <div class="flex items-center gap-1.5 shrink-0">
            <label for="adminAttachmentInput" class="h-9 w-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-600 flex items-center justify-center cursor-pointer transition shadow-2xs" title="Attach Photo">
              📷
              <input type="file" id="adminAttachmentInput" accept="image/*" class="hidden" onchange="uploadAdminPhotoAttachment()" />
            </label>

            <button type="button" id="adminMicBtn" class="h-9 w-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-600 flex items-center justify-center cursor-pointer transition shadow-2xs" title="Record Voice Note">
              🎙️
            </button>
          </div>

          <div class="flex-1 flex items-center bg-stone-100/90 border border-stone-200/90 rounded-full px-3.5 py-1 focus-within:bg-white focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/10 transition">
            <textarea id="adminReplyText" name="message" rows="1" placeholder="Type a message..." class="w-full text-xs sm:text-sm bg-transparent focus:outline-none text-stone-800 placeholder:text-stone-400 resize-none h-8 leading-snug py-1.5" required></textarea>
          </div>
          
          <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-extrabold px-4 py-2 rounded-full shadow-sm flex items-center gap-1.5 shrink-0 transition cursor-pointer">
            <span>Reply</span>
            <span>➔</span>
          </button>
        </form>

      @else
        <div class="flex flex-col items-center justify-center h-full text-center text-gray-400 p-8 space-y-2">
          <span class="text-4xl">💬</span>
          <h4 class="font-extrabold text-sm text-gray-600">Select a Conversation</h4>
        </div>
      @endif
    </div>

    {{-- Right Sidebar: Customer Context & Past Orders (Desktop Only) --}}
    <div class="hidden lg:flex lg:col-span-3 bg-white rounded-2xl border border-gray-200 flex-col overflow-hidden shadow-xs h-full">
      @if($activeConversation)
        <div class="p-3.5 border-b border-gray-100 bg-gray-50/50 space-y-2">
          <h4 class="font-extrabold text-xs text-ink">
            👤 Customer Info
          </h4>
          <div class="text-[11px] text-gray-600 space-y-1">
            <p><strong>Name:</strong> {{ $activeConversation->customer_name }}</p>
            @if($activeConversation->customer_phone) <p><strong>Phone:</strong> {{ $activeConversation->customer_phone }}</p> @endif
            @if($activeConversation->customer_email) <p class="truncate"><strong>Email:</strong> {{ $activeConversation->customer_email }}</p> @endif
          </div>
        </div>

        <div class="p-3.5 border-b border-gray-100 bg-gray-50/30">
          <h4 class="font-extrabold text-xs text-ink flex items-center gap-1.5">
            <span>📦</span> Customer Orders ({{ $customerOrders->count() }})
          </h4>
          <p class="text-[10px] text-gray-400 mt-0.5">Quickly share past order status with customer</p>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-3 divide-y divide-gray-100 no-scrollbar">
          @forelse($customerOrders as $ord)
            @php $ordPrice = $ord->total > 0 ? $ord->total : $ord->grand_total; @endphp
            <div class="pt-3 first:pt-0 space-y-2">
              <div class="flex items-center justify-between gap-1">
                <a href="{{ route('admin.orders.show', $ord) }}" target="_blank" class="font-extrabold text-xs text-brand-600 hover:underline">
                  {{ $ord->order_number ?? '#' . $ord->id }}
                </a>
                
                {{-- Quick Status Change Selector --}}
                <form method="POST" action="{{ route('admin.conversations.orders.update-status', [$activeConversation, $ord]) }}" class="inline-block">
                  @csrf
                  <select name="status" onchange="this.form.submit()" class="text-[10px] font-bold rounded px-1.5 py-0.5 border border-gray-300 bg-white text-gray-800 focus:outline-none focus:border-brand-500 cursor-pointer">
                    <option value="pending" {{ $ord->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $ord->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="processing" {{ $ord->status === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="shipped" {{ $ord->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ $ord->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ $ord->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                  </select>
                </form>
              </div>

              {{-- Item Details --}}
              <div class="bg-gray-50 p-2 rounded-lg border border-gray-100 space-y-1">
                @foreach($ord->items as $item)
                  <div class="text-[11px] font-medium text-gray-700 flex items-center justify-between gap-2">
                    <span class="truncate max-w-[170px]" title="{{ $item->product_name }}">
                      📦 {{ $item->product_name ?: ($item->product?->name ?? 'Product') }}
                      @if($item->variant_label) <span class="text-gray-400">({{ $item->variant_label }})</span> @endif
                    </span>
                    <span class="font-bold shrink-0 text-gray-500">×{{ $item->quantity }}</span>
                  </div>
                @endforeach
              </div>

              <div class="flex items-center justify-between text-[11px] text-gray-500 pt-0.5">
                <span>{{ $ord->items->sum('quantity') }} items</span>
                <span class="font-black text-ink text-xs">{{ money($ordPrice) }}</span>
              </div>

              <form method="POST" action="{{ route('admin.conversations.send-order', $activeConversation) }}">
                @csrf
                <input type="hidden" name="order_id" value="{{ $ord->id }}" />
                <button type="submit" class="w-full py-1 text-[10px] font-extrabold bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded border border-emerald-200/60 transition cursor-pointer">
                  🧾 Send Order Card to Chat
                </button>
              </form>
            </div>
          @empty
            <div class="p-6 text-center text-xs text-gray-400 space-y-1">
              <p class="font-bold text-gray-500">No past orders found</p>
              <p class="text-[10px]">Customer has not placed orders under this phone/account yet.</p>
            </div>
          @endforelse
        </div>
      @else
        <div class="p-6 text-center text-xs text-gray-400">Select a chat to view customer details</div>
      @endif
    </div>

  </div>
</div>

{{-- Mobile Customer Info Modal --}}
@if($activeConversation)
<div id="mobileCustomerInfoModal" class="hidden fixed inset-0 bg-ink/50 backdrop-blur-xs z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
  <div class="bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-2xl border border-gray-100 overflow-hidden max-h-[85vh] flex flex-col p-4 space-y-3">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <h3 class="font-extrabold text-sm text-ink flex items-center gap-2">
        <span>👤</span> Customer Info & Orders
      </h3>
      <button type="button" onclick="closeMobileCustomerInfoModal()" class="text-gray-400 hover:text-gray-700 text-lg font-bold">×</button>
    </div>

    <div class="overflow-y-auto space-y-3 flex-1 no-scrollbar pr-1">
      <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 space-y-1 text-xs text-gray-700">
        <p><strong>Name:</strong> {{ $activeConversation->customer_name }}</p>
        @if($activeConversation->customer_phone) <p><strong>Phone:</strong> {{ $activeConversation->customer_phone }}</p> @endif
        @if($activeConversation->customer_email) <p class="truncate"><strong>Email:</strong> {{ $activeConversation->customer_email }}</p> @endif
      </div>

      <div class="border-t border-gray-100 pt-2 space-y-2">
        <h4 class="font-extrabold text-xs text-ink flex items-center gap-1.5">
          <span>📦</span> Past Orders ({{ $customerOrders->count() }})
        </h4>
        @forelse($customerOrders as $ord)
          @php $ordPrice = $ord->total > 0 ? $ord->total : $ord->grand_total; @endphp
          <div class="p-3 bg-white border border-gray-200 rounded-xl space-y-2 text-xs shadow-2xs">
            <div class="flex items-center justify-between">
              <a href="{{ route('admin.orders.show', $ord) }}" target="_blank" class="font-extrabold text-brand-600 hover:underline">
                {{ $ord->order_number ?? '#' . $ord->id }}
              </a>
              <span class="font-black text-ink">{{ money($ordPrice) }}</span>
            </div>
            <div class="bg-gray-50 p-2 rounded-lg border border-gray-100 space-y-1">
              @foreach($ord->items as $item)
                <div class="text-[11px] font-medium text-gray-700 flex items-center justify-between gap-2">
                  <span class="truncate" title="{{ $item->product_name }}">
                    📦 {{ $item->product_name ?: ($item->product?->name ?? 'Product') }}
                  </span>
                  <span class="font-bold shrink-0 text-gray-500">×{{ $item->quantity }}</span>
                </div>
              @endforeach
            </div>
            <form method="POST" action="{{ route('admin.conversations.send-order', $activeConversation) }}">
              @csrf
              <input type="hidden" name="order_id" value="{{ $ord->id }}" />
              <button type="submit" class="w-full py-1 text-[10px] font-extrabold bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded border border-emerald-200/60 transition cursor-pointer">
                🧾 Send Order Card to Chat
              </button>
            </form>
          </div>
        @empty
          <p class="text-xs text-gray-400 italic">No past orders found.</p>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endif

{{-- Product Picker Modal --}}
@if($activeConversation)
<div id="productPickerModal" class="hidden fixed inset-0 bg-ink/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
  <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-gray-100 overflow-hidden space-y-3 p-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <h3 class="font-extrabold text-sm text-ink flex items-center gap-2">
        <span>🛍️</span> Recommend Product to Customer
      </h3>
      <button type="button" onclick="closeProductPickerModal()" class="text-gray-400 hover:text-gray-700 text-lg font-bold">×</button>
    </div>

    <div>
      <input type="text" id="productSearchInput" placeholder="Type product name or SKU..." class="inp text-xs w-full" oninput="searchProductsForChat()" />
    </div>

    <div id="productSearchResults" class="max-h-60 overflow-y-auto space-y-2 border border-gray-100 rounded-xl p-2 bg-slate-50/50">
      <div class="p-4 text-center text-xs text-gray-400">Type above to search products...</div>
    </div>
  </div>
</div>

{{-- Coupon Picker Modal --}}
<div id="couponPickerModal" class="hidden fixed inset-0 bg-ink/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
  <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-gray-100 overflow-hidden space-y-3 p-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <h3 class="font-extrabold text-sm text-ink flex items-center gap-2">
        <span>🎟️</span> Send Discount Coupon Card
      </h3>
      <button type="button" onclick="closeCouponPickerModal()" class="text-gray-400 hover:text-gray-700 text-lg font-bold">×</button>
    </div>

    <div id="couponListResults" class="max-h-60 overflow-y-auto space-y-2 border border-gray-100 rounded-xl p-2 bg-slate-50/50">
      <div class="p-4 text-center text-xs text-gray-400">Loading active store coupons...</div>
    </div>
  </div>
</div>
@endif

@push('scripts')
<script>
function scrollChatToBottom() {
  const msgList = document.getElementById('adminMessageList');
  if (msgList) {
    msgList.scrollTop = msgList.scrollHeight;
  }
}

document.addEventListener('DOMContentLoaded', function() {
  scrollChatToBottom();

  const activeChatId = "{{ $activeConversation->id ?? '' }}";
  if (activeChatId) {
    setInterval(async function() {
      try {
        const res = await fetch(`/admin/conversations/${activeChatId}`, {
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (res.ok) {
          const data = await res.json();
          if (data && data.messages) {
            const msgList = document.getElementById('adminMessageList');
            if (msgList && data.messages.length > msgList.children.length) {
              window.location.reload();
            }
          }
        }
      } catch (e) {}
    }, 4000);
  }
});

function insertQuickReply(text) {
  const replyBox = document.getElementById('adminReplyText');
  if (replyBox) {
    replyBox.value = text;
    replyBox.focus();
  }
}

function openMobileCustomerInfoModal() {
  const modal = document.getElementById('mobileCustomerInfoModal');
  if (modal) modal.classList.remove('hidden');
}

function closeMobileCustomerInfoModal() {
  const modal = document.getElementById('mobileCustomerInfoModal');
  if (modal) modal.classList.add('hidden');
}

function openProductPickerModal() {
  const modal = document.getElementById('productPickerModal');
  if (modal) {
    modal.classList.remove('hidden');
    document.getElementById('productSearchInput')?.focus();
    searchProductsForChat();
  }
}

function closeProductPickerModal() {
  const modal = document.getElementById('productPickerModal');
  if (modal) modal.classList.add('hidden');
}

function openCouponPickerModal() {
  const modal = document.getElementById('couponPickerModal');
  if (modal) {
    modal.classList.remove('hidden');
    fetchCouponsForChat();
  }
}

function closeCouponPickerModal() {
  const modal = document.getElementById('couponPickerModal');
  if (modal) modal.classList.add('hidden');
}

async function searchProductsForChat() {
  const query = document.getElementById('productSearchInput')?.value || '';
  const container = document.getElementById('productSearchResults');
  if (!container) return;

  try {
    const res = await fetch(`/admin/conversations/search-products?q=${encodeURIComponent(query)}`);
    const data = await res.json();
    
    if (!data || data.length === 0) {
      container.innerHTML = '<div class="p-4 text-center text-xs text-gray-400">No products found</div>';
      return;
    }

    let html = '';
    data.forEach(p => {
      html += `
        <div class="flex items-center justify-between p-2 hover:bg-white rounded-lg border border-transparent hover:border-gray-200 transition">
          <div class="flex items-center gap-2.5 min-w-0">
            <img src="${p.image_url}" class="h-9 w-9 object-cover rounded-lg border border-gray-200 shrink-0" />
            <div class="min-w-0">
              <h5 class="font-bold text-xs text-ink truncate">${p.name}</h5>
              <p class="text-[11px] font-extrabold text-brand-600">${p.formatted_price}</p>
            </div>
          </div>
          <form method="POST" action="/admin/conversations/{{ $activeConversation->id ?? 0 }}/send-product">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
            <input type="hidden" name="product_id" value="${p.id}">
            <button type="submit" class="px-2.5 py-1 bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-[10px] rounded-md transition shadow-2xs">Send Card ➔</button>
          </form>
        </div>
      `;
    });
    container.innerHTML = html;
  } catch (e) {
    container.innerHTML = '<div class="p-4 text-center text-xs text-rose-500 font-bold">Failed to load products</div>';
  }
}

async function fetchCouponsForChat() {
  const container = document.getElementById('couponListResults');
  if (!container) return;

  try {
    const res = await fetch('/admin/conversations/coupons');
    const data = await res.json();
    
    if (!data || data.length === 0) {
      container.innerHTML = '<div class="p-4 text-center text-xs text-gray-400">No active coupons available</div>';
      return;
    }

    let html = '';
    data.forEach(c => {
      html += `
        <div class="flex items-center justify-between p-2.5 hover:bg-white rounded-lg border border-transparent hover:border-amber-200 transition bg-amber-50/50">
          <div>
            <span class="font-mono font-extrabold text-xs text-amber-900 bg-white px-2 py-0.5 rounded border border-amber-200">${c.code}</span>
            <p class="text-[11px] text-gray-600 font-medium mt-1">${c.description}</p>
          </div>
          <form method="POST" action="/admin/conversations/{{ $activeConversation->id ?? 0 }}/send-coupon">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
            <input type="hidden" name="coupon_id" value="${c.id}">
            <button type="submit" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-[10px] rounded-md transition shadow-2xs">Send Coupon ➔</button>
          </form>
        </div>
      `;
    });
    container.innerHTML = html;
  } catch (e) {
    container.innerHTML = '<div class="p-4 text-center text-xs text-rose-500 font-bold">Failed to load coupons</div>';
  }
}
</script>
@endpush
@endsection
