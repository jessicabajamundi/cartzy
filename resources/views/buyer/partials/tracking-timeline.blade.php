                                <h3 class="commerce-timeline-title">Delivery timeline</h3><ol class="mt-6 space-y-0">
                                    @foreach($tracking['steps'] as $step)
                                        <li class="relative flex gap-4 pb-7 last:pb-0">
                                            @if(!$loop->last)<span class="absolute left-[15px] top-8 h-[calc(100%-1.5rem)] w-0.5 {{ $step['done'] && !($step['current'] ?? false) ? 'bg-[#C08B7F]' : 'bg-gray-200' }}"></span>@endif
                                            <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ ($step['error'] ?? false) ? 'bg-rose-700 text-white' : (($step['current'] ?? false) ? 'bg-[#C08B7F] text-white ring-4 ring-[#C08B7F]/25' : ($step['done'] ? 'bg-[#564B68] text-white' : 'bg-gray-200 text-gray-400')) }}">
                                                @if($step['error'] ?? false) ✕
                                                @elseif($step['done']) ✓
                                                @else <span class="tracking-pending-spinner" aria-hidden="true"></span>
                                                @endif
                                            </span>
                                            <div>
                                                <p class="text-sm font-bold {{ ($step['error'] ?? false) ? 'text-rose-700' : ($step['done'] ? 'text-gray-900' : 'text-gray-500') }}">{{ $step['label'] }} @if($step['current'] ?? false)<span class="tracking-current">{{ ($step['error'] ?? false) ? (($step['rejected'] ?? false) ? 'Rejected' : 'Stopped') : ($step['done'] ? 'Completed' : 'Pending') }}</span>@endif</p>
                                                <p class="text-xs text-gray-500">{{ $step['detail'] }}</p>
                                                <p class="mt-0.5 text-[11px] font-semibold text-[#8C5A50]">{{ $step['time'] }}</p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                                <details class="tracking-history">
                                    <summary>Detailed activity · {{ count($tracking['history']) }} recorded updates</summary>
                                    <p class="tracking-explainer">Newest first. Repeated scans and delivery attempts are shown separately. All times are Philippine time.</p>
                                    @forelse($tracking['history'] as $event)
                                        <article>
                                            <strong>{{ $event['label'] }}</strong>
                                            <p>{{ $event['time'] }}@if($event['attempt'] > 1) · Attempt {{ $event['attempt'] }}@endif</p>
                                            @if($event['note'])<p>{{ $event['note'] }}</p>@endif
                                        </article>
                                    @empty
                                        <p class="tracking-explainer">No courier scans recorded yet.</p>
                                    @endforelse
                                </details>
