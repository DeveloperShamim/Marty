{{-- The one "See all" button under homepage sections: full width on phones so it is easy to spot and tap --}}
<div class="mt-4 sm:mt-6 flex justify-center">
  <a href="{{ $href }}" class="group w-full sm:w-auto inline-flex items-center justify-center gap-1.5 h-10 sm:h-11 px-6 rounded-xl border border-stone-300/80 bg-white text-[13px] sm:text-sm font-semibold text-stone-800 hover:border-brand-500/60 hover:text-brand-600 shadow-2xs transition-colors active:scale-[0.98]" data-see-all>
    <span class="truncate">See all {{ $label }}</span>
    <svg class="w-4 h-4 shrink-0 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
  </a>
</div>
