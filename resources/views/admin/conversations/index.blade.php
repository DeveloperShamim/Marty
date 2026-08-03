@extends('layouts.admin')
@section('title', 'Live Support Chat Inbox')

@section('content')
<div class="space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between flex-wrap gap-4 border-b border-gray-200 pb-4">
    <div>
      <h2 class="text-2xl font-extrabold text-ink tracking-tight flex items-center gap-2.5">
        <span>💬</span> Live Customer Support Chat
      </h2>
      <p class="text-xs text-gray-500 mt-1">Manage real-time customer inquiries, voice notes, phone calls, product recommendations, and order tracking.</p>
    </div>
    
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.conversations.index', ['status' => 'open']) }}" class="px-3.5 py-1.5 text-xs font-bold rounded-xl {{ $status === 'open' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-gray-700 border border-gray-200' }}">
        Open Chats ({{ \App\Models\Conversation::where('status', 'open')->count() }})
      </a>
      <a href="{{ route('admin.conversations.index', ['status' => 'closed']) }}" class="px-3.5 py-1.5 text-xs font-bold rounded-xl {{ $status === 'closed' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-gray-700 border border-gray-200' }}">
        Closed Chats
      </a>
      <a href="{{ route('admin.conversations.index', ['status' => 'all']) }}" class="px-3.5 py-1.5 text-xs font-bold rounded-xl {{ $status === 'all' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-gray-700 border border-gray-200' }}">
        All
      </a>
      <button type="button" onclick="document.getElementById('storageCleanupModal').classList.remove('hidden')" class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition flex items-center gap-1 cursor-pointer">
        <span>🧹</span> Clean Storage
      </button>
    </div>
  </div>

  {{-- Main Inbox Grid --}}
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[740px] max-h-[84vh]">
    
    {{-- Left Panel: Conversations List --}}
    <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-200 flex flex-col overflow-hidden shadow-xs">
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
          @php $isActive = $activeConversation && $activeConversation->id === $conv->id; @endphp
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
    <div class="lg:col-span-6 bg-white rounded-2xl border border-gray-200 flex flex-col overflow-hidden shadow-xs">
      @if($activeConversation)
        {{-- Thread Header --}}
        <div class="p-3.5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-extrabold text-xs border border-brand-200">
              {{ strtoupper(substr($activeConversation->customer_name, 0, 1)) }}
            </div>
            <div>
              <h3 class="font-extrabold text-xs text-ink flex items-center gap-2">
                <span>{{ $activeConversation->customer_name }}</span>
                @if($activeConversation->status === 'open')
                  <span class="px-2 py-0.5 text-[9px] font-extrabold bg-emerald-100 text-emerald-800 rounded-full border border-emerald-200">Open</span>
                @else
                  <span class="px-2 py-0.5 text-[9px] font-extrabold bg-gray-100 text-gray-600 rounded-full border border-gray-200">Closed</span>
                @endif
              </h3>
              <p class="text-[10px] text-gray-400 mt-0.5">
                @if($activeConversation->customer_phone) 📞 {{ $activeConversation->customer_phone }} @endif
                @if($activeConversation->customer_email) | ✉️ {{ $activeConversation->customer_email }} @endif
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.conversations.toggle-status', $activeConversation) }}">
              @csrf
              @if($activeConversation->status === 'open')
                <button type="submit" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition shadow-2xs cursor-pointer flex items-center gap-1">
                  <span>🔒</span> Close Chat
                </button>
              @else
                <button type="submit" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition shadow-2xs cursor-pointer flex items-center gap-1">
                  <span>🔓</span> Re-open Chat
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
        <div id="adminMessageList" class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50/40 no-scrollbar">
          @forelse($activeConversation->messages as $msg)
            @php $isAdmin = $msg->sender_type === 'admin'; @endphp
            <div class="flex flex-col {{ $isAdmin ? 'items-end' : 'items-start' }}">
              
              @if($msg->type === 'product' && $msg->metadata)
                @php $meta = $msg->metadata; @endphp
                <div class="max-w-[80%] rounded-2xl p-3 bg-white border border-indigo-200 shadow-md text-gray-800 space-y-2">
                  <div class="flex items-center gap-2.5">
                    <img src="{{ $meta['image_url'] ?? logo_url() }}" class="h-11 w-11 object-cover rounded-lg border border-gray-100 bg-gray-50 shrink-0" alt="" />
                    <div class="min-w-0">
                      <span class="text-[9px] font-extrabold text-indigo-600 uppercase tracking-wider block">Sent Product Card</span>
                      <h5 class="font-extrabold text-xs text-ink truncate">{{ $meta['name'] ?? '' }}</h5>
                      <p class="text-xs font-black text-brand-600 mt-0.5">{{ $meta['formatted_price'] ?? money($meta['price'] ?? 0) }}</p>
                    </div>
                  </div>
                  <a href="{{ $meta['url'] ?? '#' }}" target="_blank" class="block w-full py-1 text-center bg-indigo-50 text-indigo-700 font-extrabold text-[10px] rounded-md hover:bg-indigo-100">View Product Page ↗</a>
                </div>

              @elseif($msg->type === 'order' && $msg->metadata)
                @php $meta = $msg->metadata; @endphp
                <div class="max-w-[80%] rounded-2xl p-3 bg-white border border-emerald-200 shadow-md text-gray-800 space-y-2">
                  <div class="border-b border-gray-100 pb-1.5 flex items-center justify-between">
                    <span class="font-extrabold text-xs text-emerald-950">Sent Order Details ({{ $meta['order_number'] ?? '' }})</span>
                    <span class="px-2 py-0.5 text-[9px] font-extrabold bg-emerald-100 text-emerald-800 rounded-full">{{ $meta['delivery_status'] ?? '' }}</span>
                  </div>
                  @if(!empty($meta['items_summary']))
                    <p class="text-[11px] font-medium text-gray-700">📦 {{ $meta['items_summary'] }}</p>
                  @endif
                  <p class="text-[11px] text-gray-600">Total: <strong>{{ $meta['formatted_total'] ?? '' }}</strong> ({{ $meta['items_count'] ?? 1 }} items)</p>
                  <a href="{{ $meta['tracking_url'] ?? '#' }}" target="_blank" class="block w-full py-1 text-center bg-emerald-50 text-emerald-800 font-extrabold text-[10px] rounded-md hover:bg-emerald-100">Tracking Link ↗</a>
                </div>

              @elseif($msg->type === 'coupon' && $msg->metadata)
                @php $meta = $msg->metadata; @endphp
                <div class="max-w-[80%] rounded-2xl p-3 bg-amber-50 border border-amber-200 shadow-md text-amber-950 space-y-1.5">
                  <div class="flex items-center justify-between">
                    <span class="text-[9px] font-extrabold text-amber-800 uppercase tracking-wider">🎟️ Discount Coupon Card</span>
                    <span class="font-extrabold text-xs text-amber-900">{{ $meta['discount_text'] ?? '' }}</span>
                  </div>
                  <div class="bg-white p-1.5 rounded border border-dashed border-amber-300 font-mono text-xs font-bold text-center tracking-wider text-amber-900">
                    CODE: {{ $meta['code'] ?? '' }}
                  </div>
                </div>

              @elseif($msg->type === 'voice' && $msg->attachment_url)
                <div class="max-w-[80%] rounded-2xl p-2.5 bg-white border border-gray-200 shadow-md text-gray-800 space-y-1">
                  <div class="flex items-center gap-1 text-[10px] font-extrabold text-brand-600">
                    <span>🎙️ Voice Note Recording</span>
                  </div>
                  <audio controls class="w-64 h-8 rounded max-w-full">
                    <source src="{{ $msg->attachment_url }}" type="audio/webm">
                    <source src="{{ $msg->attachment_url }}" type="audio/mp3">
                  </audio>
                </div>

              @elseif($msg->type === 'image' || $msg->attachment_url)
                <div class="max-w-[80%] rounded-2xl p-1 bg-white border border-gray-200 shadow-md overflow-hidden">
                  <a href="{{ $msg->attachment_url }}" target="_blank">
                    <img src="{{ $msg->attachment_url }}" class="max-h-48 w-full object-cover rounded-xl" alt="Attachment" />
                  </a>
                </div>

              @else
                <div class="max-w-[78%] rounded-2xl px-3.5 py-2 text-xs font-medium leading-relaxed shadow-2xs {{ $isAdmin ? 'bg-brand-600 text-white rounded-br-none' : 'bg-white text-gray-800 border border-gray-200/80 rounded-bl-none shadow-xs' }}">
                  {{ $msg->message }}
                </div>
              @endif

              <div class="text-[9px] font-semibold text-gray-400 mt-1 px-1 flex items-center gap-1">
                <span>{{ $isAdmin ? 'Support Agent' : $activeConversation->customer_name }}</span>
                <span>•</span>
                <span>{{ $msg->created_at->format('h:i A') }}</span>
              </div>
            </div>
          @empty
            <div class="text-center text-xs text-gray-400 py-12">No message history available.</div>
          @endforelse
        </div>

        {{-- Audio Recording Overlay Bar --}}
        <div id="adminRecordingOverlay" class="hidden p-2.5 bg-rose-50 border-t border-rose-100 flex items-center justify-between gap-2 animate-pulse">
          <div class="flex items-center gap-2 text-rose-700 text-xs font-extrabold">
            <span class="w-2.5 h-2.5 bg-rose-600 rounded-full animate-ping"></span>
            <span>Recording Admin Voice Note...</span>
            <span id="adminRecordTimer" class="font-mono text-rose-900">00:00</span>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" id="adminCancelRecordBtn" class="px-2.5 py-1 bg-white border border-rose-200 text-rose-700 font-bold text-[10px] rounded-lg">Cancel</button>
            <button type="button" id="adminStopSendRecordBtn" class="px-3 py-1 bg-rose-600 text-white font-extrabold text-[10px] rounded-lg shadow-2xs">Send Voice Note ➔</button>
          </div>
        </div>

        {{-- Admin Reply Form --}}
        <form method="POST" action="{{ route('admin.conversations.reply', $activeConversation) }}" enctype="multipart/form-data" class="p-3 bg-white border-t border-gray-100 flex items-center gap-2">
          @csrf
          <label for="adminAttachmentInput" class="h-9 w-9 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center cursor-pointer shrink-0 transition" title="Attach Photo">
            📷
            <input type="file" id="adminAttachmentInput" accept="image/*" class="hidden" onchange="uploadAdminPhotoAttachment()" />
          </label>

          <button type="button" id="adminMicBtn" class="h-9 w-9 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center cursor-pointer shrink-0 transition" title="Record Voice Note">
            🎙️
          </button>

          <textarea id="adminReplyText" name="message" rows="2" placeholder="Type message to customer..." class="flex-1 text-xs bg-gray-50 border border-gray-200 rounded-xl p-2.5 focus:outline-none focus:border-brand-500 focus:bg-white transition resize-none" required></textarea>
          
          <button type="submit" class="btn-primary px-4 py-3 text-xs font-extrabold rounded-xl shadow-xs shrink-0 flex items-center gap-1.5">
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

    {{-- Right Sidebar: Customer Context & Past Orders --}}
    <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-200 flex flex-col overflow-hidden shadow-xs">
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
function insertQuickReply(text) {
  const replyBox = document.getElementById('adminReplyText');
  if (replyBox) {
    replyBox.value = text;
    replyBox.focus();
  }
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

async function fetchCouponsForChat() {
  const container = document.getElementById('couponListResults');
  if (!container) return;

  container.innerHTML = `<div class="p-4 text-center text-xs text-gray-400">Loading coupons...</div>`;

  try {
    const res = await fetch("{{ route('admin.conversations.coupons.list') }}");
    const data = await res.json();

    if (!data.coupons || data.coupons.length === 0) {
      container.innerHTML = `<div class="p-4 text-center text-xs text-gray-400">No active discount coupons available. Manage coupons under Admin > Marketing.</div>`;
      return;
    }

    container.innerHTML = '';
    data.coupons.forEach(c => {
      const div = document.createElement('div');
      div.className = 'flex items-center justify-between p-2.5 rounded-lg bg-amber-50/80 border border-amber-200 gap-3';
      div.innerHTML = `
        <div class="min-w-0">
          <code class="font-mono text-xs font-bold text-amber-900 tracking-wider">${c.code}</code>
          <p class="text-[10px] text-amber-800 font-semibold mt-0.5">${c.discount_text}</p>
        </div>
        <form method="POST" action="{{ route('admin.conversations.send-coupon', $activeConversation ? $activeConversation->id : 0) }}">
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <input type="hidden" name="coupon_id" value="${c.id}" />
          <button type="submit" class="px-2.5 py-1 text-[10px] font-extrabold bg-amber-600 hover:bg-amber-700 text-white rounded-lg shadow-2xs cursor-pointer shrink-0">
            Send Coupon ➔
          </button>
        </form>
      `;
      container.appendChild(div);
    });
  } catch (e) {
    container.innerHTML = `<div class="p-4 text-center text-xs text-rose-500">Failed to load coupons</div>`;
  }
}

async function uploadAdminPhotoAttachment() {
  const fileInput = document.getElementById('adminAttachmentInput');
  if (!fileInput || !fileInput.files[0]) return;

  const formData = new FormData();
  formData.append('image', fileInput.files[0]);
  formData.append('_token', '{{ csrf_token() }}');

  try {
    const res = await fetch("{{ route('admin.conversations.upload-attachment', $activeConversation ? $activeConversation->id : 0) }}", {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      window.location.reload();
    }
  } catch (e) {
    alert('Failed to upload photo attachment');
  } finally {
    fileInput.value = '';
  }
}

// Admin Voice Recording
(function() {
  const micBtn = document.getElementById('adminMicBtn');
  const overlay = document.getElementById('adminRecordingOverlay');
  const timerEl = document.getElementById('adminRecordTimer');
  const cancelBtn = document.getElementById('adminCancelRecordBtn');
  const stopSendBtn = document.getElementById('adminStopSendRecordBtn');

  if (!micBtn) return;

  let mediaRecorder = null;
  let audioChunks = [];
  let recordTimerInterval = null;
  let recordSeconds = 0;

  micBtn.addEventListener('click', async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      mediaRecorder = new MediaRecorder(stream);
      audioChunks = [];

      mediaRecorder.ondataavailable = (e) => {
        if (e.data.size > 0) audioChunks.push(e.data);
      };

      mediaRecorder.onstop = async () => {
        stream.getTracks().forEach(t => t.stop());
        if (audioChunks.length === 0) return;
        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
        sendAdminVoiceBlob(audioBlob);
      };

      mediaRecorder.start();
      recordSeconds = 0;
      overlay.classList.remove('hidden');
      recordTimerInterval = setInterval(() => {
        recordSeconds++;
        const m = String(Math.floor(recordSeconds / 60)).padStart(2, '0');
        const s = String(recordSeconds % 60).padStart(2, '0');
        timerEl.textContent = `${m}:${s}`;
      }, 1000);
    } catch (err) {
      alert('Microphone permission required for voice notes');
    }
  });

  cancelBtn?.addEventListener('click', () => {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
      audioChunks = [];
      mediaRecorder.stop();
    }
    clearInterval(recordTimerInterval);
    overlay.classList.add('hidden');
  });

  stopSendBtn?.addEventListener('click', () => {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
      mediaRecorder.stop();
    }
    clearInterval(recordTimerInterval);
    overlay.classList.add('hidden');
  });

  async function sendAdminVoiceBlob(blob) {
    const formData = new FormData();
    formData.append('audio', blob, 'voice_note.webm');
    formData.append('_token', '{{ csrf_token() }}');

    try {
      const res = await fetch("{{ route('admin.conversations.upload-voice', $activeConversation ? $activeConversation->id : 0) }}", {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        window.location.reload();
      }
    } catch (e) {
      alert('Failed to send voice note');
    }
  }
})();

let searchTimer = null;
function searchProductsForChat() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(async () => {
    const q = document.getElementById('productSearchInput')?.value || '';
    const container = document.getElementById('productSearchResults');
    if (!container) return;

    container.innerHTML = `<div class="p-4 text-center text-xs text-gray-400">Searching products...</div>`;

    try {
      const res = await fetch("{{ route('admin.conversations.products.search') }}?q=" + encodeURIComponent(q));
      const data = await res.json();

      if (!data.products || data.products.length === 0) {
        container.innerHTML = `<div class="p-4 text-center text-xs text-gray-400">No products found matching "${q}"</div>`;
        return;
      }

      container.innerHTML = '';
      data.products.forEach(p => {
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between p-2 rounded-lg bg-white border border-gray-200/80 gap-3';
        div.innerHTML = `
          <div class="flex items-center gap-2.5 min-w-0">
            <img src="${p.image_url}" class="h-9 w-9 object-cover rounded bg-gray-50 border border-gray-100 shrink-0" alt="" />
            <div class="min-w-0">
              <h5 class="font-extrabold text-xs text-ink truncate">${p.name}</h5>
              <p class="text-[10px] text-brand-600 font-bold">${p.formatted_price}</p>
            </div>
          </div>
          <form method="POST" action="{{ route('admin.conversations.send-product', $activeConversation ? $activeConversation->id : 0) }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <input type="hidden" name="product_id" value="${p.id}" />
            <button type="submit" class="px-2.5 py-1 text-[10px] font-extrabold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-2xs cursor-pointer shrink-0">
              Send Card ➔
            </button>
          </form>
        `;
        container.appendChild(div);
      });
    } catch (e) {
      container.innerHTML = `<div class="p-4 text-center text-xs text-rose-500">Failed to load products</div>`;
    }
  }, 250);
}

(function() {
  const msgList = document.getElementById('adminMessageList');
  if (msgList) {
    msgList.scrollTop = msgList.scrollHeight;
  }
})();
</script>
{{-- Storage Cleanup Modal --}}
<div id="storageCleanupModal" class="hidden fixed inset-0 bg-ink/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
  <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-gray-100 overflow-hidden space-y-4 p-5">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <h3 class="font-extrabold text-sm text-ink flex items-center gap-2">
        <span>🧹</span> Prune & Clean Chat Storage Space
      </h3>
      <button type="button" onclick="document.getElementById('storageCleanupModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 text-lg font-bold">✕</button>
    </div>

    <form method="POST" action="{{ route('admin.conversations.prune-storage') }}" class="space-y-4">
      @csrf
      <p class="text-xs text-gray-600 leading-relaxed">
        Automatically delete old voice notes (`.webm`), uploaded screenshots, and expired attachment files to free server disk space.
      </p>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1.5">Delete files older than:</label>
        <select name="days" class="w-full text-xs font-bold bg-gray-50 border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
          <option value="30">30 Days</option>
          <option value="60">60 Days</option>
          <option value="90" selected>90 Days (Recommended)</option>
          <option value="180">180 Days (6 Months)</option>
          <option value="365">365 Days (1 Year)</option>
        </select>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('storageCleanupModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl cursor-pointer">Cancel</button>
        <button type="submit" onclick="return confirm('Are you sure you want to clean old chat storage files?')" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow transition cursor-pointer">
          🧹 Clean Storage Now
        </button>
      </div>
    </form>
  </div>
</div>

@endpush
@endsection
