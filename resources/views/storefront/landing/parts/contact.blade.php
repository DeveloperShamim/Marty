@if($page->sectionOn('contact') && (filled($contact['phone'] ?? null) || $wa !== '' || $messengerUrl))
  <section class="py-10" data-lp-section="contact">
    <div class="max-w-xl mx-auto px-4">
      <div class="rounded-2xl border border-gray-200 p-5 text-center">
        @if(filled($contact['heading'] ?? null))<h2 class="text-lg sm:text-xl font-bold">{{ $contact['heading'] }}</h2>@endif
        <div class="mt-4 flex flex-wrap justify-center gap-3">
          @if(filled($contact['phone'] ?? null))
            <a href="tel:{{ preg_replace('/[^\d+]/', '', $contact['phone']) }}" class="inline-flex h-11 items-center gap-2 rounded-xl border border-gray-300 px-4 font-semibold">📞 {{ $contact['phone'] }}</a>
          @endif
          @if($wa !== '')
            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="inline-flex h-11 items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-4 font-semibold text-emerald-700">WhatsApp</a>
          @endif
          @if($messengerUrl)
            <a href="{{ $messengerUrl }}" target="_blank" rel="noopener" class="inline-flex h-11 items-center gap-2 rounded-xl border border-blue-300 bg-blue-50 px-4 font-semibold text-blue-700">Messenger</a>
          @endif
        </div>
      </div>
    </div>
  </section>
@endif
