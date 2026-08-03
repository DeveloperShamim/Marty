{{-- Live Customer Support Chat Floating Widget --}}
<div id="liveChatRoot" class="fixed bottom-20 right-6 z-[60] select-none font-sans">
  {{-- Floating Toggle Button (Positioned above backToTop with identical w-11 h-11 size) --}}
  <button type="button" id="chatTriggerBtn" class="relative group flex items-center justify-center bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 active:scale-95 text-white w-11 h-11 rounded-full shadow-lg transition-all duration-200 cursor-pointer" aria-label="Open Live Chat">
    {{-- Closed State: Direct white speech bubble icon without inner circle --}}
    <div id="chatIconClosed" class="relative flex items-center justify-center text-white transition-transform duration-200 group-hover:scale-110">
      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.2L4 17.2V4h16v12z"/>
        <path d="M7 9h10v2H7zm0-3h10v2H7z"/>
      </svg>
    </div>

    {{-- Opened State: Compact downward chevron --}}
    <div id="chatIconOpened" class="hidden relative flex items-center justify-center text-white transition-transform duration-200 group-hover:translate-y-0.5">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
      </svg>
    </div>

    <span id="chatOnlineDot" class="absolute top-0 right-0 w-2.5 h-2.5 bg-emerald-400 border-2 border-white rounded-full ring-1 ring-emerald-500"></span>
    <span id="chatUnreadBadge" class="hidden absolute -top-1 -left-1 bg-rose-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded-full ring-2 ring-white shadow">0</span>
  </button>

  {{-- Chat Window Panel (Dynamic Brand Color Mobile Shell) --}}
  <div id="chatWindowPanel" class="hidden fixed bottom-32 right-4 sm:right-6 w-[calc(100vw-32px)] sm:w-[325px] h-[475px] max-h-[75vh] bg-gradient-to-b from-brand-600 via-brand-700 to-brand-800 rounded-[28px] shadow-2xl border border-brand-400/30 flex flex-col overflow-hidden transition-all duration-300 transform scale-95 opacity-0 z-[60] text-white">
    
    {{-- ======================================================== --}}
    {{-- VIEW 1: HOME LANDING VIEW (MATCHING BRAND THEME)         --}}
    {{-- ======================================================== --}}
    <div id="chatHomeView" class="flex-1 flex flex-col justify-between p-6 relative overflow-y-auto no-scrollbar">
      
      {{-- Top Header Bar --}}
      <div class="flex items-center justify-between pt-1">
        <div class="flex items-center gap-2.5">
          <div class="h-10 w-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-lg font-black border border-white/30 shadow-inner">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
          </div>
          <span class="font-black text-sm tracking-tight text-white/90 drop-shadow-xs">{{ site_name() }}</span>
        </div>

        <button type="button" id="chatHomeCloseBtn" class="h-9 w-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer" aria-label="Close Chat">
          ✕
        </button>
      </div>

      {{-- Main Greeting Glass Box --}}
      <div class="my-auto py-4">
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-6 text-center space-y-3 shadow-xl">
          <h2 class="text-2xl font-black tracking-tight text-white leading-snug drop-shadow-sm">
            Welcome to<br/>{{ site_name() }}!
          </h2>
          <p class="text-sm font-medium text-white/90 leading-normal">
            How can we help you today?
          </p>
          <button type="button" id="startChatBtn" class="mt-2 w-full py-3 px-4 bg-white hover:bg-brand-50 active:scale-98 text-brand-700 font-extrabold text-sm rounded-2xl shadow-lg transition-all cursor-pointer flex items-center justify-center gap-2">
            <span>💬 Send Us a Message</span>
            <span>➔</span>
          </button>
        </div>
      </div>

      {{-- Recent Conversation Card Section --}}
      <div class="space-y-2.5 pb-2">
        <h4 class="text-xs font-bold text-white/90 tracking-wide">Recent conversation</h4>
        
        <div id="recentConversationCard" class="bg-white text-gray-900 rounded-2xl p-3.5 shadow-xl hover:shadow-2xl transition-all cursor-pointer border border-white/40 flex items-center justify-between gap-3 group active:scale-98">
          <div class="flex items-center gap-3 min-w-0">
            <div class="relative shrink-0">
              <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-brand-500 to-brand-700 text-white flex items-center justify-center font-black text-sm border-2 border-white shadow">
                👤
              </div>
              <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 border-2 border-white rounded-full"></span>
            </div>
            <div class="min-w-0">
              <h5 class="font-extrabold text-xs text-gray-900 group-hover:text-brand-600 transition truncate">
                {{ site_name() }} Support
              </h5>
              <p id="recentMessageSnippet" class="text-[11px] text-gray-500 truncate mt-0.5 font-medium">
                Hello! 👋 Welcome to {{ site_name() }}. How can we help?
              </p>
            </div>
          </div>
          <span id="recentMessageTime" class="text-[10px] font-bold text-gray-400 shrink-0">
            Just now
          </span>
        </div>

        <div class="text-center pt-2">
          <span class="text-[10px] font-bold text-white/60 uppercase tracking-widest">
            Powered by {{ site_name() }} Support
          </span>
        </div>
      </div>
    </div>

    {{-- ======================================================== --}}
    {{-- VIEW 2: ACTIVE CHAT THREAD VIEW (FULL MESSAGING INTERFACE)--}}
    {{-- ======================================================== --}}
    <div id="chatThreadView" class="hidden flex-1 flex flex-col bg-white text-gray-800 overflow-hidden">
      
      {{-- Header Bar --}}
      <div class="bg-gradient-to-r from-brand-700 to-brand-600 text-white p-3.5 flex items-center justify-between shrink-0 shadow-md">
        <div class="flex items-center gap-2.5">
          <button type="button" id="chatBackToHomeBtn" class="h-8 w-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer" title="Back to Home">
            ←
          </button>
          <div class="relative">
            <div class="h-9 w-9 rounded-full bg-white/20 flex items-center justify-center text-white text-sm font-black border border-white/30">
              💬
            </div>
            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-400 border-2 border-brand-600 rounded-full"></span>
          </div>
          <div>
            <h4 class="font-extrabold text-sm tracking-tight leading-tight">{{ site_name() }} Support</h4>
            <p class="text-[11px] text-emerald-100 font-medium">● Online Support</p>
          </div>
        </div>

        <button type="button" id="chatThreadCloseBtn" class="h-8 w-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer" aria-label="Close Chat">
          ✕
        </button>
      </div>

      {{-- Contact Info Bar (For Guests) --}}
      @guest
      <div id="chatGuestInfoBar" class="bg-brand-50/80 border-b border-brand-100 px-3.5 py-2 flex items-center gap-2 shrink-0">
        <span class="text-xs">💡</span>
        <input type="text" id="chatGuestNameInput" placeholder="Your name (optional)" class="text-[11px] bg-white border border-brand-200 rounded px-2 py-1 w-full focus:outline-none focus:border-brand-500" />
      </div>
      @endguest

      {{-- Messages Container --}}
      <div id="chatMessageList" class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50/60 no-scrollbar">
        <div id="chatLoadingState" class="flex items-center justify-center h-full text-xs text-gray-400 font-medium space-x-2">
          <span class="animate-spin text-brand-600 text-sm">↻</span>
          <span>Connecting to support...</span>
        </div>
      </div>

      {{-- Audio Recording Overlay Bar (Hidden by default) --}}
      <div id="chatRecordingOverlay" class="hidden p-3 bg-rose-50 border-t border-rose-100 flex items-center justify-between gap-2 shrink-0 animate-pulse">
        <div class="flex items-center gap-2 text-rose-700 text-xs font-extrabold">
          <span class="w-2.5 h-2.5 bg-rose-600 rounded-full animate-ping"></span>
          <span>Recording Voice Note...</span>
          <span id="chatRecordTimer" class="font-mono text-rose-900">00:00</span>
        </div>
        <div class="flex items-center gap-2">
          <button type="button" id="chatCancelRecordBtn" class="px-2.5 py-1 bg-white border border-rose-200 text-rose-700 font-bold text-[10px] rounded-lg">Cancel</button>
          <button type="button" id="chatStopSendRecordBtn" class="px-3 py-1 bg-rose-600 text-white font-extrabold text-[10px] rounded-lg shadow-2xs">Send ➔</button>
        </div>
      </div>

      {{-- Input Footer --}}
      <form id="chatMessageForm" class="p-3 bg-white border-t border-gray-100 flex items-center gap-2 shrink-0">
        @csrf
        <label for="chatAttachFileInput" class="h-9 w-9 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center cursor-pointer shrink-0 transition" title="Attach Photo">
          📷
          <input type="file" id="chatAttachFileInput" accept="image/*" class="hidden" />
        </label>

        <button type="button" id="chatMicBtn" class="h-9 w-9 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center cursor-pointer shrink-0 transition" title="Record Voice Message">
          🎙️
        </button>

        <input type="text" id="chatInputText" placeholder="Write a message..." class="flex-1 text-xs bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-brand-500 focus:bg-white transition" autocomplete="off" required />
        
        <button type="submit" id="chatSendBtn" class="h-9 w-9 rounded-xl bg-brand-600 hover:bg-brand-700 text-white flex items-center justify-center shadow transition shrink-0 cursor-pointer disabled:opacity-50">
          <svg class="w-4 h-4 transform rotate-90" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
        </button>
      </form>

    </div>
  </div>
</div>

<script>
(function() {
  const triggerBtn = document.getElementById('chatTriggerBtn');
  const homeCloseBtn = document.getElementById('chatHomeCloseBtn');
  const threadCloseBtn = document.getElementById('chatThreadCloseBtn');
  const backToHomeBtn = document.getElementById('chatBackToHomeBtn');
  const startChatBtn = document.getElementById('startChatBtn');
  const recentCard = document.getElementById('recentConversationCard');
  
  const panel = document.getElementById('chatWindowPanel');
  const homeView = document.getElementById('chatHomeView');
  const threadView = document.getElementById('chatThreadView');

  const msgList = document.getElementById('chatMessageList');
  const form = document.getElementById('chatMessageForm');
  const input = document.getElementById('chatInputText');
  const fileInput = document.getElementById('chatAttachFileInput');
  const micBtn = document.getElementById('chatMicBtn');
  const recordingOverlay = document.getElementById('chatRecordingOverlay');
  const recordTimerEl = document.getElementById('chatRecordTimer');
  const cancelRecordBtn = document.getElementById('chatCancelRecordBtn');
  const stopSendRecordBtn = document.getElementById('chatStopSendRecordBtn');
  const guestNameInput = document.getElementById('chatGuestNameInput');
  const snippetEl = document.getElementById('recentMessageSnippet');
  const timeEl = document.getElementById('recentMessageTime');

  const iconClosed = document.getElementById('chatIconClosed');
  const iconOpened = document.getElementById('chatIconOpened');
  const onlineDot = document.getElementById('chatOnlineDot');

  if (!triggerBtn || !panel) return;

  let isOpen = false;
  let isInitialized = false;
  let lastMessageId = 0;
  let pollTimer = null;
  let mediaRecorder = null;
  let audioChunks = [];
  let recordTimerInterval = null;
  let recordSeconds = 0;
  const token = '{{ csrf_token() }}';
  const siteName = "{{ site_name() }}";

  function updateTriggerIcon() {
    if (isOpen) {
      if (iconClosed) iconClosed.classList.add('hidden');
      if (iconOpened) iconOpened.classList.remove('hidden');
      if (onlineDot) onlineDot.classList.add('hidden');
    } else {
      if (iconClosed) iconClosed.classList.remove('hidden');
      if (iconOpened) iconOpened.classList.add('hidden');
      if (onlineDot) onlineDot.classList.remove('hidden');
    }
  }

  function togglePanel() {
    isOpen = !isOpen;
    updateTriggerIcon();
    if (isOpen) {
      // Close cart drawer if currently open to prevent overlapping panels
      const cartDrawer = document.getElementById('cartDrawer');
      if (cartDrawer) cartDrawer.classList.add('translate-x-full');
      const mobileMenu = document.getElementById('mobileMenu');
      if (mobileMenu) mobileMenu.classList.add('-translate-x-full');
      const overlay = document.getElementById('overlay');
      if (overlay) overlay.classList.add('opacity-0', 'pointer-events-none');
      document.body.classList.remove('no-scroll');

      panel.classList.remove('hidden');
      setTimeout(() => {
        panel.classList.remove('scale-95', 'opacity-0');
        panel.classList.add('scale-100', 'opacity-100');
      }, 10);

      if (!isInitialized) {
        initChat();
      } else {
        fetchMessages();
      }
      startPolling();
    } else {
      panel.classList.remove('scale-100', 'opacity-100');
      panel.classList.add('scale-95', 'opacity-0');
      setTimeout(() => panel.classList.add('hidden'), 200);
      stopPolling();
    }
  }

  function showThreadView() {
    homeView.classList.add('hidden');
    threadView.classList.remove('hidden');
    setTimeout(() => {
      scrollToBottom();
      input.focus();
    }, 50);
  }

  function showHomeView() {
    threadView.classList.add('hidden');
    homeView.classList.remove('hidden');
  }

  window.closeLiveChatPanel = function() {
    if (isOpen) {
      isOpen = false;
      updateTriggerIcon();
      panel.classList.remove('scale-100', 'opacity-100');
      panel.classList.add('scale-95', 'opacity-0');
      setTimeout(() => panel.classList.add('hidden'), 200);
      stopPolling();
    }
  };

  triggerBtn.addEventListener('click', togglePanel);
  homeCloseBtn?.addEventListener('click', togglePanel);
  threadCloseBtn?.addEventListener('click', togglePanel);
  backToHomeBtn?.addEventListener('click', showHomeView);
  startChatBtn?.addEventListener('click', showThreadView);
  recentCard?.addEventListener('click', showThreadView);

  async function initChat() {
    try {
      const res = await fetch("{{ route('chat.conversation') }}", {
        headers: { 'Accept': 'application/json' }
      });
      const data = await res.json();
      
      if (data.success) {
        isInitialized = true;
        renderMessages(data.messages, true);
      }
    } catch (e) {
      msgList.innerHTML = `<div class="text-center text-xs text-rose-500 py-4 font-semibold">Failed to connect. Please try again.</div>`;
    }
  }

  async function fetchMessages() {
    try {
      const res = await fetch(`{{ route('chat.poll') }}?last_id=${lastMessageId}`, {
        headers: { 'Accept': 'application/json' }
      });
      const data = await res.json();
      if (data.success && data.messages.length > 0) {
        let hasNewAdminMsg = data.messages.some(m => m.sender_type === 'admin');
        if (hasNewAdminMsg) playChime();
        appendMessages(data.messages);
      }
    } catch (e) {}
  }

  function renderMessages(messages, isInitial = false) {
    msgList.innerHTML = '';

    // Auto Greeting Message from Support when chat opens
    const autoGreetingMsg = {
      id: 0,
      sender_type: 'admin',
      type: 'text',
      message: `Hello! 👋 Welcome to ${siteName} Live Support. How can we help you today?`,
      time: 'Just now'
    };
    
    msgList.appendChild(createBubble(autoGreetingMsg));

    if (messages && messages.length > 0) {
      messages.forEach(msg => {
        msgList.appendChild(createBubble(msg));
        if (msg.id > lastMessageId) lastMessageId = msg.id;
      });

      // Update Recent Conversation snippet card on Home View
      const lastMsg = messages[messages.length - 1];
      if (lastMsg && snippetEl) {
        snippetEl.textContent = lastMsg.message || 'Image / Attachment';
        if (timeEl && lastMsg.time) timeEl.textContent = lastMsg.time;
      }
    }

    scrollToBottom();
  }

  function appendMessages(messages) {
    let shouldScroll = isNearBottom();
    messages.forEach(msg => {
      if (msg.id > lastMessageId) {
        msgList.appendChild(createBubble(msg));
        lastMessageId = msg.id;
      }
    });

    if (messages.length > 0 && snippetEl) {
      const lastMsg = messages[messages.length - 1];
      snippetEl.textContent = lastMsg.message || 'Image / Attachment';
      if (timeEl && lastMsg.time) timeEl.textContent = lastMsg.time;
    }

    if (shouldScroll) scrollToBottom();
  }

  function createBubble(msg) {
    const isCustomer = msg.sender_type === 'customer';
    const wrap = document.createElement('div');
    wrap.className = `flex flex-col ${isCustomer ? 'items-end' : 'items-start'} mb-3`;

    const bubble = document.createElement('div');

    if (msg.type === 'product' && msg.metadata) {
      const meta = msg.metadata;
      bubble.className = 'max-w-[85%] rounded-2xl p-3 bg-white border border-brand-200 shadow-md text-gray-800 space-y-2';
      bubble.innerHTML = `
        <div class="flex items-center gap-2.5">
          <img src="${escapeHtml(meta.image_url)}" class="h-12 w-12 object-cover rounded-lg border border-gray-100 bg-gray-50 shrink-0" alt="" />
          <div class="min-w-0">
            <span class="text-[10px] font-extrabold text-brand-600 uppercase tracking-wider block">Recommended Product</span>
            <h5 class="font-extrabold text-xs text-ink truncate">${escapeHtml(meta.name)}</h5>
            <p class="text-xs font-black text-brand-600 mt-0.5">${escapeHtml(meta.formatted_price || '৳' + meta.price)}</p>
          </div>
        </div>
        <a href="${escapeHtml(meta.url)}" target="_blank" class="block w-full py-1.5 px-3 bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-[11px] rounded-lg text-center shadow-2xs transition">
          View Product ↗
        </a>
      `;
    } else if (msg.type === 'order' && msg.metadata) {
      const meta = msg.metadata;
      const itemsText = meta.items_summary ? `<p class="text-[11px] font-medium text-gray-700 mt-1">📦 ${escapeHtml(meta.items_summary)}</p>` : '';
      bubble.className = 'max-w-[85%] rounded-2xl p-3 bg-white border border-indigo-200 shadow-md text-gray-800 space-y-2';
      bubble.innerHTML = `
        <div class="border-b border-gray-100 pb-2">
          <div class="flex items-center justify-between gap-2">
            <span class="font-extrabold text-xs text-indigo-950">Order ${escapeHtml(meta.order_number)}</span>
            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-indigo-100 text-indigo-800">${escapeHtml(meta.delivery_status)}</span>
          </div>
          ${itemsText}
          <p class="text-[11px] text-gray-500 mt-0.5">${meta.items_count || 1} items • Total: <strong class="text-ink">${escapeHtml(meta.formatted_total || '৳' + meta.total)}</strong></p>
        </div>
        <a href="${escapeHtml(meta.tracking_url || '/track')}" target="_blank" class="block w-full py-1.5 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-[11px] rounded-lg text-center shadow-2xs transition">
          Track Order Details ↗
        </a>
      `;
    } else if (msg.type === 'coupon' && msg.metadata) {
      const meta = msg.metadata;
      bubble.className = 'max-w-[85%] rounded-2xl p-3 bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 shadow-md text-amber-950 space-y-2';
      bubble.innerHTML = `
        <div class="flex items-center justify-between border-b border-amber-200/60 pb-1.5">
          <span class="text-[10px] font-extrabold text-amber-800 uppercase tracking-wider">🎟️ Discount Gift Coupon</span>
          <span class="font-black text-xs text-amber-900">${escapeHtml(meta.discount_text)}</span>
        </div>
        <div class="bg-white p-2 rounded-lg border border-dashed border-amber-300 flex items-center justify-between">
          <code class="font-mono text-xs font-bold text-amber-900 tracking-wider">${escapeHtml(meta.code)}</code>
          <button type="button" onclick="navigator.clipboard.writeText('${escapeHtml(meta.code)}'); this.textContent='Copied!';" class="px-2 py-1 bg-amber-600 text-white font-bold text-[10px] rounded hover:bg-amber-700">Copy Code</button>
        </div>
      `;
    } else if (msg.type === 'voice' && msg.attachment_url) {
      bubble.className = 'max-w-[85%] rounded-2xl p-2 bg-white border border-gray-200 shadow-md text-gray-800 space-y-1';
      bubble.innerHTML = `
        <div class="flex items-center gap-1 text-[10px] font-extrabold text-brand-600">
          <span>🎙️ Voice Note</span>
        </div>
        <audio controls class="w-60 h-8 rounded max-w-full">
          <source src="${escapeHtml(msg.attachment_url)}" type="audio/webm">
          <source src="${escapeHtml(msg.attachment_url)}" type="audio/mp3">
        </audio>
      `;
    } else if (msg.type === 'image' || msg.attachment_url) {
      bubble.className = 'max-w-[85%] rounded-2xl p-1 bg-white border border-gray-200 shadow-md overflow-hidden';
      bubble.innerHTML = `
        <a href="${escapeHtml(msg.attachment_url)}" target="_blank">
          <img src="${escapeHtml(msg.attachment_url)}" class="max-h-48 w-full object-cover rounded-xl" alt="Attachment" />
        </a>
      `;
    } else {
      bubble.className = `max-w-[82%] rounded-2xl px-3.5 py-2 text-xs font-medium leading-relaxed shadow-xs ${
        isCustomer 
          ? 'bg-brand-600 text-white rounded-br-none' 
          : 'bg-white text-gray-800 border border-gray-100 rounded-bl-none shadow-sm'
      }`;
      bubble.textContent = msg.message;
    }

    const time = document.createElement('span');
    time.className = 'text-[9px] text-gray-400 mt-1 px-1 font-semibold';
    time.textContent = msg.time || '';

    wrap.appendChild(bubble);
    wrap.appendChild(time);
    return wrap;
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;

    input.value = '';
    const tempMsg = {
      id: Date.now(),
      sender_type: 'customer',
      message: text,
      time: 'Just now'
    };
    msgList.appendChild(createBubble(tempMsg));
    scrollToBottom();

    if (snippetEl) snippetEl.textContent = text;
    if (timeEl) timeEl.textContent = 'Just now';

    const formData = new FormData();
    formData.append('message', text);
    formData.append('_token', token);
    if (guestNameInput && guestNameInput.value.trim()) {
      formData.append('customer_name', guestNameInput.value.trim());
    }

    try {
      const res = await fetch("{{ route('chat.send') }}", {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
      });
      const data = await res.json();
      if (data.success && data.message) {
        if (data.message.id > lastMessageId) {
          lastMessageId = data.message.id;
        }
      }
    } catch (e) {
      showToast('Message delivery failed');
    }
  });

  // Voice Note Recording
  if (micBtn) {
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
          sendVoiceBlob(audioBlob);
        };

        mediaRecorder.start();
        recordSeconds = 0;
        recordingOverlay.classList.remove('hidden');
        recordTimerInterval = setInterval(() => {
          recordSeconds++;
          const m = String(Math.floor(recordSeconds / 60)).padStart(2, '0');
          const s = String(recordSeconds % 60).padStart(2, '0');
          recordTimerEl.textContent = `${m}:${s}`;
        }, 1000);
      } catch (err) {
        showToast('Microphone permission required for voice notes');
      }
    });
  }

  cancelRecordBtn?.addEventListener('click', () => {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
      audioChunks = [];
      mediaRecorder.stop();
    }
    clearInterval(recordTimerInterval);
    recordingOverlay.classList.add('hidden');
  });

  stopSendRecordBtn?.addEventListener('click', () => {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
      mediaRecorder.stop();
    }
    clearInterval(recordTimerInterval);
    recordingOverlay.classList.add('hidden');
  });

  async function sendVoiceBlob(blob) {
    showToast('Sending voice note...');
    const formData = new FormData();
    formData.append('audio', blob, 'voice_note.webm');
    formData.append('_token', token);

    try {
      const res = await fetch("{{ route('chat.send-voice') }}", {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
      });
      const data = await res.json();
      if (data.success && data.message) {
        msgList.appendChild(createBubble(data.message));
        scrollToBottom();
        if (data.message.id > lastMessageId) lastMessageId = data.message.id;
      }
    } catch (e) {
      showToast('Failed to send voice note');
    }
  }

  if (fileInput) {
    fileInput.addEventListener('change', async () => {
      const file = fileInput.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append('image', file);
      formData.append('_token', token);

      showToast('Uploading photo...');

      try {
        const res = await fetch("{{ route('chat.send-attachment') }}", {
          method: 'POST',
          headers: { 'Accept': 'application/json' },
          body: formData
        });
        const data = await res.json();
        if (data.success && data.message) {
          msgList.appendChild(createBubble(data.message));
          scrollToBottom();
          if (data.message.id > lastMessageId) lastMessageId = data.message.id;
        }
      } catch (e) {
        showToast('Photo upload failed');
      } finally {
        fileInput.value = '';
      }
    });
  }

  function playChime() {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(587.33, ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15);
      gain.gain.setValueAtTime(0.1, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + 0.15);
    } catch (e) {}
  }

  function startPolling() {
    stopPolling();
    pollTimer = setInterval(fetchMessages, 4000);
  }

  function stopPolling() {
    if (pollTimer) clearInterval(pollTimer);
  }

  function scrollToBottom() {
    msgList.scrollTop = msgList.scrollHeight;
  }

  function isNearBottom() {
    return msgList.scrollHeight - msgList.scrollTop - msgList.clientHeight < 120;
  }

  function showToast(msg) {
    const t = document.createElement('div');
    t.className = 'fixed bottom-24 right-5 bg-stone-800 text-white text-[11px] font-bold px-3 py-1.5 rounded-lg shadow-lg z-[70]';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2500);
  }

  function escapeHtml(str) {
    return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
})();
</script>
