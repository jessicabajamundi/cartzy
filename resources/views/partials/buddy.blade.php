{{-- Cozy the teddy bear: floating shopping buddy for the storefront, or a helper in a workspace with
     $buddyMode (seller, admin, logistics). $buddyFloating false keeps the bear hidden until the sidebar link brings her out. --}}
@php
    $buddyMode = $buddyMode ?? null;
    $buddyFirstName = Auth::check() ? strtok((string) Auth::user()->name, ' ') : '';
    $buddyStoreName = $buddyMode === 'seller' ? (Auth::user()->seller?->name ?? $buddyFirstName) : '';
    $buddyHubName = $buddyMode === 'logistics' ? (Auth::user()->logisticsProvider?->name ?? $buddyFirstName) : '';
    $buddyWorkspace = [
        'seller' => ['status' => 'Your Cartzy shop helper', 'welcome' => "Hi {$buddyStoreName}! I'm Cozy, your shop helper. I can check your orders, stock and sales, or show you where to do things.", 'chips' => ['What needs attention?', 'Low stock items', 'Sales this month'], 'greeting' => "Hi {$buddyStoreName}! Need help with your shop?"],
        'admin' => ['status' => 'Your Cartzy admin helper', 'welcome' => "Hi Admin! I'm Cozy, your admin helper. I can check platform totals and what's waiting for review, or show you where to do things.", 'chips' => ['What needs review?', 'Open disputes', 'Platform totals'], 'greeting' => "Hi Admin! Need a hand with the marketplace?"],
        'logistics' => ['status' => 'Your Cartzy hub helper', 'welcome' => "Hello, {$buddyHubName}! I'm Cozy, your hub helper. I can check today's parcels, pickups and riders, or show you where to do things.", 'chips' => ['Parcels in the hub', 'Pickups today', 'Rider applications'], 'greeting' => "Hello, {$buddyHubName}! Ready to check today's parcels?"],
    ][$buddyMode] ?? null;
@endphp
<div id="buddy" class="buddy" @if($buddyWorkspace) data-mode="{{ $buddyMode }}" data-welcome="{{ $buddyWorkspace['welcome'] }}" data-chips='@json($buddyWorkspace['chips'])' data-greeting="{{ $buddyWorkspace['greeting'] }}" @endif data-chat-url="{{ route('buddy.chat') }}" data-user-id="{{ Auth::id() }}" data-user-name="{{ Auth::check() ? strtok((string) Auth::user()->name, ' ') : '' }}">
    <template id="buddy-bear-art">
        <svg viewBox="0 0 200 200" class="buddy-bear" aria-hidden="true" focusable="false">
            <g class="buddy-bear-body" stroke-linejoin="round">
                <circle cx="166" cy="168" r="10" fill="#EFC9BC" stroke="#A47065" stroke-width="3.500"/>
                <ellipse class="buddy-bear-arm-left" cx="68" cy="154" rx="11" ry="17" fill="#EFC9BC" stroke="#A47065" stroke-width="3.500"/>
                <ellipse class="buddy-bear-arm-right" cx="132" cy="154" rx="11" ry="17" fill="#EFC9BC" stroke="#A47065" stroke-width="3.500"/>
                <ellipse cx="100" cy="156" rx="36" ry="32" fill="#EFC9BC" stroke="#A47065" stroke-width="3.500"/>
                <ellipse cx="100" cy="162" rx="22" ry="20" fill="#FFF5F3"/>
                <circle cx="62" cy="176" r="17" fill="#EFC9BC" stroke="#A47065" stroke-width="3.500"/><ellipse cx="62" cy="180" rx="8" ry="6.500" fill="#D9A797"/><circle cx="54" cy="170" r="3.200" fill="#D9A797"/><circle cx="62" cy="167" r="3.200" fill="#D9A797"/><circle cx="70" cy="170" r="3.200" fill="#D9A797"/><circle cx="138" cy="176" r="17" fill="#EFC9BC" stroke="#A47065" stroke-width="3.500"/><ellipse cx="138" cy="180" rx="8" ry="6.500" fill="#D9A797"/><circle cx="130" cy="170" r="3.200" fill="#D9A797"/><circle cx="138" cy="167" r="3.200" fill="#D9A797"/><circle cx="146" cy="170" r="3.200" fill="#D9A797"/>
            </g>
            <g class="buddy-bear-head">
                <g class="buddy-bear-ear buddy-bear-ear-left"><circle cx="38" cy="38" r="22" fill="#EFC9BC" stroke="#A47065" stroke-width="3.500"/><circle cx="38" cy="39" r="12" fill="#E1DDE7"/></g>
                <g class="buddy-bear-ear buddy-bear-ear-right"><circle cx="162" cy="38" r="22" fill="#EFC9BC" stroke="#A47065" stroke-width="3.500"/><circle cx="162" cy="39" r="12" fill="#E1DDE7"/></g>
                <ellipse cx="100" cy="80" rx="72" ry="60" fill="#EFC9BC" stroke="#A47065" stroke-width="3.500"/>
                <ellipse cx="88" cy="42" rx="32" ry="12" fill="#F9E3DA" transform="rotate(-8 88 42)"/>
                <ellipse cx="42" cy="110" rx="13" ry="8" fill="#F2A9A0" opacity=".7"/><ellipse cx="158" cy="110" rx="13" ry="8" fill="#F2A9A0" opacity=".7"/>
                <ellipse cx="100" cy="112" rx="21" ry="16" fill="#FFF5F3"/>
                <g class="buddy-face buddy-face-open"><g class="buddy-eye"><g transform="translate(0 0)"><g class="buddy-pupil"><ellipse cx="64" cy="88" rx="12" ry="15" fill="#4A2520"/><circle cx="68" cy="82" r="5" fill="#fff"/><circle cx="60" cy="94" r="2.500" fill="#fff"/></g></g></g><g class="buddy-eye"><g transform="translate(0 0)"><g class="buddy-pupil"><ellipse cx="136" cy="88" rx="12" ry="15" fill="#4A2520"/><circle cx="140" cy="82" r="5" fill="#fff"/><circle cx="132" cy="94" r="2.500" fill="#fff"/></g></g></g></g>
                <g class="buddy-face buddy-face-joy" fill="none" stroke="#4A2520" stroke-width="5.500" stroke-linecap="round"><path d="M50 92q14-18 28 0M122 92q14-18 28 0"/></g>
                <g class="buddy-face buddy-face-love"><path d="M64 104c-13-9-19-14-19-23a10 10 0 0 1 19-4 10 10 0 0 1 19 4c0 9-6 14-19 23z" fill="#C96F7E"/><circle cx="56" cy="80" r="3" fill="#fff"/><path d="M136 104c-13-9-19-14-19-23a10 10 0 0 1 19-4 10 10 0 0 1 19 4c0 9-6 14-19 23z" fill="#C96F7E"/><circle cx="128" cy="80" r="3" fill="#fff"/></g>
                <g class="buddy-face buddy-face-sad">
                    <path d="M52 66l18-6M148 66l-18-6" fill="none" stroke="#A47065" stroke-width="4" stroke-linecap="round"/>
                    <g class="buddy-eye"><g transform="translate(0 0)"><g class="buddy-pupil"><ellipse cx="64" cy="90" rx="12" ry="15" fill="#4A2520"/><circle cx="68" cy="84" r="5" fill="#fff"/><circle cx="60" cy="96" r="2.500" fill="#fff"/></g></g></g><g class="buddy-eye"><g transform="translate(0 0)"><g class="buddy-pupil"><ellipse cx="136" cy="90" rx="12" ry="15" fill="#4A2520"/><circle cx="140" cy="84" r="5" fill="#fff"/><circle cx="132" cy="96" r="2.500" fill="#fff"/></g></g></g>
                    <path d="M150 104c-5 7-7 10-3 14s10 0 8-5c-1-3-3-6-5-9z" fill="#C7DDF0"/>
                </g>
                <g class="buddy-face buddy-face-wink"><g class="buddy-eye"><g transform="translate(0 0)"><g class="buddy-pupil"><ellipse cx="64" cy="88" rx="12" ry="15" fill="#4A2520"/><circle cx="68" cy="82" r="5" fill="#fff"/><circle cx="60" cy="94" r="2.500" fill="#fff"/></g></g></g><path d="M122 90q14-15 28 0" fill="none" stroke="#4A2520" stroke-width="5.500" stroke-linecap="round"/></g>
                <g class="buddy-face buddy-face-thinking"><g class="buddy-eye"><g transform="translate(2 -3)"><g class="buddy-pupil"><ellipse cx="64" cy="88" rx="12" ry="15" fill="#4A2520"/><circle cx="68" cy="82" r="5" fill="#fff"/><circle cx="60" cy="94" r="2.500" fill="#fff"/></g></g></g><g class="buddy-eye"><g transform="translate(2 -3)"><g class="buddy-pupil"><ellipse cx="136" cy="88" rx="12" ry="15" fill="#4A2520"/><circle cx="140" cy="82" r="5" fill="#fff"/><circle cx="132" cy="94" r="2.500" fill="#fff"/></g></g></g></g>
                <ellipse cx="100" cy="106" rx="7" ry="4.800" fill="#8C5A50"/>
                <g class="buddy-mouth" fill="none" stroke="#8C5A50" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path class="buddy-mouth-smile" d="M90 113q5 7 10 1q5 6 10-1"/>
                    <path class="buddy-mouth-sad" d="M91 121q9-8 18 0"/>
                    <ellipse class="buddy-mouth-o" cx="100" cy="118" rx="4" ry="5" fill="#C96F7E" stroke="none"/>
                    <path class="buddy-mouth-open" d="M88 112q12 18 24 0q-12 4-24 0z" fill="#C96F7E"/>
                </g>
                <g class="buddy-bear-bow" stroke="#6F6382" stroke-width="3.500" stroke-linejoin="round">
                    <path d="M136 26 114 14q-6-3-6 4v16q0 7 6 4z" fill="#A8A0B2"/>
                    <path d="M136 26l22-12q6-3 6 4v16q0 7-6 4z" fill="#A8A0B2"/>
                    <rect x="129" y="19" width="14" height="14" rx="5" fill="#6F6382"/>
                </g>
            </g>
        </svg>
    </template>

    <button type="button" class="buddy-launcher" id="buddyLauncher" @unless($buddyFloating ?? true) hidden @endunless aria-expanded="false" aria-controls="buddyPanel" aria-label="Chat with Cozy the teddy bear">
        <span class="buddy-launcher-bear" data-bear data-mood="happy"></span>
        <span class="buddy-greeting" id="buddyGreeting" hidden></span>
    </button>

    <div class="buddy-discard-target" id="buddyDiscard" hidden aria-hidden="true">
        <div class="buddy-discard-circle">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </div>
        <span class="buddy-discard-label" id="buddyDiscardLabel">Drag here to discard</span>
    </div>


    <section class="buddy-panel" id="buddyPanel" role="dialog" aria-label="Chat with Cozy" hidden>
        <header class="buddy-head">
            <span class="buddy-head-bear" data-bear data-mood="happy"></span>
            <div class="buddy-head-text">
                <strong>Cozy</strong>
                <small id="buddyStatus">{{ $buddyWorkspace['status'] ?? 'Your Cartzy shopping buddy' }}</small>
            </div>
            <button type="button" class="buddy-icon" id="buddyReset" aria-label="Start a new chat" title="Start a new chat">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.700L3 8"/><path d="M3 3v5h5"/></svg>
            </button>
            <button type="button" class="buddy-icon" id="buddyClose" aria-label="Close chat" title="Close">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.200" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </header>
        <div class="buddy-log" id="buddyLog" role="log" aria-live="polite"></div>
        <div class="buddy-chips" id="buddyChips"></div>
        <form class="buddy-compose" id="buddyForm" autocomplete="off">
            <label class="buddy-sr" for="buddyInput">Message Cozy</label>
            <textarea id="buddyInput" rows="1" maxlength="2000" placeholder="Say hi to Cozy..."></textarea>
            <button type="submit" id="buddySend" aria-label="Send message">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3.400 20.400 21 12 3.400 3.600 3.400 10l12 2-12 2z"/></svg>
            </button>
        </form>
    </section>
</div>
