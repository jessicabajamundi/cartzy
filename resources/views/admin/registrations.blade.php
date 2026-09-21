@extends('layouts.admin')

@section('title', 'Manage Account Registrations (KYC) | cartzy')
@section('page_title', 'Manage Account Registrations')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header Summary & Guidelines -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900">Applicant KYC & Registration Verification</h1>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                Verify submitted business permits, valid government IDs, and rider licenses before activating store selling privileges and express delivery route access.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs bg-indigo-50 text-indigo-700 font-bold px-3 py-1.5 rounded-xl border border-indigo-200 flex items-center gap-1.5">
                <span>📧</span> Automated Decision Email Notifier Active
            </span>
        </div>
    </div>

    <!-- Filters Bar (Role & Status Tabs) -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-4">
        
        <!-- Role Filters -->
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs font-bold text-slate-400 mr-2 uppercase tracking-wider text-[10px]">Filter Role:</span>
            <a href="{{ route('admin.registrations', ['role' => 'all', 'status' => $statusFilter]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $roleFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All ({{ count($registrations) }})
            </a>
            <a href="{{ route('admin.registrations', ['role' => 'seller', 'status' => $statusFilter]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $roleFilter === 'seller' ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                🏬 Sellers
            </a>
            <a href="{{ route('admin.registrations', ['role' => 'courier', 'status' => $statusFilter]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $roleFilter === 'courier' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                🛵 Couriers
            </a>
            <a href="{{ route('admin.registrations', ['role' => 'buyer', 'status' => $statusFilter]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $roleFilter === 'buyer' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                👤 Buyers
            </a>
        </div>

        <!-- Status Filters -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider text-[10px]">Status:</span>
            <a href="{{ route('admin.registrations', ['role' => $roleFilter, 'status' => 'all']) }}" class="text-xs px-2.5 py-1 rounded font-semibold {{ $statusFilter === 'all' ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                All
            </a>
            <a href="{{ route('admin.registrations', ['role' => $roleFilter, 'status' => 'pending']) }}" class="text-xs px-2.5 py-1 rounded font-semibold {{ $statusFilter === 'pending' ? 'text-amber-700 bg-amber-50 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                Pending Review
            </a>
            <a href="{{ route('admin.registrations', ['role' => $roleFilter, 'status' => 'approved']) }}" class="text-xs px-2.5 py-1 rounded font-semibold {{ $statusFilter === 'approved' ? 'text-emerald-700 bg-emerald-50 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                Approved
            </a>
            <a href="{{ route('admin.registrations', ['role' => $roleFilter, 'status' => 'rejected']) }}" class="text-xs px-2.5 py-1 rounded font-semibold {{ $statusFilter === 'rejected' ? 'text-rose-700 bg-rose-50 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                Disapproved
            </a>
        </div>

    </div>

    <!-- Applicants Cards / Table List -->
    <div class="space-y-4">
        @forelse($registrations as $reg)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6 transition hover:border-slate-300">
            
            <div class="flex flex-wrap items-start justify-between gap-4">
                
                <!-- Left: Applicant Details & Role Badge -->
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl shrink-0 {{ $reg['role'] === 'seller' ? 'bg-purple-100 text-purple-700' : ($reg['role'] === 'courier' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                        {{ $reg['role'] === 'seller' ? '🏬' : ($reg['role'] === 'courier' ? '🛵' : '👤') }}
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-black text-slate-900">{{ $reg['name'] }}</h2>
                            <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full {{ $reg['role'] === 'seller' ? 'bg-purple-100 text-purple-800' : ($reg['role'] === 'courier' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                {{ $reg['role'] }} Application
                            </span>
                            @if($reg['status'] === 'pending')
                                <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">
                                    ⏳ Pending Verification
                                </span>
                            @elseif($reg['status'] === 'approved')
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                    ✓ Verified & Approved
                                </span>
                            @else
                                <span class="bg-rose-100 text-rose-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                    ✕ Disapproved
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-1.5 text-xs text-slate-600 mt-2">
                            <div><strong class="text-slate-900">Applicant:</strong> {{ $reg['applicant_name'] }}</div>
                            <div><strong class="text-slate-900">Email:</strong> {{ $reg['email'] }}</div>
                            <div><strong class="text-slate-900">Phone:</strong> {{ $reg['phone'] }}</div>
                            @if(!empty($reg['sex']))
                                <div><strong class="text-slate-900">Sex:</strong> {{ $reg['sex'] }}</div>
                            @endif
                            @if(!empty($reg['birthday']))
                                <div><strong class="text-slate-900">Birthday:</strong> {{ $reg['birthday'] }} @if(!empty($reg['age']))<span class="text-slate-400">({{ $reg['age'] }} yrs old)</span>@endif</div>
                            @endif
                            @if(!empty($reg['address']) && $reg['address'] !== 'N/A')
                                <div class="sm:col-span-2 lg:col-span-3"><strong class="text-slate-900">Residential Address:</strong> {{ $reg['address'] }}</div>
                            @endif
                            @if(isset($reg['category']))
                                <div><strong class="text-slate-900">Registered Category:</strong> <span class="text-indigo-600 font-bold">{{ $reg['category'] }}</span></div>
                            @endif
                            @if(isset($reg['vehicle']))
                                <div><strong class="text-slate-900">Registered Vehicle:</strong> <span class="text-amber-700 font-bold">{{ $reg['vehicle'] }}</span></div>
                            @endif
                        </div>

                        <!-- Documents Submitted -->
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Submitted KYC Documents & Verification Files:</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($reg['documents'] as $doc)
                                <div class="bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs px-3 py-1.5 rounded-lg flex items-center gap-1.5 font-medium transition cursor-pointer" onclick="openDocPreview('{{ $doc }}', '{{ $reg['name'] }}', '{{ $reg['id_photo'] ?? '' }}')">
                                    <span>📄</span>
                                    <span>{{ $doc }}</span>
                                    <span class="text-indigo-500 text-[10px] ml-1 font-bold">View</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        @if($reg['rejection_reason'])
                        <div class="mt-3 p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl">
                            <strong>Disapproval Reason Sent via Email:</strong> {{ $reg['rejection_reason'] }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Right: Action Buttons (Approve / Disapprove) -->
                <div class="flex sm:flex-col items-center gap-2 shrink-0 w-full sm:w-auto">
                    @if($reg['status'] === 'pending')
                        <!-- Approve Button with Email Dispatch Simulation -->
                        <form action="{{ route('admin.registrations.status', $reg['id']) }}" method="POST" class="w-full">
                            @csrf
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-md transition flex items-center justify-center gap-1.5">
                                <span>✓ Approve & Notify Email</span>
                            </button>
                        </form>

                        <!-- Disapprove Button (Opens Reject Modal) -->
                        <button type="button" onclick="openRejectModal('{{ $reg['id'] }}', '{{ $reg['name'] }}', '{{ $reg['email'] }}')" class="w-full bg-white hover:bg-rose-50 text-rose-600 border border-rose-300 font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center justify-center gap-1.5">
                            <span>✕ Disapprove Application</span>
                        </button>
                    @else
                        <!-- Re-evaluate actions -->
                        <div class="text-[11px] text-slate-400 text-right">
                            Processed: {{ $reg['updated_at'] ?? $reg['applied_at'] }}
                        </div>
                        <button type="button" onclick="openRejectModal('{{ $reg['id'] }}', '{{ $reg['name'] }}', '{{ $reg['email'] }}')" class="text-xs text-slate-500 hover:text-slate-800 underline font-medium">
                            Change Decision
                        </button>
                    @endif
                </div>

            </div>

        </div>
        @empty
        <div class="bg-white p-12 rounded-2xl border border-slate-200 text-center text-slate-500 text-sm">
            No registrations found matching the selected filter criteria.
        </div>
        @endforelse
    </div>

    <!-- Reject / Disapprove Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-black text-base text-slate-900 flex items-center gap-2">
                    <span class="text-rose-500">✕</span> Disapprove Registration Application
                </h3>
                <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <p class="text-xs text-slate-600">
                You are about to disapprove the application for <strong id="rejectApplicantName" class="text-slate-900"></strong> (<span id="rejectApplicantEmail" class="text-indigo-600"></span>). An automated email decision notification will be sent immediately.
            </p>

            <form id="rejectForm" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="status" value="rejected">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">State Reason for Disapproval / Missing Requirements:</label>
                    <textarea name="reason" rows="3" required placeholder="e.g., The submitted BIR Form 2303 is blurred or incomplete. Please upload a clear scanned copy of your Mayor's Permit 2026." class="w-full text-xs p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-md">Confirm & Dispatch Email</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Document Viewer Modal -->
    <div id="docPreviewModal" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 space-y-4 shadow-2xl border border-slate-200 text-center">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 text-left">
                <h3 class="font-black text-sm text-slate-900" id="previewDocTitle">KYC Document Inspection</h3>
                <button type="button" onclick="closeDocPreview()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            
            <div class="p-6 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-300 flex flex-col items-center justify-center space-y-3">
                <div id="previewDocImageContainer" class="hidden w-full max-h-80 overflow-auto rounded-xl">
                    <img id="previewDocImage" src="" alt="Uploaded Document" class="max-h-72 mx-auto rounded-lg shadow-sm border border-slate-200">
                </div>
                <div id="previewDocPlaceholder" class="flex flex-col items-center">
                    <div class="text-5xl mb-2">📄</div>
                    <div class="font-bold text-sm text-slate-900" id="previewDocName"></div>
                </div>
                <div class="text-xs text-slate-500" id="previewDocOwner"></div>
                <div class="text-[11px] bg-emerald-100 text-emerald-800 font-bold px-3 py-1 rounded-full">
                    ✓ High Resolution Digital Verification Match
                </div>
            </div>

            <div class="text-right">
                <button type="button" onclick="closeDocPreview()" class="px-4 py-2 text-xs font-bold bg-slate-900 text-white rounded-xl">Close Preview</button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function openRejectModal(id, name, email) {
        document.getElementById('rejectApplicantName').innerText = name;
        document.getElementById('rejectApplicantEmail').innerText = email;
        document.getElementById('rejectForm').action = "/admin/registrations/" + id + "/status";
        document.getElementById('rejectModal').classList.remove('hidden');
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    function openDocPreview(docName, ownerName, photoUrl) {
        document.getElementById('previewDocName').innerText = docName;
        document.getElementById('previewDocOwner').innerText = 'Applicant: ' + ownerName;
        
        const imgContainer = document.getElementById('previewDocImageContainer');
        const img = document.getElementById('previewDocImage');
        const placeholder = document.getElementById('previewDocPlaceholder');
        
        if (photoUrl && (photoUrl.endsWith('.jpg') || photoUrl.endsWith('.jpeg') || photoUrl.endsWith('.png'))) {
            img.src = photoUrl;
            imgContainer.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            img.src = '';
            imgContainer.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }

        document.getElementById('docPreviewModal').classList.remove('hidden');
    }
    function closeDocPreview() {
        document.getElementById('docPreviewModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
