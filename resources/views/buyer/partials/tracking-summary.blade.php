                                <div class="commerce-tracking-summary">
                                    <div><p class="text-[11px] font-bold uppercase tracking-wider text-white/60">Order</p><p class="font-extrabold">{{ $tracking['order'] }}</p></div>
                                    <div><p class="text-[11px] font-bold uppercase tracking-wider text-white/60">Tracking no.</p><p class="font-extrabold">{{ $tracking['tracking_no'] }}</p></div>
                                    @if($tracking['eta'] !== 'Not confirmed by courier')
                                        <div><p class="text-[11px] font-bold uppercase tracking-wider text-white/60">Estimated arrival</p><p class="font-extrabold text-[#E8C9C0]">{{ $tracking['eta'] }}</p></div>
                                    @endif
                                </div>
                                <p class="mt-4 text-sm text-gray-500">Logistics provider: <span class="font-bold text-gray-800">{{ $tracking['courier'] }}</span></p>
                                <div class="tracking-status-panel">
                                    <div><span class="tracking-caption">Latest confirmed stage</span><strong>{{ $tracking['current_status'] }}</strong></div>
                                    <div><span class="tracking-caption">Last recorded update · Philippine time</span><strong>{{ $tracking['last_update'] }}</strong></div>
                                </div>
                                <dl class="tracking-details">
                                    <div><dt>Pickup rider</dt><dd>{{ $tracking['pickup_rider'] ?? 'Not assigned yet' }}</dd></div>
                                    <div><dt>Hub-to-Hub courier</dt><dd>{{ $tracking['hub_to_hub_courier'] ?? 'Not assigned yet' }}</dd></div>
                                    <div><dt>Delivery rider</dt><dd>{{ $tracking['rider'] }}</dd></div>
                                    <div><dt>Delivery area</dt><dd>{{ $tracking['area'] }}</dd></div>
                                    <div><dt>Delivery attempts</dt><dd>{{ $tracking['attempts'] ?: 'No attempt recorded' }}</dd></div>
                                </dl>
                                @if($tracking['attention'])
                                    <p class="tracking-attention" role="status">{{ $tracking['attention'] }}</p>
                                @endif
                                @if(!empty($tracking['refund_status']))
                                    <p class="tracking-attention">{{ $tracking['refund_status'] }}</p>
                                @endif

