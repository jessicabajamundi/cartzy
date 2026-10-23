@extends('layouts.courier')

@section('title', 'Profit & Earnings | cartzy')
@section('header_title', 'Profit & Earnings Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Earnings Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-2xl sm:text-3xl font-bold text-[#564B68]">Rider Earnings &amp; Commissions</h1>
            <p class="text-xs text-gray-500">Track delivery payouts, COD cash remittances, and performance metrics</p>
        </div>

        <button type="button" onclick="document.getElementById('remitModal').classList.remove('hidden')" class="btn-gradient-master text-xs px-4 py-2.5 flex items-center gap-2 cursor-pointer shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span>Log COD Remittance</span>
        </button>
    </div>

    <!-- 4 Profit Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Today's Profit -->
        <div class="rider-card p-5 relative overflow-hidden">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Today's Payout</span>
                <span class="w-8 h-8 rounded-xl bg-[#FAF8F6] text-[#8C5A50] flex items-center justify-center font-bold text-xs">₱</span>
            </div>
            <div class="font-heading text-3xl font-bold text-[#8C5A50] mt-2">₱{{ number_format($totalEarningsToday, 2) }}</div>
            <p class="text-[11px] text-gray-500 font-semibold mt-1">{{ $deliveredTodayCount }} completed deliveries</p>
        </div>

        <!-- 2. This Week -->
        <div class="rider-card p-5">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">This Week (7 Days)</span>
                <span class="w-8 h-8 rounded-xl bg-[#F8F6FA] text-[#6F6382] flex items-center justify-center font-bold text-xs">7D</span>
            </div>
            <div class="font-heading text-3xl font-bold text-[#564B68] mt-2">₱{{ number_format($totalEarningsWeek, 2) }}</div>
            <p class="text-[11px] text-gray-500 font-semibold mt-1">{{ $deliveredWeekCount }} deliveries</p>
        </div>

        <!-- 3. This Month -->
        <div class="rider-card p-5">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">This Month</span>
                <span class="w-8 h-8 rounded-xl bg-[#FAF8F6] text-[#C08B7F] flex items-center justify-center font-bold text-xs">MO</span>
            </div>
            <div class="font-heading text-3xl font-bold text-[#C08B7F] mt-2">₱{{ number_format($totalEarningsMonth, 2) }}</div>
            <p class="text-[11px] text-gray-500 font-semibold mt-1">{{ $deliveredMonthCount }} deliveries</p>
        </div>

        <!-- 4. Total COD Cash on Hand -->
        <div class="rider-card p-5 bg-[#FAF8F6] border-amber-200">
            <div class="flex items-center justify-between text-amber-700">
                <span class="text-[11px] font-bold uppercase tracking-wider">COD Cash On Hand</span>
                <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-900 flex items-center justify-center font-bold text-xs">COD</span>
            </div>
            <div class="font-heading text-3xl font-bold text-amber-900 mt-2">₱{{ number_format($codCollectedToday, 2) }}</div>
            <p class="text-[11px] text-amber-700 font-semibold mt-1">Pending hub remittance</p>
        </div>
    </div>

    <!-- Analytics Chart & Rate Info Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 7-Day Performance Chart -->
        <div class="rider-card p-5 lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-heading text-lg font-bold text-[#564B68]">7-Day Delivery &amp; Profit Trend</h3>
                    <p class="text-[11px] text-gray-500">Completed packages per day and earned commissions</p>
                </div>
                <span class="text-xs font-bold text-gray-500 bg-gray-50 px-2.5 py-1 rounded-lg border border-gray-200">Standard ₱65/drop</span>
            </div>
            <div class="h-64 relative">
                <canvas id="earningsChart"></canvas>
            </div>
        </div>

        <!-- Rider Fee Structure & COD Settlement Policy -->
        <div class="rider-card p-5 flex flex-col justify-between space-y-4" style="background: var(--gradient-soft);">
            <div>
                <h3 class="font-heading text-lg font-bold text-[#564B68]">Commission Structure</h3>
                <p class="text-[11px] text-gray-500">Transparent compensation model</p>

                <div class="mt-4 space-y-3 text-xs">
                    <div class="p-3 bg-white rounded-xl border border-[#ECE7EF]">
                        <span class="text-gray-500 block text-[10px] uppercase font-bold">Standard Parcel Drop</span>
                        <strong class="text-base text-[#564B68]">₱65.00</strong> <span class="text-gray-500">per completed drop</span>
                    </div>

                    <div class="p-3 bg-white rounded-xl border border-[#ECE7EF]">
                        <span class="text-gray-500 block text-[10px] uppercase font-bold">COD Incentive</span>
                        <strong class="text-base text-[#8C5A50]">+100%</strong> <span class="text-gray-500">remitted intact to hub</span>
                    </div>

                    <div class="p-3 bg-white rounded-xl border border-[#ECE7EF]">
                        <span class="text-gray-500 block text-[10px] uppercase font-bold">Logistics Hub Partner</span>
                        <strong class="text-xs text-gray-800">{{ $rider->logisticsProvider?->name ?? 'Metro South Sorting Hub' }}</strong>
                    </div>
                </div>
            </div>

            <p class="text-[11px] text-gray-400 italic">
                * Payouts are credited directly to your registered courier account or remitted via hub cashier weekly.
            </p>
        </div>
    </div>

    <!-- Recent Delivery Payouts Table -->
    <div class="rider-card overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-[#ECE7EF] flex items-center justify-between bg-[#FAF8F6]">
            <div>
                <h3 class="font-heading text-lg font-bold text-[#564B68]">Recent Payout Transactions</h3>
                <p class="text-[11px] text-gray-500">Itemized commission per delivered parcel</p>
            </div>
            <span class="text-xs font-bold text-[#6F6382]">{{ $recentPayouts->count() }} Records</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-3.5 pl-5">Tracking Code</th>
                        <th class="p-3.5">Recipient / Area</th>
                        <th class="p-3.5">Payment</th>
                        <th class="p-3.5">Delivered Time</th>
                        <th class="p-3.5 pr-5 text-right">Rider Commission</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($recentPayouts as $p)
                        <tr class="hover:bg-[#FAF9FB] transition">
                            <td class="p-3.5 pl-5 font-mono font-bold text-gray-900">{{ $p->tracking_code }}</td>
                            <td class="p-3.5">
                                <span class="font-bold text-gray-800">{{ $p->sellerOrder->order->shipping_address['recipient'] ?? 'Customer' }}</span>
                                <span class="text-gray-400 block text-[11px]">{{ $p->delivery_area ?? 'Metro South' }}</span>
                            </td>
                            <td class="p-3.5">
                                @if($p->cod_collected)
                                    <span class="bg-amber-100 text-amber-900 font-bold text-[10px] px-2 py-0.5 rounded">COD Collected</span>
                                @else
                                    <span class="bg-emerald-100 text-emerald-800 font-bold text-[10px] px-2 py-0.5 rounded">Prepaid</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-gray-500 text-[11px]">
                                {{ $p->updated_at?->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                            </td>
                            <td class="p-3.5 pr-5 text-right font-bold text-emerald-700 text-sm">
                                +₱{{ number_format(\App\Http\Controllers\Courier\DashboardController::RIDER_FEE_PER_DELIVERY, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400">No completed payouts recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- COD REMITTANCE MODAL -->
<div id="remitModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-gray-100">
        <div class="text-white p-5 flex items-center justify-between" style="background: var(--gradient-master);">
            <div>
                <h3 class="font-bold text-base">Log COD Remittance</h3>
                <p class="text-[11px] text-white/80">Submit cash collected to Hub Cashier</p>
            </div>
            <button type="button" onclick="document.getElementById('remitModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white">✕</button>
        </div>

        <form action="{{ route('courier.earnings.remit') }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Remittance Amount (PHP):</label>
                <input type="number" step="0.01" name="amount" value="{{ $codCollectedToday > 0 ? $codCollectedToday : '1000.00' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-base font-bold text-gray-900 focus:ring-2 focus:ring-[#564B68] focus:outline-hidden" required>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Cashier / Hub Note (Optional):</label>
                <input type="text" name="notes" placeholder="e.g. End of shift remittance with Cashier Jenny" class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#564B68] focus:outline-hidden">
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-[11px] leading-relaxed">
                Please hand in the physical cash to the logistics hub cashier upon arrival. The cashier will cross-verify with this digital submission.
            </div>

            <div class="flex gap-2 pt-2">
                <button type="button" onclick="document.getElementById('remitModal').classList.add('hidden')" class="w-1/3 py-2.5 rounded-xl font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="w-2/3 py-2.5 btn-gradient-master font-bold cursor-pointer">Submit Remittance</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('earningsChart');
        if (!ctx) return;

        const labels = @json($chartLabels);
        const dataEarnings = @json($chartEarnings);
        const dataDeliveries = @json($chartDeliveries);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Profit Earned (₱)',
                        data: dataEarnings,
                        backgroundColor: '#6F6382',
                        borderRadius: 8,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Deliveries Completed',
                        data: dataDeliveries,
                        backgroundColor: '#C08B7F',
                        borderRadius: 8,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: "'Lato', sans-serif", weight: 'bold', size: 11 }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return '₱' + value; },
                            font: { size: 10 }
                        },
                        grid: { color: '#F3F4F6' }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        ticks: {
                            precision: 0,
                            font: { size: 10 }
                        },
                        grid: { drawOnChartArea: false }
                    },
                    x: {
                        ticks: { font: { size: 10 } },
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endpush
