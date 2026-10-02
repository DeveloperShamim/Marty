{{-- Calls & staff notes: log a call with its result or a private note; below, who did what on this order. --}}
@php
  $activities = $order->activities;
  $calls = $activities->where('type', 'call');
  $lastCall = $calls->first();
  $confirmedBy = $activities->first(fn ($a) => ($a->type === 'status' && str_contains((string) $a->body, '→ Confirmed'))
      || ($a->type === 'call' && $a->call_result === 'confirmed'));
  $shown = 6;
@endphp
<section class="card max-lg:order-4" id="calls">
  <div class="flex items-center justify-between gap-2 px-4 sm:px-5 py-3 border-b border-slate-100">
    <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-900"><x-oi name="phone" class="w-4 h-4 text-slate-400" /> Calls &amp; staff notes</h3>
    <span class="text-xs text-slate-400">{{ $calls->count() }} {{ \Illuminate\Support\Str::plural('call', $calls->count()) }}</span>
  </div>

  <div class="p-4 sm:p-5 space-y-4">
    @if($confirmedBy || $lastCall)
      <dl class="grid gap-1 text-xs">
        @if($confirmedBy)
          <div class="flex items-center gap-2 min-w-0"><x-oi name="check-circle" class="w-3.5 h-3.5 text-emerald-600" /><dt class="text-slate-500 shrink-0">Confirmed by</dt><dd class="font-medium text-slate-900 truncate">{{ $confirmedBy->staff_name }} · {{ $confirmedBy->created_at->diffForHumans() }}</dd></div>
        @endif
        @if($lastCall)
          <div class="flex items-center gap-2 min-w-0"><x-oi name="clock" class="w-3.5 h-3.5 text-slate-400" /><dt class="text-slate-500 shrink-0">Last call</dt><dd class="font-medium text-slate-900 truncate">{{ $lastCall->staff_name }} · {{ $lastCall->created_at->diffForHumans() }} · {{ $lastCall->callLabel() }}</dd></div>
        @endif
      </dl>
    @endif

    {{-- Tapping a call result saves at once (with anything typed in the box); "Save note" saves the text alone. --}}
    <form method="POST" action="{{ route('admin.orders.activities.store', $order) }}" class="space-y-2.5"
          onsubmit="setTimeout(() => this.querySelectorAll('button').forEach(b => b.disabled = true))">
      @csrf
      <div>
        <label for="activityBody" class="sr-only">What did they say?</label>
        <textarea id="activityBody" name="body" rows="2" maxlength="1000" class="inp text-sm" placeholder="What did they say? (optional) Or a private note…">{{ old('body') }}</textarea>
        @error('body')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
      </div>
      <fieldset>
        <legend class="text-xs text-slate-500 mb-1.5">Called the customer? Tap the result to save</legend>
        <div class="grid grid-cols-3 gap-1.5">
          @foreach(\App\Models\OrderActivity::CALL_RESULTS as $key => [$label, $icon, $tone, $short])
            <button type="submit" name="call_result" value="{{ $key }}" title="{{ $label }}"
                    class="flex flex-col items-center justify-center gap-1 px-1 py-2 rounded-lg ring-1 ring-slate-200 bg-white text-[11px] font-medium text-slate-600 text-center leading-tight hover:bg-brand-50 hover:ring-brand-600 hover:text-brand-800 active:scale-95 transition cursor-pointer disabled:opacity-50">
              <x-oi :name="$icon" class="w-4 h-4" />{{ $short }}
            </button>
          @endforeach
        </div>
      </fieldset>
      <div class="flex items-center justify-between gap-2">
        <span class="text-[11px] text-slate-400">{{ $order->status === 'pending' ? '"Confirmed" also confirms the order.' : 'Only staff see this.' }}</span>
        <button type="submit" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg ring-1 ring-slate-300 text-xs font-medium text-slate-700 hover:bg-slate-50 cursor-pointer disabled:opacity-50"><x-oi name="note" class="w-3.5 h-3.5" /> Save note</button>
      </div>
    </form>

    @if($activities->isNotEmpty())
      <div class="pt-3 border-t border-slate-100">
        <ol class="relative space-y-3.5 before:absolute before:left-3.5 before:top-2 before:bottom-2 before:w-px before:bg-slate-200">
          @foreach($activities->take($shown) as $a)
            @include('admin.orders.partials.activity-item')
          @endforeach
        </ol>
        @if($activities->count() > $shown)
          <details class="mt-3">
            <summary class="text-xs font-medium text-brand-700 cursor-pointer">Show {{ $activities->count() - $shown }} older</summary>
            <ol class="relative mt-3 space-y-3.5 before:absolute before:left-3.5 before:top-2 before:bottom-2 before:w-px before:bg-slate-200">
              @foreach($activities->slice($shown) as $a)
                @include('admin.orders.partials.activity-item')
              @endforeach
            </ol>
          </details>
        @endif
      </div>
    @else
      <p class="text-xs text-slate-400 pt-3 border-t border-slate-100">No calls or notes yet. Status, payment and courier changes appear here with who made them.</p>
    @endif
  </div>
</section>
