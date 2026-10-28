<form method="POST" action="{{ route('logout') }}" class="logistics-header-logout">
    @csrf
    <button type="submit" title="Log out" aria-label="Log out">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        <span>Log out</span>
    </button>
</form>
