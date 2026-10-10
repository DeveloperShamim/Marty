{{-- Step-by-step setup guides for the Tracking cards, in English and Bangla.
     Each card's "Guide" button opens the matching dialog; the language choice is remembered. --}}
@php
  $guides = [
    'google' => [
      'title' => 'Google setup guide',
      'en' => [
        ['h' => 'Google Analytics 4', 'steps' => [
          'Open <b>analytics.google.com</b> and sign in. If you have no account yet, create an account and a property for your shop.',
          'Click <b>Admin</b> (the gear icon at the bottom left) → <b>Data streams</b> → <b>Add stream</b> → <b>Web</b>. Type your website address and create the stream.',
          'Copy the <b>Measurement ID</b>. It starts with <code>G-</code>. Paste it in <b>GA4 Measurement ID</b>.',
          'Optional, to count orders even when a browser blocks Google: on the same stream open <b>Measurement Protocol API secrets</b> → <b>Create</b>. Copy the <b>Secret value</b> into <b>GA4 API secret</b>.',
        ]],
        ['h' => 'Google Ads purchase conversion', 'steps' => [
          'Open <b>ads.google.com</b> → <b>Goals</b> → <b>Conversions</b> → <b>Summary</b> → <b>New conversion action</b> → <b>Website</b>.',
          'Type your website address, then add a conversion action manually. Choose the category <b>Purchase</b> and save.',
          'Choose <b>Use Google tag</b> or <b>Install the tag yourself</b>. In the event snippet find <code>send_to: \'AW-123456789/AbC-D_efG\'</code>.',
          'Paste the part before the slash (<code>AW-123456789</code>) in <b>Google Ads conversion ID</b>, and the part after the slash (<code>AbC-D_efG</code>) in <b>Google Ads purchase label</b>. You do not need to paste the code itself.',
        ]],
        ['h' => 'Search Console', 'steps' => [
          'Open <b>search.google.com/search-console</b> → <b>Add property</b> → <b>URL prefix</b> and type your website address.',
          'Choose the <b>HTML tag</b> method. From the tag, copy only the code inside <code>content="…"</code> and paste it in <b>Search Console tag</b>.',
          'Click <b>Save tracking settings</b> here first, then click <b>Verify</b> in Search Console.',
        ]],
        ['h' => 'Save and check', 'steps' => [
          'Click <b>Save tracking settings</b>.',
          'In Google Analytics open <b>Reports</b> → <b>Realtime</b>, then open your shop on your phone. You should appear within a minute.',
        ]],
      ],
      'en_note' => 'Tag Manager is optional. Only fill it if you already use Google Tag Manager, and do not add GA4 inside Tag Manager as well, or every visit is counted twice.',
      'bn' => [
        ['h' => 'Google Analytics 4', 'steps' => [
          '<b>analytics.google.com</b> খুলে সাইন ইন করুন। অ্যাকাউন্ট না থাকলে আপনার শপের জন্য একটি অ্যাকাউন্ট ও প্রপার্টি তৈরি করুন।',
          '<b>Admin</b> (নিচে বাঁ দিকের গিয়ার আইকন) → <b>Data streams</b> → <b>Add stream</b> → <b>Web</b> চাপুন। আপনার ওয়েবসাইটের ঠিকানা লিখে স্ট্রিম তৈরি করুন।',
          '<b>Measurement ID</b> কপি করুন, এটি <code>G-</code> দিয়ে শুরু হয়। এখানে <b>GA4 Measurement ID</b> ঘরে পেস্ট করুন।',
          'ঐচ্ছিক: ব্রাউজার Google ব্লক করলেও অর্ডার গুনতে, একই স্ট্রিমে <b>Measurement Protocol API secrets</b> → <b>Create</b> চাপুন। <b>Secret value</b> কপি করে <b>GA4 API secret</b> ঘরে দিন।',
        ]],
        ['h' => 'Google Ads পারচেজ কনভার্সন', 'steps' => [
          '<b>ads.google.com</b> → <b>Goals</b> → <b>Conversions</b> → <b>Summary</b> → <b>New conversion action</b> → <b>Website</b> চাপুন।',
          'ওয়েবসাইটের ঠিকানা লিখুন, তারপর নিজে একটি কনভার্সন অ্যাকশন যোগ করুন। ক্যাটাগরি <b>Purchase</b> বেছে সেভ করুন।',
          '<b>Use Google tag</b> বা <b>Install the tag yourself</b> বেছে নিন। ইভেন্ট কোডে <code>send_to: \'AW-123456789/AbC-D_efG\'</code> খুঁজুন।',
          'স্ল্যাশের আগের অংশ (<code>AW-123456789</code>) <b>Google Ads conversion ID</b> ঘরে, আর স্ল্যাশের পরের অংশ (<code>AbC-D_efG</code>) <b>Google Ads purchase label</b> ঘরে দিন। পুরো কোড পেস্ট করার দরকার নেই।',
        ]],
        ['h' => 'Search Console', 'steps' => [
          '<b>search.google.com/search-console</b> → <b>Add property</b> → <b>URL prefix</b> চাপুন এবং ওয়েবসাইটের ঠিকানা লিখুন।',
          '<b>HTML tag</b> পদ্ধতি বেছে নিন। ট্যাগ থেকে শুধু <code>content="…"</code> এর ভেতরের কোডটি কপি করে <b>Search Console tag</b> ঘরে দিন।',
          'আগে এখানে <b>Save tracking settings</b> চাপুন, তারপর Search Console এ <b>Verify</b> চাপুন।',
        ]],
        ['h' => 'সেভ ও যাচাই', 'steps' => [
          '<b>Save tracking settings</b> চাপুন।',
          'Google Analytics এ <b>Reports</b> → <b>Realtime</b> খুলুন, তারপর ফোনে আপনার শপ খুলুন। এক মিনিটের মধ্যে আপনাকে দেখা যাবে।',
        ]],
      ],
      'bn_note' => 'Tag Manager ঐচ্ছিক। আগে থেকে Google Tag Manager ব্যবহার করলে তবেই দিন, আর Tag Manager এর ভেতরে আবার GA4 যোগ করবেন না, না হলে প্রতিটি ভিজিট দুইবার গোনা হবে।',
    ],

    'meta' => [
      'title' => 'Facebook Pixel & Conversions API guide',
      'en' => [
        ['h' => 'Pixel', 'steps' => [
          'Open <b>business.facebook.com</b> → <b>Events Manager</b>. If you already have a pixel, select it. If not, click <b>Connect data sources</b> → <b>Web</b>, give it a name and create it.',
          'Copy the <b>Pixel ID</b> (also called Dataset ID, a long number) and paste it in <b>Meta Pixel / Dataset ID</b>. You do not need to paste the pixel code.',
        ]],
        ['h' => 'Domain verification', 'steps' => [
          'Open <b>Business settings</b> → <b>Brand safety</b> → <b>Domains</b> → <b>Add</b> and type your domain, without https:// or www.',
          'Choose the <b>Meta-tag</b> method. Copy the whole tag, or just the code inside <code>content="…"</code>, and paste it in <b>Domain verification</b>.',
          'Click <b>Save tracking settings</b> here first, then click <b>Verify</b> in Meta.',
        ]],
        ['h' => 'Conversions API (server-side)', 'steps' => [
          'In Events Manager select your pixel → <b>Settings</b> → <b>Conversions API</b> → <b>Generate access token</b>.',
          'Copy the token (it starts with <code>EAA</code>) and paste it in <b>Access token</b>. Keep it private, like a password.',
          'Turn on the <b>Conversions API</b> switch and click <b>Save tracking settings</b>.',
        ]],
        ['h' => 'Test it', 'steps' => [
          'In Events Manager open <b>Test events</b> and copy the test code, for example <code>TEST12345</code>. Paste it in <b>Test event code</b> and save.',
          'Click <b>Test Meta connection</b>. A Purchase event should appear in Test events within a few seconds.',
          'Then clear <b>Test event code</b> and save again. While a test code is saved, real orders only show in Test events.',
        ]],
      ],
      'en_note' => 'Purchases from the pixel and from the Conversions API share the same event ID, so Meta counts each order once.',
      'bn' => [
        ['h' => 'পিক্সেল', 'steps' => [
          '<b>business.facebook.com</b> → <b>Events Manager</b> খুলুন। আগে থেকে পিক্সেল থাকলে সেটি বেছে নিন। না থাকলে <b>Connect data sources</b> → <b>Web</b> চেপে একটি নাম দিয়ে তৈরি করুন।',
          '<b>Pixel ID</b> (Dataset ID ও বলে, একটি লম্বা নম্বর) কপি করে <b>Meta Pixel / Dataset ID</b> ঘরে দিন। পিক্সেল কোড পেস্ট করার দরকার নেই।',
        ]],
        ['h' => 'ডোমেইন ভেরিফিকেশন', 'steps' => [
          '<b>Business settings</b> → <b>Brand safety</b> → <b>Domains</b> → <b>Add</b> চাপুন এবং https:// বা www ছাড়া আপনার ডোমেইন লিখুন।',
          '<b>Meta-tag</b> পদ্ধতি বেছে নিন। পুরো ট্যাগ, অথবা শুধু <code>content="…"</code> এর ভেতরের কোড কপি করে <b>Domain verification</b> ঘরে দিন।',
          'আগে এখানে <b>Save tracking settings</b> চাপুন, তারপর Meta তে <b>Verify</b> চাপুন।',
        ]],
        ['h' => 'Conversions API (সার্ভার থেকে)', 'steps' => [
          'Events Manager এ আপনার পিক্সেল বেছে <b>Settings</b> → <b>Conversions API</b> → <b>Generate access token</b> চাপুন।',
          'টোকেনটি (<code>EAA</code> দিয়ে শুরু) কপি করে <b>Access token</b> ঘরে দিন। পাসওয়ার্ডের মতো এটি কাউকে দেবেন না।',
          '<b>Conversions API</b> সুইচ চালু করে <b>Save tracking settings</b> চাপুন।',
        ]],
        ['h' => 'টেস্ট করুন', 'steps' => [
          'Events Manager এ <b>Test events</b> খুলে টেস্ট কোড কপি করুন, যেমন <code>TEST12345</code>। <b>Test event code</b> ঘরে দিয়ে সেভ করুন।',
          '<b>Test Meta connection</b> চাপুন। কয়েক সেকেন্ডের মধ্যে Test events এ একটি Purchase ইভেন্ট দেখা যাবে।',
          'এরপর <b>Test event code</b> ঘর খালি করে আবার সেভ করুন। টেস্ট কোড সেভ থাকলে আসল অর্ডারগুলো শুধু Test events এ দেখায়।',
        ]],
      ],
      'bn_note' => 'পিক্সেল আর Conversions API দুটো থেকেই আসা পারচেজে একই ইভেন্ট আইডি থাকে, তাই Meta প্রতিটি অর্ডার একবারই গোনে।',
    ],

    'tiktok' => [
      'title' => 'TikTok Pixel guide',
      'en' => [
        ['h' => 'Get your Pixel ID', 'steps' => [
          'Open <b>ads.tiktok.com</b> → <b>Tools</b> → <b>Events</b> → <b>Web Events</b> → <b>Set up web events</b>.',
          'Give the pixel a name and choose <b>Manually install pixel code</b>.',
          'Copy the <b>Pixel ID</b>. It is a code of capital letters and numbers that usually starts with <code>C</code>. Paste it in <b>TikTok Pixel ID</b>. You do not need to paste the pixel code.',
          'Click <b>Save tracking settings</b>.',
        ]],
        ['h' => 'Check it', 'steps' => [
          'In TikTok Events Manager open your pixel → <b>Test events</b>, then open your shop. You should see PageView, then ViewContent and AddToCart as you browse.',
          'Orders arrive as <b>CompletePayment</b>.',
        ]],
      ],
      'bn' => [
        ['h' => 'Pixel ID নিন', 'steps' => [
          '<b>ads.tiktok.com</b> → <b>Tools</b> → <b>Events</b> → <b>Web Events</b> → <b>Set up web events</b> চাপুন।',
          'পিক্সেলের একটি নাম দিন এবং <b>Manually install pixel code</b> বেছে নিন।',
          '<b>Pixel ID</b> কপি করুন। এটি বড় হাতের অক্ষর ও সংখ্যার কোড, সাধারণত <code>C</code> দিয়ে শুরু হয়। <b>TikTok Pixel ID</b> ঘরে দিন। পিক্সেল কোড পেস্ট করার দরকার নেই।',
          '<b>Save tracking settings</b> চাপুন।',
        ]],
        ['h' => 'যাচাই করুন', 'steps' => [
          'TikTok Events Manager এ আপনার পিক্সেল → <b>Test events</b> খুলে শপটি খুলুন। PageView, তারপর ঘোরাঘুরি করলে ViewContent ও AddToCart দেখা যাবে।',
          'অর্ডারগুলো <b>CompletePayment</b> নামে আসবে।',
        ]],
      ],
    ],

    'custom' => [
      'title' => 'Custom scripts guide',
      'en' => [
        ['h' => 'Add another service', 'steps' => [
          'Copy the tracking code from the service. For example in Microsoft Clarity: <b>Settings</b> → <b>Setup</b> → <b>Install manually</b>.',
          'If the service says to put the code in the <code>&lt;head&gt;</code>, paste it in <b>Inside &lt;head&gt;</b>.',
          'If it says to put it right after the opening <code>&lt;body&gt;</code> tag (often a <code>&lt;noscript&gt;</code> part), paste that part in <b>Right after &lt;body&gt;</b>.',
          'Click <b>Save tracking settings</b>, open your shop, then check the service\'s dashboard.',
        ]],
      ],
      'en_note' => 'Do not paste Google, Facebook or TikTok codes here when their IDs are filled in above, or every visit is counted twice. If the shop looks broken after saving, clear the box and save again.',
      'bn' => [
        ['h' => 'অন্য সার্ভিস যোগ করুন', 'steps' => [
          'সার্ভিস থেকে ট্র্যাকিং কোড কপি করুন। যেমন Microsoft Clarity তে: <b>Settings</b> → <b>Setup</b> → <b>Install manually</b>।',
          'সার্ভিস যদি কোডটি <code>&lt;head&gt;</code> এ রাখতে বলে, তাহলে <b>Inside &lt;head&gt;</b> ঘরে দিন।',
          'যদি <code>&lt;body&gt;</code> ট্যাগের ঠিক পরে রাখতে বলে (প্রায়ই <code>&lt;noscript&gt;</code> অংশ), তাহলে সেই অংশটি <b>Right after &lt;body&gt;</b> ঘরে দিন।',
          '<b>Save tracking settings</b> চাপুন, শপটি খুলুন, তারপর সার্ভিসের ড্যাশবোর্ডে দেখুন।',
        ]],
      ],
      'bn_note' => 'উপরে Google, Facebook বা TikTok এর আইডি দেওয়া থাকলে তাদের কোড এখানে আবার দেবেন না, না হলে প্রতিটি ভিজিট দুইবার গোনা হবে। সেভের পর শপ ঠিকমতো না দেখালে ঘরটি খালি করে আবার সেভ করুন।',
    ],
  ];
@endphp

@foreach($guides as $key => $g)
  <dialog id="guide-{{ $key }}" data-guide-dialog aria-labelledby="guide-{{ $key }}-title"
          class="w-[calc(100%-1.5rem)] max-w-lg max-h-[85vh] p-0 rounded-2xl bg-white shadow-xl backdrop:bg-black/40">
    <div class="flex flex-col max-h-[85vh]">
      <div class="flex items-center gap-2 px-4 sm:px-5 pt-4 pb-3 border-b border-gray-100">
        <h3 id="guide-{{ $key }}-title" class="flex-1 min-w-0 text-[15px] font-semibold text-gray-900">{{ $g['title'] }}</h3>
        <div class="inline-flex p-0.5 rounded-full bg-gray-100 text-[12px] font-medium shrink-0" role="group" aria-label="Language">
          <button type="button" data-guide-lang="en" class="h-7 px-3 rounded-full">English</button>
          <button type="button" data-guide-lang="bn" class="h-7 px-3 rounded-full">বাংলা</button>
        </div>
        <button type="button" data-guide-close class="h-8 w-8 grid place-items-center rounded-full text-gray-500 hover:bg-gray-100 shrink-0" aria-label="Close">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
      </div>
      <div class="overflow-y-auto px-4 sm:px-5 py-4">
        @foreach(['en', 'bn'] as $lang)
          <div data-guide-body="{{ $lang }}" lang="{{ $lang }}" class="space-y-5 text-[13px] leading-relaxed text-gray-700 [&_b]:font-semibold [&_b]:text-gray-900 [&_code]:font-mono [&_code]:text-[12px] [&_code]:bg-gray-100 [&_code]:px-1 [&_code]:rounded" @if($lang === 'bn') style="display:none" @endif>
            @foreach($g[$lang] as $section)
              <div>
                <h4 class="text-[13px] font-semibold text-gray-900 mb-2">{{ $section['h'] }}</h4>
                <ol class="space-y-2">
                  @foreach($section['steps'] as $i => $step)
                    <li class="flex gap-2.5">
                      <span class="shrink-0 h-5 w-5 mt-px grid place-items-center rounded-full text-[11px] font-semibold text-white" style="background: var(--brand-dark);">{{ $lang === 'bn' ? strtr((string) ($i + 1), ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']) : $i + 1 }}</span>
                      <span class="min-w-0">{!! $step !!}</span>
                    </li>
                  @endforeach
                </ol>
              </div>
            @endforeach
            @if(! empty($g[$lang . '_note']))
              <p class="rounded-xl bg-amber-50 text-amber-900 px-3 py-2.5 text-[12px]">{{ $g[$lang . '_note'] }}</p>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  </dialog>
@endforeach

<script>
  (function () {
    var KEY = 'vt-guide-lang';
    function getLang() { try { return localStorage.getItem(KEY) === 'bn' ? 'bn' : 'en'; } catch (e) { return 'en'; } }
    function setLang(dialog, lang) {
      dialog.querySelectorAll('[data-guide-body]').forEach(function (b) { b.style.display = b.dataset.guideBody === lang ? '' : 'none'; });
      dialog.querySelectorAll('[data-guide-lang]').forEach(function (btn) {
        var on = btn.dataset.guideLang === lang;
        btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        btn.style.background = on ? 'var(--brand-dark)' : '';
        btn.style.color = on ? '#fff' : '';
      });
    }
    document.querySelectorAll('[data-guide-open]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var d = document.getElementById('guide-' + btn.dataset.guideOpen);
        if (!d) return;
        setLang(d, getLang());
        d.showModal();
      });
    });
    document.querySelectorAll('[data-guide-dialog]').forEach(function (d) {
      d.querySelectorAll('[data-guide-lang]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          try { localStorage.setItem(KEY, btn.dataset.guideLang); } catch (e) {}
          setLang(d, btn.dataset.guideLang);
        });
      });
      d.querySelector('[data-guide-close]').addEventListener('click', function () { d.close(); });
      // Tap outside the panel closes it.
      d.addEventListener('click', function (e) { if (e.target === d) d.close(); });
    });
  })();
</script>
