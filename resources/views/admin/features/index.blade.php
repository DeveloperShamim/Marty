@extends('layouts.admin')
@section('title', 'Trust features')
@section('subtitle', 'Badges shown in the homepage strip, like free delivery or cash on delivery.')

@section('page-actions')
  <a href="{{ route('admin.features.create') }}" class="pill-btn pill-btn-dark">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
    Add feature
  </a>
@endsection

@section('content')
<div class="card overflow-hidden">

  {{-- Phone: one card per feature --}}
  <div class="md:hidden p-3 space-y-2.5">
    @forelse($features as $feature)
      <article class="rounded-2xl bg-gray-50/80 p-3.5">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center text-gray-700 shrink-0">
            {!! $feature->renderIconHtml('h-5 w-5', 'text-brand-600') !!}
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-gray-900 text-sm leading-tight">{{ $feature->title }}</h3>
            @if($feature->subtitle)<p class="text-xs text-gray-500 mt-0.5">{{ $feature->subtitle }}</p>@endif
          </div>
          <span class="px-2 py-0.5 rounded-full bg-white text-gray-600 text-[11px] font-semibold tabular-nums shrink-0">#{{ $feature->position }}</span>
        </div>
        <div class="mt-3 flex items-center gap-1.5">
          <a href="{{ route('admin.features.edit', $feature) }}" class="flex-1 h-9 rounded-full text-white text-xs font-semibold inline-flex items-center justify-center" style="background: var(--brand-dark);">Edit</a>
          <form method="POST" action="{{ route('admin.features.destroy', $feature) }}" onsubmit="return confirm('Delete feature \'{{ addslashes($feature->title) }}\'?')">
            @csrf @method('DELETE')
            <button type="submit" class="h-9 px-4 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold cursor-pointer">Delete</button>
          </form>
        </div>
      </article>
    @empty
      <div class="py-10 text-center">
        <p class="text-sm text-gray-500">No feature badges added yet.</p>
        <a href="{{ route('admin.features.create') }}" class="mt-2 inline-block text-xs font-semibold text-gray-900 hover:underline">Add the first one</a>
      </div>
    @endforelse
  </div>

  {{-- Desktop table --}}
  <div class="hidden md:block overflow-x-auto">
    <table class="w-full text-left text-[13px]">
      <thead>
        <tr class="text-gray-500 text-xs font-medium border-b border-gray-100 bg-gray-50/60">
          <th class="py-3 px-4 w-16">Icon</th>
          <th class="py-3 px-4">Title</th>
          <th class="py-3 px-4">Subtitle</th>
          <th class="py-3 px-4 text-center">Position</th>
          <th class="py-3 px-4 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($features as $feature)
          <tr class="hover:bg-gray-50/70 transition-colors">
            <td class="py-3 px-4">
              <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 text-gray-700">
                {!! $feature->renderIconHtml('h-5 w-5', 'text-brand-600') !!}
              </div>
            </td>
            <td class="py-3 px-4 font-semibold text-gray-900">{{ $feature->title }}</td>
            <td class="py-3 px-4 text-gray-600">{{ $feature->subtitle }}</td>
            <td class="py-3 px-4 text-center">
              <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-700 tabular-nums">#{{ $feature->position }}</span>
            </td>
            <td class="py-3 px-4 text-right whitespace-nowrap">
              <div class="flex items-center justify-end gap-1.5">
                <a href="{{ route('admin.features.edit', $feature) }}" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center">Edit</a>
                <form method="POST" action="{{ route('admin.features.destroy', $feature) }}" class="inline" onsubmit="return confirm('Delete feature \'{{ addslashes($feature->title) }}\'?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="h-8 px-3 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-medium cursor-pointer">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="py-12 text-center text-gray-500">No features yet. Use “Add feature” to create the first badge.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>
@endsection
