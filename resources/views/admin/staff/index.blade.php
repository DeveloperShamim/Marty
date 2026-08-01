@extends('layouts.admin')
@section('title', 'Employee Control & Staff Management')

@section('content')
<div class="space-y-6 max-w-full">
  <!-- Top Header Title & Actions -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
    <div>
      <h2 class="text-2xl font-extrabold text-ink tracking-tight flex items-center gap-2.5">
        <span>🔑</span> Staff Management & Roles
      </h2>
      <p class="text-xs text-gray-500 mt-1">Manage employee accounts, assign role permissions, and control access.</p>
    </div>

    <!-- Create Staff Member Button -->
    <button type="button" onclick="document.getElementById('createStaffModal').classList.remove('hidden')" class="btn-primary text-xs px-4 py-2.5 shrink-0 flex items-center gap-2 cursor-pointer shadow-xs">
      <span>➕</span> Add New Employee
    </button>
  </div>

  <!-- Status Alerts -->
  @if(session('status'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-xs">
      ✓ {{ session('status') }}
    </div>
  @endif

  @if($errors->any())
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold shadow-xs">
      <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- Role Filter Tabs -->
  <div class="flex items-center gap-2 border-b border-gray-200 pb-3 overflow-x-auto">
    @php
      $tabs = [
        ''                  => 'All Staff',
        'admin'             => '👑 Super Admins',
        'store_manager'     => '👔 Store Managers',
        'order_manager'     => '📦 Order Managers',
        'inventory_manager' => '🏭 Inventory Managers',
      ];
    @endphp
    @foreach($tabs as $key => $label)
      @php $active = ($role === $key) || ($key === '' && !$role); @endphp
      <a href="{{ route('admin.staff.index', ['role' => $key, 'q' => $search]) }}"
         class="px-3.5 py-1.5 text-xs font-extrabold rounded-xl transition whitespace-nowrap {{ $active ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
        {{ $label }}
        @if(isset($counts[$key === '' ? 'all' : $key]))
          <span class="ml-1 px-1.5 py-0.5 text-[10px] rounded-full {{ $active ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700 font-bold' }}">
            {{ $counts[$key === '' ? 'all' : $key] }}
          </span>
        @endif
      </a>
    @endforeach
  </div>

  <!-- Staff Members Table Card -->
  <div class="card overflow-hidden">
    <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
        <span>👥</span> Staff Directory ({{ $staffMembers->total() }} total)
      </h3>

      <!-- Search -->
      <form method="GET" action="{{ route('admin.staff.index') }}" class="flex items-center gap-2">
        <input type="hidden" name="role" value="{{ $role }}" />
        <div class="relative w-64">
          <input type="text" name="q" value="{{ $search }}" placeholder="Search name, email, phone..." class="inp text-xs pr-8 py-2 w-full" />
          @if($search !== '')
            <a href="{{ route('admin.staff.index', ['role' => $role]) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">✕</a>
          @endif
        </div>
        <button type="submit" class="btn-primary text-xs px-3 py-2 shrink-0">Search</button>
      </form>
    </div>

    <div class="overflow-x-auto w-full">
      <table class="w-full text-left text-xs border-collapse min-w-[700px]">
        <thead>
          <tr class="bg-slate-900 text-white uppercase text-[11px] font-extrabold tracking-wider whitespace-nowrap">
            <th class="py-3.5 px-4">Employee</th>
            <th class="py-3.5 px-4">Role &amp; Access Level</th>
            <th class="py-3.5 px-4 text-center">Account Status</th>
            <th class="py-3.5 px-4 text-center">Joined Date</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          @forelse($staffMembers as $member)
            <tr class="hover:bg-slate-50 transition-colors">
              <td class="py-4 px-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <div class="h-9 w-9 rounded-full bg-slate-900 text-white font-extrabold flex items-center justify-center text-xs shrink-0 shadow-2xs">
                    {{ strtoupper(substr($member->name, 0, 2)) }}
                  </div>
                  <div>
                    <p class="font-extrabold text-slate-900 text-xs leading-tight">{{ $member->name }}</p>
                    <p class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $member->email }}</p>
                    @if($member->phone)
                      <p class="text-[10px] text-slate-400 font-mono">📞 {{ $member->phone }}</p>
                    @endif
                  </div>
                </div>
              </td>

              <!-- Role & Permissions Badge -->
              <td class="py-4 px-4 whitespace-nowrap">
                @if($member->role === 'admin')
                  <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-black rounded-full bg-purple-100 text-purple-800 border border-purple-200">
                    👑 Super Admin (Full Access)
                  </span>
                @elseif($member->role === 'store_manager')
                  <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-black rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                    👔 Store Manager (Orders &amp; Catalog)
                  </span>
                @elseif($member->role === 'order_manager')
                  <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-black rounded-full bg-sky-100 text-sky-800 border border-sky-200">
                    📦 Order Manager (Orders &amp; Support)
                  </span>
                @elseif($member->role === 'inventory_manager')
                  <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-black rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                    🏭 Inventory Manager (Catalog &amp; Stock)
                  </span>
                @endif
              </td>

              <!-- Status -->
              <td class="py-4 px-4 text-center whitespace-nowrap">
                @if($member->is_suspended)
                  <span class="inline-block px-2.5 py-1 text-[10px] font-black rounded-full bg-rose-100 text-rose-700 border border-rose-200">
                    🚫 Suspended
                  </span>
                @else
                  <span class="inline-block px-2.5 py-1 text-[10px] font-black rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">
                    🟢 Active
                  </span>
                @endif
              </td>

              <!-- Joined Date -->
              <td class="py-4 px-4 text-center text-slate-500 text-[11px] font-medium whitespace-nowrap">
                {{ $member->created_at ? $member->created_at->format('d M Y') : 'N/A' }}
              </td>

              <!-- Actions -->
              <td class="py-4 px-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-2">
                  <!-- Toggle Suspend -->
                  @if($member->email !== 'admin@freshkart.test' && $member->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.staff.toggle', $member) }}" class="inline">
                      @csrf
                      @method('PATCH')
                      @if($member->is_suspended)
                        <button type="submit" title="Re-activate Employee Account" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition cursor-pointer">
                          ✓ Activate
                        </button>
                      @else
                        <button type="submit" title="Suspend Employee Account" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition cursor-pointer">
                          🚫 Suspend
                        </button>
                      @endif
                    </form>

                    <!-- Delete -->
                    <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete staff account {{ $member->name }}?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" title="Delete Employee" class="w-8 h-8 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 font-bold transition inline-flex items-center justify-center text-xs cursor-pointer">
                        🗑️
                      </button>
                    </form>
                  @else
                    <span class="text-[10px] text-slate-400 font-bold italic">Master Account</span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-12 text-slate-400 text-xs">
                No staff members matching your filter.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($staffMembers->hasPages())
      <div class="p-4 border-t border-slate-100">
        {{ $staffMembers->links() }}
      </div>
    @endif
  </div>

</div>

<!-- Create Staff Member Modal -->
<div id="createStaffModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
  <div class="card p-6 w-full max-w-md space-y-4 shadow-2xl animate-in fade-in zoom-in duration-150">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
      <h3 class="font-black text-base text-slate-900 flex items-center gap-2">
        <span>➕</span> Add New Staff Member
      </h3>
      <button type="button" onclick="document.getElementById('createStaffModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-sm">✕</button>
    </div>

    <form method="POST" action="{{ route('admin.staff.store') }}" class="space-y-4 text-xs">
      @csrf

      <div>
        <label class="block font-extrabold text-slate-700 mb-1">Full Name</label>
        <input type="text" name="name" required placeholder="e.g. Rafi Ahmed" class="inp text-xs py-2.5 px-3 w-full" />
      </div>

      <div>
        <label class="block font-extrabold text-slate-700 mb-1">Email Address (Login Username)</label>
        <input type="email" name="email" required placeholder="e.g. rafi@yourstore.com" class="inp text-xs py-2.5 px-3 w-full" />
      </div>

      <div>
        <label class="block font-extrabold text-slate-700 mb-1">Phone Number (Optional)</label>
        <input type="text" name="phone" placeholder="e.g. 01700-000000" class="inp text-xs py-2.5 px-3 w-full" />
      </div>

      <div>
        <label class="block font-extrabold text-slate-700 mb-1">Assign Access Role</label>
        <select name="role" required class="inp text-xs py-2.5 px-3 w-full font-bold cursor-pointer">
          <option value="store_manager">👔 Store Manager (Orders &amp; Catalog Management)</option>
          <option value="order_manager">📦 Order Manager (Orders &amp; Support Only)</option>
          <option value="inventory_manager">🏭 Inventory Manager (Products &amp; Stock Only)</option>
          <option value="admin">👑 Super Admin (Full Access to Everything)</option>
        </select>
      </div>

      <div>
        <label class="block font-extrabold text-slate-700 mb-1">Temporary Password</label>
        <input type="password" name="password" required placeholder="Min 6 characters" class="inp text-xs py-2.5 px-3 w-full" />
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('createStaffModal').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
        <button type="submit" class="btn-primary text-xs px-5 py-2.5">Create Staff Account</button>
      </div>
    </form>
  </div>
</div>
@endsection
