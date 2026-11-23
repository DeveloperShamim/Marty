@if($page->sectionOn('ticker') && $ticker->isNotEmpty())
  <div class="overflow-hidden border-b border-gray-100 bg-gray-50 py-2" data-lp-section="ticker">
    <div class="lp-ticker">
      @for($copy = 0; $copy < 2; $copy++)
        <div class="flex shrink-0 items-center" @if($copy) aria-hidden="true" @endif>
          @for($rep = 0; $rep < 2; $rep++)
            @foreach($ticker as $line)
              <span class="px-5 text-[13px] sm:text-sm font-medium text-gray-700 whitespace-nowrap">{{ $line }}</span>
              <span class="lp-accent text-xs">●</span>
            @endforeach
          @endfor
        </div>
      @endfor
    </div>
  </div>
@endif
