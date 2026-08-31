@extends('layouts.admin')
@section('title', 'Employee Control & Staff Management')

@section('content')
<div class="space-y-5 sm:space-y-6 max-w-full">

  {{-- Header & Actions Ribbon --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs">
    <div>
      <div class="flex items-center gap-2 flex-wrap">
        <h1 class="text-base sm:text-xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
          <span>🔑</span> Staff &amp; Team Management
        </h1>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-stone-100 text-stone-700 border border-stone-200">
          {{ $counts['all'] ?? 0 }} Staff Accounts
        </span>
      </div>
      <p class="text-xs text-stone-500 mt-1">
        Manage employee accounts, assign role permissions, and control administrative access levels.
      </p>
    </div>

    <button type="button" onclick="document.getElementById('createStaffModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-1.5 self-start sm:self-auto shrink-0 cursor-pointer">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      <span>Add New Staff</span>
    </button>
  </div>

  {{-- Status Alerts --}}
  @if(session('status'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-extrabold px-4 py-3 shadow-2xs flex items-center gap-2">
      <span>✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm font-extrabold px-4 py-3 shadow-2xs">
      <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Quick Role Summary KPI Ribbon --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 sm:gap-4">
    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-purple-900 uppercase tracking-wider block">👑 Super Admins</span>
      <p class="text-xl sm:text-2xl font-black text-purple-800 font-mono tracking-tight">{{ $counts['admin'] ?? 0 }}</p>
      <span class="text-[10px] text-stone-400 font-semibold block">Full system control</span>
    </div>

    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-emerald-900 uppercase tracking-wider block">👔 Store Managers</span>
      <p class="text-xl sm:text-2xl font-black text-emerald-800 font-mono tracking-tight">{{ $counts['store_manager'] ?? 0 }}</p>
      <span class="text-[10px] text-stone-400 font-semibold block">Orders &amp; Catalog</span>
    </div>

    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-sky-900 uppercase tracking-wider block">📦 Order Managers</span>
      <p class="text-xl sm:text-2xl font-black text-sky-800 font-mono tracking-tight">{{ $counts['order_manager'] ?? 0 }}</p>
      <span class="text-[10px] text-stone-400 font-semibold block">Orders &amp; Support</span>
    </div>

    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-amber-900 uppercase tracking-wider block">🏭 Inventory Managers</span>
      <p class="text-xl sm:text-2xl font-black text-amber-800 font-mono tracking-tight">{{ $counts['inventory_manager'] ?? 0 }}</p>
      <span class="text-[10px] text-stone-400 font-semibold block">Catalog &amp; Stock</span>
    </div>
  </div>

  {{-- Filters & Search Card --}}
  <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-stone-100 pb-3.5">
      
      {{-- Role Tabs --}}
      @php
        $tabs = [
          ''                  => 'All Staff',
          'admin'             => '👑 Super Admins',
          'store_manager'     => '👔 Store Managers',
          'order_manager'     => '📦 Order Managers',
          'inventory_manager' => '🏭 Inventory Managers',
        ];
      @endphp
      <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 md:pb-0">
        @foreach($tabs as $key => $label)
          @php $active = ($role === $key) || ($key === '' && !$role); @endphp
          <a href="{{ route('admin.staff.index', ['role' => $key, 'q' => $search]) }}" class="px-3 py-1.5 text-xs font-black rounded-xl transition whitespace-nowrap {{ $active ? 'bg-stone-900 text-white shadow-2xs' : 'bg-stone-50 text-stone-600 hover:bg-stone-100' }}">
            {{ $label }}
            @if(isset($counts[$key === '' ? 'all' : $key]))
              <span class="ml-1 px-1.5 py-0.2 text-[10px] rounded-full {{ $active ? 'bg-white/20 text-white font-mono' : 'bg-stone-200 text-stone-700 font-mono' }}">
                {{ $counts[$key === '' ? 'all' : $key] }}
              </span>
            @endif
          </a>
        @endforeach
      </div>

      {{-- Search --}}
      <form method="GET" action="{{ route('admin.staff.index') }}" class="flex items-center gap-2 w-full md:w-auto">
        <input type="hidden" name="role" value="{{ $role }}" />
        
        <div class="relative flex-1 sm:w-64">
          <input type="text" name="q" value="{{ $search }}" placeholder="Search name, email, phone..." class="w-full pl-8 pr-3 py-2 text-xs font-semibold bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500" />
          <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <button type="submit" class="px-3.5 py-2 rounded-xl bg-stone-900 text-white font-extrabold text-xs hover:bg-stone-800 transition cursor-pointer shadow-2xs">
          Search
        </button>
      </form>
    </div>

    {{-- Desktop Table View (`hidden md:block`) --}}
    <div class="hidden md:block overflow-x-auto rounded-xl border border-stone-200">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-stone-100/90 text-stone-700 font-black border-b border-stone-200 uppercase text-[11px] tracking-wider whitespace-nowrap">
            <th class="py-3 px-4">Employee Information</th>
            <th class="py-3 px-4">Role &amp; Permission Access</th>
            <th class="py-3 px-4 text-center">Account Status</th>
            <th class="py-3 px-4 text-center">Joined Date</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 bg-white">
          @forelse($staffMembers as $member)
            <tr class="hover:bg-stone-50/80 transition-colors">
              <td class="py-3.5 px-4">
                <div class="flex items-center gap-3">
                  <div class="h-9 w-9 rounded-xl font-black text-xs flex items-center justify-center shrink-0 border {{ $member->role === 'admin' ? 'bg-purple-100 text-purple-900 border-purple-300' : 'bg-stone-100 text-stone-700 border-stone-200' }}">
                    {{ strtoupper(substr($member->name, 0, 2)) }}
                  </div>
                  <div class="min-w-0">
                    <p class="font-extrabold text-stone-900 text-xs sm:text-sm truncate">{{ $member->name }}</p>
                    <p class="text-[11px] text-stone-500 font-mono mt-0.5">{{ $member->email }}</p>
                    @if($member->phone)
                      <p class="text-[10px] text-stone-400 font-mono">📞 {{ $member->phone }}</p>
                    @endif
                  </div>
                </div>
              </td>

              {{-- Role Badge --}}
              <td class="py-3.5 px-4 whitespace-nowrap">
                @if($member->role === 'admin')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-black rounded-lg bg-purple-50 text-purple-900 border border-purple-200 shadow-2xs">
                    👑 Super Admin (Full Access)
                  </span>
                @elseif($member->role === 'store_manager')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-black rounded-lg bg-emerald-50 text-emerald-900 border border-emerald-200">
                    👔 Store Manager (Orders &amp; Catalog)
                  </span>
                @elseif($member->role === 'order_manager')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-black rounded-lg bg-sky-50 text-sky-900 border border-sky-200">
                    📦 Order Manager (Orders &amp; Support)
                  </span>
                @elseif($member->role === 'inventory_manager')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-black rounded-lg bg-amber-50 text-amber-900 border border-amber-200">
                    🏭 Inventory Manager (Catalog &amp; Stock)
                  </span>
                @endif
              </td>

              {{-- Status --}}
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                @if($member->is_suspended)
                  <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-rose-100 text-rose-800 border border-rose-200">
                    🚫 Suspended
                  </span>
                @else
                  <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                    ● Active
                  </span>
                @endif
              </td>

              {{-- Joined Date --}}
              <td class="py-3.5 px-4 text-center text-stone-500 font-medium whitespace-nowrap text-xs">
                {{ $member->created_at ? $member->created_at->format('d M, Y') : 'N/A' }}
              </td>

              {{-- Actions --}}
              <td class="py-3.5 px-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  @if($member->email !== 'admin@freshkart.test' && $member->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.staff.toggle', $member) }}" class="inline">
                      @csrf
                      @method('PATCH')
                      @if($member->is_suspended)
                        <button type="submit" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition cursor-pointer shadow-2xs">
                          ✓ Activate
                        </button>
                      @else
                        <button type="submit" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 transition cursor-pointer shadow-2xs">
                          🚫 Suspend
                        </button>
                      @endif
                    </form>

                    <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" class="inline" onsubmit="return confirm('Delete staff account {{ $member->name }}?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="px-2.5 py-1.5 text-xs font-bold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition cursor-pointer">
                        Delete
                      </button>
                    </form>
                  @else
                    <span class="text-[11px] text-stone-400 font-bold italic px-2 py-1 bg-stone-50 rounded-lg border border-stone-200">
                      Master Account
                    </span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-5 py-12 text-center text-stone-400 text-xs italic bg-stone-50/50">
                👥 No staff members found matching your search.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile Cards View (`block md:hidden`) --}}
    <div class="block md:hidden divide-y divide-stone-100 bg-white">
      @forelse($staffMembers as $member)
        <div class="p-4 space-y-3">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl font-black text-xs flex items-center justify-center shrink-0 border {{ $member->role === 'admin' ? 'bg-purple-100 text-purple-900 border-purple-300' : 'bg-stone-100 text-stone-700 border-stone-200' }}">
                {{ strtoupper(substr($member->name, 0, 2)) }}
              </div>
              <div class="min-w-0">
                <p class="font-extrabold text-sm text-stone-900 truncate">{{ $member->name }}</p>
                <p class="text-xs font-mono text-stone-500 truncate mt-0.5">{{ $member->email }}</p>
                @if($member->phone)
                  <p class="text-[11px] font-mono text-stone-400">📞 {{ $member->phone }}</p>
                @endif
              </div>
            </div>

            <div class="text-right shrink-0">
              @if($member->is_suspended)
                <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-rose-100 text-rose-800 border border-rose-200 block">
                  Suspended
                </span>
              @else
                <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 block">
                  Active
                </span>
              @endif
            </div>
          </div>

          <div class="p-2 bg-stone-50 rounded-xl border border-stone-200/80 flex items-center justify-between text-xs">
            <span class="text-[11px] font-bold text-stone-500">Access Role:</span>
            <span class="font-black text-stone-800 text-[11px]">
              {{ match($member->role) {
                'admin' => '👑 Super Admin',
                'store_manager' => '👔 Store Manager',
                'order_manager' => '📦 Order Manager',
                'inventory_manager' => '🏭 Inventory Manager',
                default => ucfirst($member->role)
              } }}
            </span>
          </div>

          <div class="flex items-center justify-between gap-2 pt-1 text-xs">
            <span class="text-[11px] text-stone-400 font-medium">Joined: {{ $member->created_at ? $member->created_at->format('d M, Y') : 'N/A' }}</span>

            <div class="flex items-center gap-1.5">
              @if($member->email !== 'admin@freshkart.test' && $member->id !== auth()->id())
                <form method="POST" action="{{ route('admin.staff.toggle', $member) }}">
                  @csrf @method('PATCH')
                  <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-xl border transition {{ $member->is_suspended ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                    {{ $member->is_suspended ? 'Activate' : 'Suspend' }}
                  </button>
                </form>

                <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" onsubmit="return confirm('Delete staff member {{ $member->name }}?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-rose-50 text-rose-700 border border-rose-200">
                    Delete
                  </button>
                </form>
              @else
                <span class="text-[11px] text-stone-400 font-bold italic">Master Account</span>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="p-8 text-center text-xs text-stone-400 bg-stone-50">
          No staff members found.
        </div>
      @endforelse
    </div>

    @if($staffMembers->hasPages())
      <div class="p-4 border-t border-stone-100 bg-stone-50/40">{{ $staffMembers->links() }}</div>
    @endif
  </div>

</div>

{{-- Create Staff Member Modal --}}
<div id="createStaffModal" class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
  <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 p-6 w-full max-w-md space-y-4 shadow-2xl animate-in fade-in zoom-in duration-150">
    <div class="flex items-center justify-between border-b border-stone-100 pb-3">
      <h3 class="font-black text-base text-stone-900 flex items-center gap-2">
        <span>➕</span> Add New Staff Member
      </h3>
      <button type="button" onclick="document.getElementById('createStaffModal').classList.add('hidden')" class="text-stone-400 hover:text-stone-700 text-sm cursor-pointer p-1">✕</button>
    </div>

    <form method="POST" action="{{ route('admin.staff.store') }}" class="space-y-4 text-xs">
      @csrf

      <div class="space-y-1">
        <label class="block font-black text-stone-700">Full Name <span class="text-rose-500">*</span></label>
        <input type="text" name="name" required placeholder="e.g. Rafi Ahmed" class="w-full px-3.5 py-2.5 text-xs font-bold bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500" />
      </div>

      <div class="space-y-1">
        <label class="block font-black text-stone-700">Email Address (Login Username) <span class="text-rose-500">*</span></label>
        <input type="email" name="email" required placeholder="e.g. rafi@yourstore.com" class="w-full px-3.5 py-2.5 text-xs font-bold bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500" />
      </div>

      <div class="space-y-1">
        <label class="block font-black text-stone-700">Phone Number (Optional)</label>
        <input type="text" name="phone" placeholder="e.g. 01700-000000" class="w-full px-3.5 py-2.5 text-xs font-bold bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500" />
      </div>

      <div class="space-y-1">
        <label class="block font-black text-stone-700">Assign Access Role <span class="text-rose-500">*</span></label>
        <select name="role" required class="w-full px-3.5 py-2.5 text-xs font-black bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer shadow-2xs">
          <option value="store_manager">👔 Store Manager (Orders &amp; Catalog Management)</option>
          <option value="order_manager">📦 Order Manager (Orders &amp; Support — No Dashboard / Financials)</option>
          <option value="inventory_manager">🏭 Inventory Manager (Products &amp; Stock Only)</option>
          <option value="admin">👑 Super Admin (Full Access to Everything)</option>
        </select>
      </div>

      <div class="space-y-1">
        <label class="block font-black text-stone-700">Temporary Password <span class="text-rose-500">*</span></label>
        <input type="password" name="password" required placeholder="Min 6 characters" class="w-full px-3.5 py-2.5 text-xs font-bold bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500" />
      </div>

      <div class="flex items-center justify-end gap-2 pt-2 border-t border-stone-100">
        <button type="button" onclick="document.getElementById('createStaffModal').classList.add('hidden')" class="px-4 py-2.5 text-xs font-bold text-stone-600 hover:text-stone-900 rounded-xl border border-stone-200 bg-stone-50 cursor-pointer">Cancel</button>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition cursor-pointer">Create Staff Account</button>
      </div>
    </form>
  </div>
</div>
@endsection
