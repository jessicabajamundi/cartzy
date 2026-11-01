{{-- Cozy the teddy bear: floating shopping buddy for the storefront. --}}
<div id="buddy" class="buddy" data-chat-url="{{ route('buddy.chat') }}" data-user-id="{{ Auth::id() }}" data-user-name="{{ Auth::check() ? strtok((string) Auth::user()->name, ' ') : '' }}">
    <template id="buddy-bear-art">
        <svg viewBox="0 0 200 200" class="buddy-bear" aria-hidden="true" focusable="false">
            <defs>
                <radialGradient id="buddyFur" cx="50%" cy="38%" r="68%">
                    <stop offset="0" stop-color="#E4DDF0"/><stop offset=".65" stop-color="#CBC2DC"/><stop offset="1" stop-color="#A99FBF"/>
                </radialGradient>
                <radialGradient id="buddyInner" cx="50%" cy="40%" r="70%">
                    <stop offset="0" stop-color="#FFF5F3"/><stop offset="1" stop-color="#F5CFC7"/>
                </radialGradient>
                <linearGradient id="buddyGold" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stop-color="#F0C4B8"/><stop offset=".55" stop-color="#D4A69A"/><stop offset="1" stop-color="#B76E79"/>
                </linearGradient>
            </defs>
            <g class="buddy-bear-body">
                <ellipse cx="68" cy="182" rx="20" ry="14" fill="url(#buddyFur)"/><ellipse cx="132" cy="182" rx="20" ry="14" fill="url(#buddyFur)"/>
                <ellipse cx="68" cy="184" rx="11" ry="7.5" fill="url(#buddyInner)"/><ellipse cx="132" cy="184" rx="11" ry="7.5" fill="url(#buddyInner)"/>
                <ellipse cx="100" cy="152" rx="40" ry="35" fill="url(#buddyFur)"/>
                <ellipse cx="100" cy="158" rx="24" ry="21" fill="url(#buddyInner)"/>
                <path d="M100 170c-7-5-11-8-11-12.500a5.800 5.800 0 0 1 11-2.500 5.800 5.800 0 0 1 11 2.500c0 4.500-4 7.500-11 12.500z" fill="#D4A69A" opacity=".85"/>
                <ellipse class="buddy-bear-arm-left" cx="60" cy="148" rx="13" ry="21" fill="url(#buddyFur)"/>
                <ellipse class="buddy-bear-arm-right" cx="140" cy="148" rx="13" ry="21" fill="url(#buddyFur)"/>
            </g>
            <g class="buddy-bear-head">
                <g class="buddy-bear-ear buddy-bear-ear-left"><circle cx="42" cy="36" r="25" fill="url(#buddyFur)"/><circle cx="42" cy="37" r="14.500" fill="url(#buddyInner)"/></g>
                <g class="buddy-bear-ear buddy-bear-ear-right"><circle cx="158" cy="36" r="25" fill="url(#buddyFur)"/><circle cx="158" cy="37" r="14.500" fill="url(#buddyInner)"/></g>
                <ellipse cx="100" cy="72" rx="68" ry="57" fill="url(#buddyFur)"/>
                <path d="M86 28c5-9 11-11 14-11-1 4 1 8 5 11" fill="none" stroke="#A99FBF" stroke-width="3.500" stroke-linecap="round"/>
                <ellipse cx="50" cy="92" rx="13" ry="8.500" fill="#E8B8AD" opacity=".8"/><ellipse cx="150" cy="92" rx="13" ry="8.500" fill="#E8B8AD" opacity=".8"/>
                <ellipse cx="100" cy="92" rx="24" ry="19" fill="#FFF5F3"/>
                <g class="buddy-face buddy-face-open">
                    <g class="buddy-eye"><ellipse cx="66" cy="72" rx="13" ry="15" fill="#3E354C"/><g class="buddy-pupil"><circle cx="70" cy="66" r="5" fill="#fff"/><circle cx="61" cy="78" r="2.500" fill="#fff" opacity=".85"/></g></g>
                    <g class="buddy-eye"><ellipse cx="134" cy="72" rx="13" ry="15" fill="#3E354C"/><g class="buddy-pupil"><circle cx="138" cy="66" r="5" fill="#fff"/><circle cx="129" cy="78" r="2.500" fill="#fff" opacity=".85"/></g></g>
                </g>
                <g class="buddy-face buddy-face-joy" fill="none" stroke="#3E354C" stroke-width="5" stroke-linecap="round"><path d="M53 76q13-18 26 0"/><path d="M121 76q13-18 26 0"/></g>
                <g class="buddy-face buddy-face-love" fill="#B76E79"><path d="M66 88c-12-8-18-13-18-21a9.500 9.500 0 0 1 18-4.500A9.500 9.500 0 0 1 84 67c0 8-6 13-18 21z"/><path d="M134 88c-12-8-18-13-18-21a9.500 9.500 0 0 1 18-4.500A9.500 9.500 0 0 1 152 67c0 8-6 13-18 21z"/></g>
                <g class="buddy-face buddy-face-sad"><ellipse cx="66" cy="75" rx="12" ry="13" fill="#3E354C"/><circle cx="69" cy="70" r="5" fill="#fff"/><ellipse cx="134" cy="75" rx="12" ry="13" fill="#3E354C"/><circle cx="137" cy="70" r="5" fill="#fff"/><path d="M53 56l18-7M147 56l-18-7" fill="none" stroke="#6F6382" stroke-width="4.500" stroke-linecap="round"/><path d="M145 84c-5 7-6 10-3 13s8 0 7-4c0-3-2-6-4-9z" fill="#BFD6EE" opacity=".9"/></g>
                <g class="buddy-face buddy-face-wink"><ellipse cx="66" cy="72" rx="13" ry="15" fill="#3E354C"/><circle cx="70" cy="66" r="5" fill="#fff"/><circle cx="61" cy="78" r="2.500" fill="#fff" opacity=".85"/><path d="M121 76q13-15 26 0" fill="none" stroke="#3E354C" stroke-width="5" stroke-linecap="round"/></g>
                <g class="buddy-face buddy-face-thinking"><ellipse cx="66" cy="70" rx="13" ry="15" fill="#3E354C"/><circle cx="72" cy="62" r="5" fill="#fff"/><ellipse cx="134" cy="70" rx="13" ry="15" fill="#3E354C"/><circle cx="140" cy="62" r="5" fill="#fff"/></g>
                <path d="M100 91c-6 0-10-3-10-6 0-3 4-5 10-5s10 2 10 5c0 3-4 6-10 6z" fill="#8C5A50"/>
                <ellipse cx="96" cy="83" rx="3" ry="1.600" fill="#fff" opacity=".7"/>
                <g class="buddy-mouth" fill="none" stroke="#8C5A50" stroke-width="3.500" stroke-linecap="round">
                    <path class="buddy-mouth-smile" d="M100 91v4M100 95q-8 8-14 2M100 95q8 8 14 2"/>
                    <path class="buddy-mouth-sad" d="M100 91v4M88 104q12-10 24 0"/>
                    <path class="buddy-mouth-o" d="M100 91v4"/><ellipse class="buddy-mouth-o" cx="100" cy="101" rx="4.500" ry="5" fill="#B76E79" stroke="none"/>
                    <path class="buddy-mouth-open" d="M86 96q14 18 28 0z" fill="#B76E79" stroke-linejoin="round"/>
                </g>
            </g>
            <g class="buddy-bear-bow"><g transform="translate(100 120) scale(.82) translate(-100 -120)">
                <path d="M100 120 70 105q-7-3-7 4v22q0 7 7 4z" fill="url(#buddyGold)"/>
                <path d="M100 120l30-15q7-3 7 4v22q0 7-7 4z" fill="url(#buddyGold)"/>
                <path d="M75 113l13 7M75 127l13-7M125 113l-13 7M125 127l-13-7" stroke="#B76E79" stroke-width="2" stroke-linecap="round" opacity=".7"/>
                <rect x="91" y="110" width="18" height="20" rx="7" fill="#C08B7F"/>
                <path d="M96 114q4-2 8 0" stroke="#FFF5F3" stroke-width="2" stroke-linecap="round" fill="none" opacity=".75"/>
            </g></g>
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
