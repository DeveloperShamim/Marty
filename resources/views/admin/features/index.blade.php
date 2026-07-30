@extends('layouts.admin')
@section('title', 'Features')

@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-xl font-bold">Features (homepage strip)</h2>
    <a href="{{ route('admin.features.create') }}" class="btn-primary">+ New feature</a>
  </div>

  <div class="card overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-gray-500 bg-gray-50">
        <tr><th class="px-5 py-3 font-medium">Icon</th><th class="px-5 py-3 font-medium">Title</th><th class="px-5 py-3 font-medium">Subtitle</th><th class="px-5 py-3 font-medium">Pos</th><th class="px-5 py-3 font-medium text-right">Actions</th></tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($features as $feature)
          <tr class="hover:bg-gray-50">
            <td class="px-5 py-3"><div class="flex items-center justify-center w-8 h-8">{!! $feature->renderIconHtml('h-6 w-6', 'text-primary') !!}</div></td>
            <td class="px-5 py-3 font-medium">{{ $feature->title }}</td>
            <td class="px-5 py-3 text-gray-500">{{ $feature->subtitle }}</td>
            <td class="px-5 py-3">{{ $feature->position }}</td>
            <td class="px-5 py-3 text-right whitespace-nowrap">
              <a href="{{ route('admin.features.edit', $feature) }}" class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">Edit</a>
              <form method="POST" action="{{ route('admin.features.destroy', $feature) }}" class="inline" onsubmit="return confirm('Delete feature?')">@csrf @method('DELETE')<button class="px-2 py-1 text-xs rounded bg-red-50 text-red-600 border border-red-200">Delete</button></form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">No features yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
