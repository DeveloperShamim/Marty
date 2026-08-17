@extends('layouts.admin')
@section('title', 'Homepage Trust Features Strip')

@section('content')
<div class="space-y-6">

  {{-- Page Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
        <span>✨ Homepage Trust Features Strip</span>
      </h1>
      <p class="text-xs sm:text-sm text-stone-500 mt-1">Manage trust badges (e.g. Free Shipping, Cash on Delivery, Organic Guarantee) shown on the homepage</p>
    </div>
    <a href="{{ route('admin.features.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all">
      <span>+ Add New Feature Badge</span>
    </a>
  </div>

  {{-- Mobile Cards View (Visible on Small Screens) --}}
  <div class="grid grid-cols-1 gap-3.5 md:hidden">
    @forelse($features as $feature)
      <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs space-y-3">
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-700 shrink-0 shadow-2xs">
              {!! $feature->renderIconHtml('h-6 w-6', 'text-brand-600') !!}
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-stone-100 font-extrabold text-stone-800 text-[10px]">#{{ $feature->position }}</span>
                <h3 class="font-extrabold text-stone-900 text-sm leading-tight">{{ $feature->title }}</h3>
              </div>
              <p class="text-xs text-stone-500 font-medium mt-0.5">{{ $feature->subtitle }}</p>
            </div>
          </div>
        </div>

        <div class="pt-2 border-t border-stone-100 flex items-center justify-end gap-2">
          <a href="{{ route('admin.features.edit', $feature) }}" class="px-3.5 py-1.5 text-xs font-extrabold rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 border border-stone-200">
            Edit
          </a>
          <form method="POST" action="{{ route('admin.features.destroy', $feature) }}" onsubmit="return confirm('Delete feature \'{{ addslashes($feature->title) }}\'?')">
            @csrf @method('DELETE')
            <button type="submit" class="px-3.5 py-1.5 text-xs font-extrabold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 cursor-pointer">
              Delete
            </button>
          </form>
        </div>
      </div>
    @empty
      <div class="bg-white p-8 text-center rounded-2xl border border-stone-200">
        <div class="text-3xl mb-2">✨</div>
        <p class="text-xs font-bold text-stone-600">No feature badges added yet.</p>
        <a href="{{ route('admin.features.create') }}" class="mt-2 inline-block text-xs font-bold text-brand-600 hover:underline">+ Add First Feature Badge</a>
      </div>
    @endforelse
  </div>

  {{-- Desktop Table View (Hidden on Small Screens) --}}
  <div class="hidden md:block bg-white rounded-2xl border border-stone-200 shadow-2xs overflow-hidden">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 border-b border-stone-200 font-extrabold text-stone-600 uppercase tracking-wider">
        <tr>
          <th class="px-5 py-3.5 w-16 text-center">Icon</th>
          <th class="px-5 py-3.5">Title</th>
          <th class="px-5 py-3.5">Subtitle / Description</th>
          <th class="px-5 py-3.5 text-center">Position</th>
          <th class="px-5 py-3.5 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-stone-100 font-medium">
        @forelse($features as $feature)
          <tr class="hover:bg-stone-50/70 transition-colors">
            <td class="px-5 py-3.5 text-center">
              <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-brand-50 border border-brand-100 text-brand-700 mx-auto shadow-2xs">
                {!! $feature->renderIconHtml('h-5 w-5', 'text-brand-600') !!}
              </div>
            </td>
            <td class="px-5 py-3.5 font-extrabold text-stone-900 text-sm">{{ $feature->title }}</td>
            <td class="px-5 py-3.5 text-stone-600 font-semibold">{{ $feature->subtitle }}</td>
            <td class="px-5 py-3.5 text-center">
              <span class="px-2.5 py-1 text-xs font-extrabold rounded-lg bg-stone-100 text-stone-800 border border-stone-200">
                #{{ $feature->position }}
              </span>
            </td>
            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1">
              <a href="{{ route('admin.features.edit', $feature) }}" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 border border-stone-200 transition">
                Edit
              </a>
              <form method="POST" action="{{ route('admin.features.destroy', $feature) }}" class="inline" onsubmit="return confirm('Delete feature \'{{ addslashes($feature->title) }}\'?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 cursor-pointer transition">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-5 py-12 text-center text-stone-400 font-bold">No features created yet. Add your first homepage feature badge above!</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>
@endsection
