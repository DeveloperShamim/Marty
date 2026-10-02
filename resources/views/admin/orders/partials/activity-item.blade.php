<li class="relative flex gap-3">
  <span class="relative z-10 w-7 h-7 shrink-0 rounded-full ring-4 ring-white flex items-center justify-center {{ $a->dotClass() }}"><x-oi :name="$a->icon()" class="w-3.5 h-3.5" /></span>
  <div class="min-w-0 flex-1 pt-0.5">
    <p class="text-xs text-slate-500">
      <span class="font-medium text-slate-900">{{ $a->staff_name ?? 'System' }}</span>
      · <time datetime="{{ $a->created_at->toIso8601String() }}" title="{{ $a->created_at->format('d M Y, g:i A') }}">{{ $a->created_at->diffForHumans() }}</time>
    </p>
    @if($a->type === 'call')
      <span class="inline-block mt-1 px-2 py-0.5 rounded-full ring-1 text-[11px] font-medium {{ $a->badgeClass() }}">{{ $a->callLabel() }}</span>
    @endif
    @if($a->body)
      <p class="text-sm mt-0.5 break-words {{ in_array($a->type, ['call', 'note'], true) ? 'text-slate-800' : 'text-slate-500' }}">{{ $a->body }}</p>
    @endif
  </div>
</li>
