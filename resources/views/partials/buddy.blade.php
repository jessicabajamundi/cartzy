{{-- Cozy the teddy bear: floating shopping buddy for the storefront. --}}
<div id="buddy" class="buddy" data-chat-url="{{ route('buddy.chat') }}" data-user-id="{{ Auth::id() }}" data-user-name="{{ Auth::check() ? strtok((string) Auth::user()->name, ' ') : '' }}">
    <template id="buddy-bear-art">
        <svg viewBox="0 0 200 200" class="buddy-bear" aria-hidden="true" focusable="false">
            <g class="buddy-bear-body" stroke="#4A3D5C" stroke-width="5" stroke-linejoin="round">
                <ellipse cx="72" cy="183" rx="18" ry="12.500" fill="#CFC6E6"/><ellipse cx="128" cy="183" rx="18" ry="12.500" fill="#CFC6E6"/>
                <ellipse cx="72" cy="185" rx="9" ry="6" fill="#F5CFC7" stroke="none"/><ellipse cx="128" cy="185" rx="9" ry="6" fill="#F5CFC7" stroke="none"/>
                <ellipse class="buddy-bear-arm-left" cx="65" cy="152" rx="12" ry="18" fill="#CFC6E6"/>
                <ellipse class="buddy-bear-arm-right" cx="135" cy="152" rx="12" ry="18" fill="#CFC6E6"/>
                <ellipse cx="100" cy="158" rx="35" ry="29" fill="#CFC6E6"/>
                <ellipse cx="100" cy="165" rx="21" ry="17" fill="#FFF5F3" stroke="none"/>
                <path d="M100 176c-8-5.500-12-9-12-13.500a6.200 6.200 0 0 1 12-2.500 6.200 6.200 0 0 1 12 2.500c0 4.500-4 8-12 13.500z" fill="#D4A69A" stroke="none"/>
            </g>
            <g class="buddy-bear-head">
                <g stroke="#4A3D5C" stroke-width="5">
                    <g class="buddy-bear-ear buddy-bear-ear-left"><circle cx="38" cy="38" r="24" fill="#CFC6E6"/><circle cx="38" cy="39" r="12.500" fill="#F5CFC7" stroke="none"/></g>
                    <g class="buddy-bear-ear buddy-bear-ear-right"><circle cx="162" cy="38" r="24" fill="#CFC6E6"/><circle cx="162" cy="39" r="12.500" fill="#F5CFC7" stroke="none"/></g>
                    <ellipse cx="100" cy="78" rx="74" ry="62" fill="#D6CEEA"/>
                </g>
                <ellipse cx="86" cy="36" rx="34" ry="13" fill="#E9E3F5" transform="rotate(-8 86 36)"/>
                <path d="M92 18q2-9 9-11M101 17q3-7 10-7" fill="none" stroke="#4A3D5C" stroke-width="4.500" stroke-linecap="round"/>
                <ellipse cx="38" cy="106" rx="14" ry="9" fill="#F2B3B0"/><ellipse cx="162" cy="106" rx="14" ry="9" fill="#F2B3B0"/>
                <ellipse cx="100" cy="108" rx="25" ry="19" fill="#FFF5F3"/>
                <g class="buddy-face buddy-face-open">
                    <g class="buddy-eye"><ellipse cx="62" cy="84" rx="16" ry="19" fill="#2F2740"/><ellipse cx="62" cy="93" rx="10" ry="7" fill="#6F6382"/><g class="buddy-pupil"><circle cx="68" cy="76" r="6.500" fill="#fff"/><circle cx="56" cy="90" r="3" fill="#fff"/></g></g>
                    <g class="buddy-eye"><ellipse cx="138" cy="84" rx="16" ry="19" fill="#2F2740"/><ellipse cx="138" cy="93" rx="10" ry="7" fill="#6F6382"/><g class="buddy-pupil"><circle cx="144" cy="76" r="6.500" fill="#fff"/><circle cx="132" cy="90" r="3" fill="#fff"/></g></g>
                </g>
                <g class="buddy-face buddy-face-joy">
                    <path d="M45 90q17-24 34 0M121 90q17-24 34 0" fill="none" stroke="#2F2740" stroke-width="6.500" stroke-linecap="round"/>
                    <path d="M26 66l2.500 6 6 2.500-6 2.500-2.500 6-2.500-6-6-2.500 6-2.500zM174 66l2.500 6 6 2.500-6 2.500-2.500 6-2.500-6-6-2.500 6-2.500z" fill="#D4A69A"/>
                </g>
                <g class="buddy-face buddy-face-love" fill="#B76E79" stroke="#4A3D5C" stroke-width="4" stroke-linejoin="round">
                    <path d="M62 104c-15-10-22-16-22-26a11.500 11.500 0 0 1 22-5 11.500 11.500 0 0 1 22 5c0 10-7 16-22 26z"/>
                    <path d="M138 104c-15-10-22-16-22-26a11.500 11.500 0 0 1 22-5 11.500 11.500 0 0 1 22 5c0 10-7 16-22 26z"/>
                    <circle cx="54" cy="76" r="3.500" fill="#fff" stroke="none"/><circle cx="130" cy="76" r="3.500" fill="#fff" stroke="none"/>
                </g>
                <g class="buddy-face buddy-face-sad">
                    <ellipse cx="62" cy="87" rx="15" ry="17" fill="#2F2740"/><ellipse cx="62" cy="96" rx="10" ry="6" fill="#6F6382"/><circle cx="67" cy="80" r="6.500" fill="#fff"/><circle cx="56" cy="92" r="3" fill="#fff"/>
                    <ellipse cx="138" cy="87" rx="15" ry="17" fill="#2F2740"/><ellipse cx="138" cy="96" rx="10" ry="6" fill="#6F6382"/><circle cx="143" cy="80" r="6.500" fill="#fff"/><circle cx="132" cy="92" r="3" fill="#fff"/>
                    <path d="M46 63l20-8M154 63l-20-8" fill="none" stroke="#4A3D5C" stroke-width="5" stroke-linecap="round"/>
                    <path d="M154 100c-6 8-8 12-4 16s11 0 9-5c-1-4-3-8-5-11z" fill="#BFD6EE" stroke="#4A3D5C" stroke-width="3" stroke-linejoin="round"/>
                </g>
                <g class="buddy-face buddy-face-wink">
                    <ellipse cx="62" cy="84" rx="16" ry="19" fill="#2F2740"/><ellipse cx="62" cy="93" rx="10" ry="7" fill="#6F6382"/><circle cx="68" cy="76" r="6.500" fill="#fff"/><circle cx="56" cy="90" r="3" fill="#fff"/>
                    <path d="M121 88q17-20 34 0" fill="none" stroke="#2F2740" stroke-width="6.500" stroke-linecap="round"/>
                </g>
                <g class="buddy-face buddy-face-thinking">
                    <ellipse cx="62" cy="82" rx="16" ry="19" fill="#2F2740"/><ellipse cx="62" cy="91" rx="10" ry="7" fill="#6F6382"/><circle cx="70" cy="72" r="6.500" fill="#fff"/><circle cx="55" cy="88" r="3" fill="#fff"/>
                    <ellipse cx="138" cy="82" rx="16" ry="19" fill="#2F2740"/><ellipse cx="138" cy="91" rx="10" ry="7" fill="#6F6382"/><circle cx="146" cy="72" r="6.500" fill="#fff"/><circle cx="131" cy="88" r="3" fill="#fff"/>
                </g>
                <path d="M100 104c-6-2-10-5-10-8 0-3 4-5 10-5s10 2 10 5c0 3-4 6-10 8z" fill="#8C5A50"/>
                <ellipse cx="96" cy="94.500" rx="3.200" ry="1.700" fill="#fff"/>
                <g class="buddy-mouth" fill="none" stroke="#4A3D5C" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                    <path class="buddy-mouth-smile" d="M100 104v3M87 108q6.500 8 13-1q6.500 9 13 1"/>
                    <path class="buddy-mouth-sad" d="M100 104v4M89 118q11-11 22 0"/>
                    <path class="buddy-mouth-o" d="M100 104v3"/><ellipse class="buddy-mouth-o" cx="100" cy="113" rx="5" ry="6" fill="#B76E79"/>
                    <path class="buddy-mouth-open" d="M84 106q16 24 32 0q-16 5-32 0z" fill="#B76E79"/>
                </g>
                <g class="buddy-bear-bow" stroke="#4A3D5C" stroke-width="4.500" stroke-linejoin="round">
                    <path d="M136 22 112 9q-6-3-6 4v20q0 7 6 4z" fill="#D4A69A"/>
                    <path d="M136 22l24-13q6-3 6 4v20q0 7-6 4z" fill="#D4A69A"/>
                    <path d="M115 17l11 5M115 29l11-5M157 17l-11 5M157 29l-11-5" fill="none" stroke="#B76E79" stroke-width="3" stroke-linecap="round"/>
                    <rect x="128" y="14" width="16" height="17" rx="6" fill="#B76E79"/>
                </g>
            </g>
        </svg>
    </template>

    <button type="button" class="buddy-launcher" id="buddyLauncher" aria-expanded="false" aria-controls="buddyPanel" aria-label="Chat with Cozy the teddy bear">
        <span class="buddy-launcher-bear" data-bear data-mood="happy"></span>
        <span class="buddy-greeting" id="buddyGreeting" hidden></span>
    </button>

    <section class="buddy-panel" id="buddyPanel" role="dialog" aria-label="Chat with Cozy" hidden>
        <header class="buddy-head">
            <span class="buddy-head-bear" data-bear data-mood="happy"></span>
            <div class="buddy-head-text">
                <strong>Cozy</strong>
                <small id="buddyStatus">Your Cartzy shopping buddy</small>
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
