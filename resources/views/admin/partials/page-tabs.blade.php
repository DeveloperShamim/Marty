{{-- Pages that share one menu entry (e.g. Catalog setup) switch between each other here. --}}
@php $pageTabs = \App\Support\AdminNav::currentTabs(auth()->user()); @endphp
@if($pageTabs)
  <nav class="pb-3 sm:pb-4 -mx-0.5 px-0.5 overflow-x-auto no-scrollbar" aria-label="Section" data-page-tabs>
    <div class="inline-flex items-center gap-1 p-1 rounded-full bg-white shadow-panel whitespace-nowrap">
      @foreach($pageTabs as $tab)
        @php $active = request()->routeIs($tab['pattern']); @endphp
        <a href="{{ route($tab['route']) }}"
           class="h-8 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center transition-colors {{ $active ? 'text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
           @if($active) style="background: var(--brand-dark);" aria-current="true" @endif>{{ $tab['label'] }}</a>
      @endforeach
    </div>
  </nav>
@endif
