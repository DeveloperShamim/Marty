{{-- Template picker: a radio card per LandingPage::TEMPLATES entry with a small drawing of its look.
     $selected: the picked key; $tryUrl (optional): the page link, to open each template as a preview. --}}
<div class="grid {{ $cols ?? 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5' }} gap-2.5" role="radiogroup" aria-label="Template" data-lp-templates>
  @foreach(\App\Models\LandingPage::TEMPLATES as $key => [$name, $hint, $accent, [$bg, $card, $ink]])
    <label class="group relative cursor-pointer rounded-xl ring-1 ring-gray-200 bg-white p-2 has-[:checked]:ring-2 has-[:checked]:ring-gray-900 hover:ring-gray-300" title="{{ $hint }}" data-lp-template="{{ $key }}">
      <input type="radio" name="template" value="{{ $key }}" class="sr-only" @checked($selected === $key)>
      {{-- Mini page --}}
      <span class="block aspect-[4/3] rounded-lg overflow-hidden ring-1 ring-black/5 p-1.5" style="background: {{ $bg }};" aria-hidden="true">
        <span class="block h-1 rounded-full mb-1.5" style="background: {{ $accent }}; width: 100%; opacity: .9;"></span>
        @if($key === 'split')
          <span class="grid grid-cols-2 gap-1.5 h-[70%]">
            <span class="rounded" style="background: {{ $card }};"></span>
            <span class="flex flex-col justify-center gap-1">
              <span class="block h-1.5 rounded-full w-full" style="background: {{ $ink }};"></span>
              <span class="block h-1.5 rounded-full w-2/3" style="background: {{ $accent }};"></span>
              <span class="block h-2.5 rounded mt-1" style="background: {{ $accent }};"></span>
            </span>
          </span>
        @else
          <span class="block h-1.5 rounded-full mx-auto w-3/4" style="background: {{ $ink }};"></span>
          <span class="block h-1.5 rounded-full mx-auto w-1/2 mt-1" style="background: {{ $accent }};"></span>
          <span class="block mx-auto mt-1.5 w-1/2 h-[38%] {{ $key === 'minimal' ? '' : 'rounded' }}" style="background: {{ $card }};"></span>
          <span class="block mx-auto mt-1.5 w-1/2 h-2.5 {{ $key === 'minimal' ? '' : 'rounded' }}" style="background: {{ $accent }};"></span>
        @endif
      </span>
      <span class="mt-1.5 block text-[12px] font-semibold text-gray-900">{{ $name }}</span>
      <span class="block text-[11px] leading-snug text-gray-500 line-clamp-2">{{ $hint }}</span>
      @isset($tryUrl)
        <a href="{{ $tryUrl }}?template={{ $key }}" target="_blank" class="mt-1 inline-block text-[11px] font-medium underline text-gray-700 hover:text-gray-900" data-lp-try="{{ $key }}">Preview</a>
      @endisset
    </label>
  @endforeach
</div>
