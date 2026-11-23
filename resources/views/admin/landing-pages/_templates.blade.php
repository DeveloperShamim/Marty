{{-- Template picker: a radio card per LandingPage::TEMPLATES entry with a small drawing of its look.
     $selected: the picked key; $tryUrl (optional): the page link, to open each template as a preview. --}}
<div class="grid {{ $cols ?? 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5' }} gap-2.5" role="radiogroup" aria-label="Template" data-lp-templates>
  @foreach(\App\Models\LandingPage::TEMPLATES as $key => [$name, $hint, $accent, [$bg, $card, $ink]])
    <label class="group relative cursor-pointer rounded-xl ring-1 ring-gray-200 bg-white p-2 has-[:checked]:ring-2 has-[:checked]:ring-gray-900 hover:ring-gray-300" title="{{ $hint }}" data-lp-template="{{ $key }}">
      <input type="radio" name="template" value="{{ $key }}" class="sr-only" @checked($selected === $key)>
      {{-- Mini drawing of the template's layout --}}
      <span class="block aspect-[4/3] rounded-lg overflow-hidden ring-1 ring-black/5" style="background: {{ $bg }};" aria-hidden="true">
        @switch($key)
          @case('bold')
            <span class="grid grid-cols-2 gap-1.5 h-full p-2 items-center">
              <span class="space-y-1">
                <span class="block h-1 w-1/3 rounded-full" style="background: {{ $accent }};"></span>
                <span class="block h-2 w-full rounded-sm" style="background: {{ $ink }};"></span>
                <span class="block h-2 w-4/5 rounded-sm" style="background: {{ $accent }};"></span>
                <span class="block h-2.5 w-3/5 rounded-full mt-1.5" style="background: {{ $accent }};"></span>
              </span>
              <span class="block h-4/5 rounded-md" style="background: {{ $card }}; box-shadow: 0 0 12px {{ $accent }}55;"></span>
            </span>
            @break
          @case('minimal')
            <span class="grid grid-cols-[1fr_1.1fr] gap-1.5 h-full p-2">
              <span class="space-y-1"><span class="block h-[47%]" style="background: {{ $card }};"></span><span class="block h-[47%]" style="background: {{ $card }};"></span></span>
              <span class="space-y-1 pt-1">
                <span class="block h-1.5 w-full" style="background: {{ $ink }};"></span>
                <span class="block h-1.5 w-2/3" style="background: {{ $ink }};"></span>
                <span class="block h-px w-full mt-1.5 bg-gray-300"></span>
                <span class="block h-2 w-full mt-1" style="background: {{ $accent }};"></span>
                <span class="block h-1 w-4/5 mt-1.5 bg-gray-300"></span><span class="block h-1 w-3/5 bg-gray-300"></span>
              </span>
            </span>
            @break
          @case('festive')
            <span class="block h-[38%] relative" style="background: {{ $accent }};"><span class="absolute inset-1 rounded border border-dashed border-yellow-300"></span><span class="absolute left-1/4 right-1/4 top-1/2 h-1.5 rounded-full bg-white/90"></span></span>
            <span class="grid grid-cols-2 gap-1.5 px-2 -mt-2">
              <span class="relative block h-9 rounded-md bg-white ring-2 ring-yellow-400"><span class="absolute -right-1 -top-1 h-3.5 w-3.5 rounded-full" style="background: {{ $accent }};"></span></span>
              <span class="block h-9 rounded-md border border-dashed border-yellow-600 bg-white"><span class="block mx-auto mt-2 h-1.5 w-2/3 rounded-full" style="background: {{ $accent }};"></span><span class="block mx-auto mt-1 h-2 w-3/4 rounded-full" style="background: {{ $accent }};"></span></span>
            </span>
            @break
          @case('split')
            <span class="grid grid-cols-2 gap-1.5 h-full p-2">
              <span class="space-y-1"><span class="block h-1.5 w-full rounded-full" style="background: {{ $ink }};"></span><span class="block h-[60%] rounded" style="background: {{ $card }};"></span><span class="block h-2 w-2/3 rounded" style="background: {{ $accent }};"></span></span>
              <span class="block rounded-md border-2 p-1 space-y-1" style="border-color: {{ $accent }};"><span class="block h-1.5 rounded-sm bg-gray-200"></span><span class="block h-1.5 rounded-sm bg-gray-200"></span><span class="block h-1.5 rounded-sm bg-gray-200"></span><span class="block h-2.5 rounded-sm mt-1.5" style="background: {{ $accent }};"></span></span>
            </span>
            @break
          @default
            <span class="block p-1.5">
              <span class="block h-1 rounded-full mb-1.5" style="background: {{ $accent }};"></span>
              <span class="block h-1.5 rounded-full mx-auto w-3/4" style="background: {{ $ink }};"></span>
              <span class="block h-1.5 rounded-full mx-auto w-1/2 mt-1" style="background: {{ $accent }};"></span>
              <span class="block mx-auto mt-1.5 w-1/2 h-8 rounded" style="background: {{ $card }};"></span>
              <span class="block mx-auto mt-1.5 w-1/2 h-2.5 rounded" style="background: {{ $accent }};"></span>
            </span>
        @endswitch
      </span>
      <span class="mt-1.5 block text-[12px] font-semibold text-gray-900">{{ $name }}</span>
      <span class="block text-[11px] leading-snug text-gray-500 line-clamp-2">{{ $hint }}</span>
      @isset($tryUrl)
        <a href="{{ $tryUrl }}?template={{ $key }}" target="_blank" class="mt-1 inline-block text-[11px] font-medium underline text-gray-700 hover:text-gray-900" data-lp-try="{{ $key }}">Preview</a>
      @endisset
    </label>
  @endforeach
</div>
