@php
    $name = $siteName ?? site_name();
    $href = $href ?? route('home');
    $light = $light ?? false;
    $size = $size ?? 'md';
    $class = $class ?? '';
    $iconOnly = $iconOnly ?? false;
    $custom = has_custom_logo();

    $textClass = match ($size) {
        'sm' => 'text-lg sm:text-xl',
        'lg' => 'text-2xl sm:text-3xl',
        default => 'text-xl sm:text-2xl',
    };
    $customClass = match ($size) {
        'sm' => 'h-8 w-auto max-w-[140px]',
        'lg' => 'h-11 w-auto max-w-[200px]',
        default => 'h-9 sm:h-10 w-auto max-w-[180px]',
    };
@endphp

<a href="{{ $href }}" class="flex items-center gap-2 shrink-0 min-w-0 {{ $class }}" aria-label="{{ $name }}">
  @if($custom)
    <img src="{{ logo_url() }}" alt="{{ $name }}" class="{{ $customClass }} object-contain" />
  @else
    <span class="h-10 w-10 rounded-lg {{ $light ? 'bg-white text-brand-700' : 'bg-brand-600 text-white' }} flex items-center justify-center shrink-0">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M6 7h13l-1.2 8H7.5L6 7Zm0 0-.8-3H3"/>
        <path d="M10 11v2M14 11v2"/>
        <path d="M12 4c0 2-1.5 3-1.5 3S9 6 9 4a1.5 1.5 0 0 1 3 0z"/>
      </svg>
    </span>
    @unless($iconOnly)
      <span class="leading-tight">
        <span class="block {{ $textClass }} font-extrabold tracking-tight {{ $light ? 'text-white' : 'text-brand-700' }}">{{ $name }}</span>
        @if(setting('tagline') && $size !== 'sm')
          <span class="hidden xl:block text-[9px] font-bold tracking-wider {{ $light ? 'text-white/70' : 'text-stone-400' }} uppercase truncate max-w-[220px]">{{ setting('tagline') }}</span>
        @endif
      </span>
    @endunless
  @endif
</a>
