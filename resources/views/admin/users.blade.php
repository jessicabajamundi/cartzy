@extends('layouts.admin')

@section('title', 'Manage User Accounts | cartzy')
@section('page_title', 'Manage User Accounts')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Top Action Bar -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900">User Account Administration</h1>
            <p class="text-xs text-slate-500 mt-1">
                View user profiles across all platform roles (Buyers, Merchants, Couriers) and control account status (Activate, Suspend, Deactivate).
            </p>
        </div>
        
        <!-- Search input -->
        <form method="GET" action="{{ route('admin.users') }}" class="flex items-center gap-2">
            <input type="hidden" name="role" value="{{ $roleFilter }}">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email, phone..." class="w-64 text-xs px-3.5 py-2 pl-9 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
            </div>
            <button type="submit" class="bg-slate-900 hover:bg-black text-white text-xs font-bold px-3 py-2 rounded-xl">Search</button>
        </form>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-4">
        
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mr-2">Role:</span>
            <a href="{{ route('admin.users', ['role' => 'all', 'status' => $statusFilter, 'search' => $search]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $roleFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All ({{ count($users) }})
            </a>
            <a href="{{ route('admin.users', ['role' => 'buyer', 'status' => $statusFilter, 'search' => $search]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $roleFilter === 'buyer' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                🛍️ Buyers
            </a>
            <a href="{{ route('admin.users', ['role' => 'seller', 'status' => $statusFilter, 'search' => $search]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $roleFilter === 'seller' ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                🏬 Sellers
            </a>
            <a href="{{ route('admin.users', ['role' => 'courier', 'status' => $statusFilter, 'search' => $search]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $roleFilter === 'courier' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                🛵 Couriers
            </a>
            <a href="{{ route('admin.users', ['role' => 'admin', 'status' => $statusFilter, 'search' => $search]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $roleFilter === 'admin' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                🛡️ Admins
            </a>
        </div>

        <div class="flex items-center gap-2 text-xs">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Status:</span>
            <a href="{{ route('admin.users', ['role' => $roleFilter, 'status' => 'all', 'search' => $search]) }}" class="px-2 py-1 rounded font-semibold {{ $statusFilter === 'all' ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-slate-500' }}">All</a>
            <a href="{{ route('admin.users', ['role' => $roleFilter, 'status' => 'active', 'search' => $search]) }}" class="px-2 py-1 rounded font-semibold {{ $statusFilter === 'active' ? 'text-emerald-700 bg-emerald-50 font-bold' : 'text-slate-500' }}">Active</a>
            <a href="{{ route('admin.users', ['role' => $roleFilter, 'status' => 'suspended', 'search' => $search]) }}" class="px-2 py-1 rounded font-semibold {{ $statusFilter === 'suspended' ? 'text-amber-700 bg-amber-50 font-bold' : 'text-slate-500' }}">Suspended</a>
            <a href="{{ route('admin.users', ['role' => $roleFilter, 'status' => 'deactivated', 'search' => $search]) }}" class="px-2 py-1 rounded font-semibold {{ $statusFilter === 'deactivated' ? 'text-rose-700 bg-rose-50 font-bold' : 'text-slate-500' }}">Deactivated</a>
        </div>

    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">User Details</th>
                        <th class="py-3.5 px-4">Role</th>
                        <th class="py-3.5 px-4">Account Status</th>
                        <th class="py-3.5 px-4 text-center">Orders / Activity</th>
                        <th class="py-3.5 px-4 text-center">Violations</th>
                        <th class="py-3.5 px-4">Joined Date</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/70 transition">
                        
                        <!-- Name & Contact -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs {{ $user['role'] === 'seller' ? 'bg-purple-100 text-purple-700' : ($user['role'] === 'courier' ? 'bg-amber-100 text-amber-700' : ($user['role'] === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700')) }}">
                                    {{ strtoupper(substr($user['name'], 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $user['name'] }}</div>
                                    <div class="text-slate-400 text-[11px]">{{ $user['email'] }} &bull; {{ $user['phone'] }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Role Badge -->
                        <td class="py-3.5 px-4">
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ $user['role'] === 'seller' ? 'bg-purple-50 text-purple-700 border border-purple-200' : ($user['role'] === 'courier' ? 'bg-amber-50 text-amber-700 border border-amber-200' : ($user['role'] === 'admin' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-blue-50 text-blue-700 border border-blue-200')) }}">
                                {{ $user['role'] }}
                            </span>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3.5 px-4">
                            @if($user['status'] === 'active')
                                <span class="bg-emerald-50 text-emerald-700 font-bold px-2.5 py-1 rounded-full text-[10px] border border-emerald-200 flex items-center gap-1 w-max">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @elseif($user['status'] === 'suspended')
                                <span class="bg-amber-50 text-amber-700 font-bold px-2.5 py-1 rounded-full text-[10px] border border-amber-200 flex items-center gap-1 w-max">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Suspended
                                </span>
                            @else
                                <span class="bg-rose-50 text-rose-700 font-bold px-2.5 py-1 rounded-full text-[10px] border border-rose-200 flex items-center gap-1 w-max">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Deactivated
                                </span>
                            @endif
                        </td>

                        <!-- Activity -->
                        <td class="py-3.5 px-4 text-center font-semibold text-slate-700">
                            {{ $user['orders_count'] }} transactions
                        </td>

                        <!-- Violations -->
                        <td class="py-3.5 px-4 text-center">
                            @if($user['violations'] > 0)
                                <span class="bg-rose-100 text-rose-700 font-black px-2 py-0.5 rounded text-[11px]">
                                    {{ $user['violations'] }} Strikes
                                </span>
                            @else
                                <span class="text-slate-400 text-[11px]">0</span>
                            @endif
                        </td>

                        <!-- Joined -->
                        <td class="py-3.5 px-4 text-slate-500">
                            {{ $user['joined'] }}
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" onclick="openProfileModal({{ json_encode($user) }})" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition" title="View Profile">
                                    👁️
                                </button>
                                <button type="button" onclick="openStatusModal('{{ $user['id'] }}', '{{ $user['name'] }}', '{{ $user['status'] }}')" class="px-2.5 py-1 bg-slate-900 hover:bg-black text-white font-bold text-[11px] rounded-lg transition">
                                    Manage Status
                                </button>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Change Modal (Activate / Suspend / Deactivate) -->
    <div id="statusModal" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-black text-base text-slate-900">Manage Account Access</h3>
                <button type="button" onclick="closeStatusModal()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <p class="text-xs text-slate-600">
                Update account status for <strong id="statusUserName" class="text-slate-900"></strong>:
            </p>

            <form id="statusForm" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Status Action:</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="p-3 border rounded-xl flex flex-col items-center gap-1 cursor-pointer hover:border-emerald-500 has-checked:border-emerald-600 has-checked:bg-emerald-50">
                            <input type="radio" name="status" value="active" class="hidden">
                            <span class="text-base">✅</span>
                            <span class="text-[11px] font-bold text-emerald-700">Activate</span>
                        </label>
                        <label class="p-3 border rounded-xl flex flex-col items-center gap-1 cursor-pointer hover:border-amber-500 has-checked:border-amber-600 has-checked:bg-amber-50">
                            <input type="radio" name="status" value="suspended" class="hidden">
                            <span class="text-base">⚠️</span>
                            <span class="text-[11px] font-bold text-amber-700">Suspend</span>
                        </label>
                        <label class="p-3 border rounded-xl flex flex-col items-center gap-1 cursor-pointer hover:border-rose-500 has-checked:border-rose-600 has-checked:bg-rose-50">
                            <input type="radio" name="status" value="deactivated" class="hidden">
                            <span class="text-base">🛑</span>
                            <span class="text-[11px] font-bold text-rose-700">Deactivate</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Reason / Internal Admin Note:</label>
                    <textarea name="reason" rows="2" placeholder="e.g. Account suspended for policy violation or user requested reactivation." class="w-full text-xs p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold bg-slate-900 hover:bg-black text-white rounded-xl shadow-md">Apply Status Change</button>
                </div>
            </form>
        </div>
    </div>

    <!-- User Profile Viewer Modal -->
    <div id="profileModal" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-black text-base text-slate-900">User Profile Overview</h3>
                <button type="button" onclick="closeProfileModal()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-xl" id="modalProfileAvatar">
                    U
                </div>
                <div>
                    <h4 class="font-extrabold text-base text-slate-900" id="modalProfileName"></h4>
                    <div class="text-xs text-slate-500" id="modalProfileEmail"></div>
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-slate-200 text-slate-800 mt-1 inline-block" id="modalProfileRole"></span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Account Status</span>
                    <span class="font-bold text-slate-900 mt-0.5 block" id="modalProfileStatus"></span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Contact Phone</span>
                    <span class="font-bold text-slate-900 mt-0.5 block" id="modalProfilePhone"></span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Total Orders / Trips</span>
                    <span class="font-bold text-slate-900 mt-0.5 block" id="modalProfileOrders"></span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Compliance Violations</span>
                    <span class="font-bold text-rose-600 mt-0.5 block" id="modalProfileViolations"></span>
                </div>
            </div>

            <div class="text-right pt-2">
                <button type="button" onclick="closeProfileModal()" class="px-4 py-2 text-xs font-bold bg-slate-900 text-white rounded-xl">Close</button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/admin/users.js') }}"></script>
@endpush
@endsection
