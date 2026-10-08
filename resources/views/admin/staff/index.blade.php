@extends('layouts.admin')
@section('title', 'Staff')
@section('subtitle', 'Manage team accounts, roles and access.')

@section('page-actions')
  <button type="button" onclick="document.getElementById('createStaffModal').classList.remove('hidden')" class="pill-btn pill-btn-dark cursor-pointer">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
    Add staff
  </button>
@endsection

@php
  $roleMeta = [
    'admin'             => ['Super admin', 'Full access', 'bg-violet-50 text-violet-700'],
    'store_manager'     => ['Store manager', 'Orders and catalog', 'bg-emerald-50 text-emerald-700'],
    'order_manager'     => ['Order manager', 'Orders and support', 'bg-sky-50 text-sky-700'],
    'inventory_manager' => ['Inventory manager', 'Catalog and stock', 'bg-amber-50 text-amber-700'],
  ];
@endphp

@section('content')
<div class="space-y-4 max-w-full">

  {{-- Role summary --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    @foreach($roleMeta as $key => [$label, $hint, $tone])
      <div class="panel p-3.5 sm:p-4">
        <div class="flex items-center justify-between gap-2">
          <span class="text-xs text-gray-500 truncate">{{ $label }}s</span>
          <span class="grid h-8 w-8 place-items-center rounded-xl {{ $tone }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
          </span>
        </div>
        <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ $counts[$key] ?? 0 }}</p>
        <p class="text-[11px] text-gray-400 mt-0.5">{{ $hint }}</p>
      </div>
    @endforeach
  </div>

  {{-- Role tabs --}}
  @php
    $tabs = [
      ''                  => 'All staff',
      'admin'             => 'Super admins',
      'store_manager'     => 'Store managers',
      'order_manager'     => 'Order managers',
      'inventory_manager' => 'Inventory managers',
    ];
  @endphp
  <nav class="-mx-3 sm:mx-0 px-3 sm:px-0 overflow-x-auto no-scrollbar" aria-label="Staff role">
    <div class="inline-flex items-center gap-1 p-1 rounded-full bg-white shadow-panel whitespace-nowrap">
      @foreach($tabs as $key => $label)
        @php $active = ($role === $key) || ($key === '' && !$role); $countKey = $key === '' ? 'all' : $key; @endphp
        <a href="{{ route('admin.staff.index', ['role' => $key, 'q' => $search]) }}"
           class="h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors {{ $active ? 'text-white' : 'text-gray-600 hover:bg-gray-100' }}"
           @if($active) style="background: var(--brand-dark);" aria-current="page" @endif>
          {{ $label }}
          @if(isset($counts[$countKey]))
            <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-semibold leading-5 text-center tabular-nums {{ $active ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts[$countKey] }}</span>
          @endif
        </a>
      @endforeach
    </div>
  </nav>

  {{-- Staff list --}}
  <div class="card overflow-hidden">
    <form method="GET" action="{{ route('admin.staff.index') }}" class="p-3 sm:p-4 flex items-center gap-2">
      <input type="hidden" name="role" value="{{ $role }}" />
      <label class="relative flex-1">
        <span class="sr-only">Search staff</span>
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search name, email or phone" class="w-full h-10 pl-10 pr-4 rounded-full bg-gray-100 border border-transparent text-sm focus:bg-white focus:border-gray-200 outline-none" />
      </label>
      <button type="submit" class="h-10 px-4 rounded-full text-white text-[13px] font-semibold shrink-0 cursor-pointer" style="background: var(--brand-dark);">Search</button>
    </form>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="whitespace-nowrap border-y border-gray-100">
            <th class="py-3 px-4">Name</th>
            <th class="py-3 px-4">Role</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4">Joined</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($staffMembers as $member)
            @php [$rLabel, $rHint, $rTone] = $roleMeta[$member->role] ?? [ucfirst($member->role), '', 'bg-gray-100 text-gray-700']; @endphp
            <tr>
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <span class="h-9 w-9 rounded-full font-semibold text-xs grid place-items-center shrink-0 {{ $member->role === 'admin' ? 'bg-violet-50 text-violet-700' : 'bg-gray-100 text-gray-700' }}">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
                  <div class="min-w-0">
                    <p class="font-semibold text-gray-900 truncate">{{ $member->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $member->email }}</p>
                    @if($member->phone)
                      <p class="text-[11px] text-gray-400 tabular-nums">{{ $member->phone }}</p>
                    @endif
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 whitespace-nowrap">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $rTone }}">{{ $rLabel }}</span>
                @if($rHint)<p class="text-[11px] text-gray-400 mt-1">{{ $rHint }}</p>@endif
              </td>
              <td class="py-3 px-4 whitespace-nowrap">
                @if($member->is_suspended)
                  <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700">Suspended</span>
                @else
                  <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">Active</span>
                @endif
              </td>
              <td class="py-3 px-4 text-gray-500 whitespace-nowrap">{{ $member->created_at ? $member->created_at->format('d M Y') : 'N/A' }}</td>
              <td class="py-3 px-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  @if(! $member->isStoreOwner() && $member->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.staff.toggle', $member) }}" class="inline">
                      @csrf
                      @method('PATCH')
                      @if($member->is_suspended)
                        <button type="submit" class="h-8 px-3 rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold cursor-pointer">Activate</button>
                      @else
                        <button type="submit" class="h-8 px-3 rounded-full bg-gray-100 text-gray-800 hover:bg-gray-200 text-xs font-semibold cursor-pointer">Suspend</button>
                      @endif
                    </form>
                    <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" class="inline" onsubmit="return confirm('Delete staff account {{ $member->name }}?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="h-8 px-3 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold cursor-pointer">Delete</button>
                    </form>
                  @else
                    <span class="text-xs text-gray-400">Owner account</span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="px-5 py-12 text-center text-gray-500 text-sm">No staff members match your search.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Phone cards --}}
    <div class="md:hidden px-3 pb-3 space-y-2">
      @forelse($staffMembers as $member)
        @php [$rLabel, $rHint, $rTone] = $roleMeta[$member->role] ?? [ucfirst($member->role), '', 'bg-gray-100 text-gray-700']; @endphp
        <article class="rounded-2xl bg-gray-50/80 p-3.5 space-y-2.5">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
              <span class="h-9 w-9 rounded-full font-semibold text-xs grid place-items-center shrink-0 {{ $member->role === 'admin' ? 'bg-violet-50 text-violet-700' : 'bg-white text-gray-700' }}">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
              <div class="min-w-0">
                <p class="font-semibold text-sm text-gray-900 truncate">{{ $member->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ $member->email }}</p>
                @if($member->phone)
                  <p class="text-[11px] text-gray-400 tabular-nums">{{ $member->phone }}</p>
                @endif
              </div>
            </div>
            @if($member->is_suspended)
              <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 shrink-0">Suspended</span>
            @else
              <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 shrink-0">Active</span>
            @endif
          </div>
          <div class="flex items-center gap-2 flex-wrap text-[11px] text-gray-500">
            <span class="px-2 py-0.5 rounded-full font-semibold {{ $rTone }}">{{ $rLabel }}</span>
            <span>Joined {{ $member->created_at ? $member->created_at->format('d M Y') : 'N/A' }}</span>
          </div>
          @if(! $member->isStoreOwner() && $member->id !== auth()->id())
            <div class="grid grid-cols-2 gap-1.5">
              <form method="POST" action="{{ route('admin.staff.toggle', $member) }}">
                @csrf @method('PATCH')
                <button type="submit" class="w-full h-8 rounded-full text-xs font-semibold {{ $member->is_suspended ? 'bg-emerald-50 text-emerald-700' : 'bg-white text-gray-800 ring-1 ring-gray-200' }}">{{ $member->is_suspended ? 'Activate' : 'Suspend' }}</button>
              </form>
              <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" onsubmit="return confirm('Delete staff member {{ $member->name }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full h-8 rounded-full bg-rose-50 text-rose-700 text-xs font-semibold">Delete</button>
              </form>
            </div>
          @else
            <p class="text-xs text-gray-400">Owner account</p>
          @endif
        </article>
      @empty
        <div class="py-10 text-center text-sm text-gray-500">No staff members found.</div>
      @endforelse
    </div>

    @if($staffMembers->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-gray-100">{{ $staffMembers->links() }}</div>
    @endif
  </div>

</div>

{{-- Add staff modal --}}
<div id="createStaffModal" class="fixed inset-0 bg-gray-950/50 z-50 flex items-center justify-center p-4 hidden">
  <div class="bg-white rounded-2xl p-5 w-full max-w-md shadow-2xl max-h-[calc(100vh-2rem)] overflow-y-auto">
    <div class="flex items-center justify-between gap-3">
      <h3 class="text-[15px] font-semibold text-gray-900">Add staff member</h3>
      <button type="button" onclick="document.getElementById('createStaffModal').classList.add('hidden')" class="h-8 w-8 grid place-items-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 cursor-pointer" aria-label="Close">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>

    <form method="POST" action="{{ route('admin.staff.store') }}" class="mt-4 space-y-3">
      @csrf
      <div>
        <label class="lbl">Full name <span class="text-rose-500">*</span></label>
        <input type="text" name="name" required placeholder="e.g. Rafi Ahmed" class="w-full h-10 rounded-xl border border-gray-200 px-3.5 text-sm" />
      </div>
      <div>
        <label class="lbl">Email (used to log in) <span class="text-rose-500">*</span></label>
        <input type="email" name="email" required placeholder="e.g. rafi@yourstore.com" class="w-full h-10 rounded-xl border border-gray-200 px-3.5 text-sm" />
      </div>
      <div>
        <label class="lbl">Phone (optional)</label>
        <input type="text" name="phone" placeholder="e.g. 01700-000000" class="w-full h-10 rounded-xl border border-gray-200 px-3.5 text-sm" />
      </div>
      <div>
        <label class="lbl">Role <span class="text-rose-500">*</span></label>
        <select name="role" required class="w-full h-10 rounded-xl border border-gray-200 px-3 text-sm bg-white cursor-pointer">
          <option value="store_manager">Store manager (orders and catalog)</option>
          <option value="order_manager">Order manager (orders and support, no financials)</option>
          <option value="inventory_manager">Inventory manager (products and stock only)</option>
          <option value="admin">Super admin (full access)</option>
        </select>
      </div>
      <div>
        <label class="lbl">Temporary password <span class="text-rose-500">*</span></label>
        <input type="password" name="password" required minlength="8" placeholder="At least 8 characters" class="w-full h-10 rounded-xl border border-gray-200 px-3.5 text-sm" />
      </div>
      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('createStaffModal').classList.add('hidden')" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium cursor-pointer">Cancel</button>
        <button type="submit" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold cursor-pointer" style="background: var(--brand-dark);">Create account</button>
      </div>
    </form>
  </div>
</div>
@endsection
