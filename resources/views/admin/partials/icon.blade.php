@php
$paths = [
    'grid' => 'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',
    'file' => 'M14 2H5v20h14V7z M14 2v6h5 M8 12h8 M8 16h6',
    'users' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M16 3a4 4 0 0 1 0 8 M22 21v-2a4 4 0 0 0-3-3.87 M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0',
    'user' => 'M20 21v-2a7 7 0 0 0-14 0v2 M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0',
    'box' => 'm12 3 9 5v9l-9 5-9-5V8z M3 8l9 5 9-5 M12 13v9 M7 5l10 6',
    'shield' => 'm12 3 9 4v5c0 5-9 10-9 10S3 17 3 12V7z m-4 9 3 3 5-6',
    'wallet' => 'M3 6h18v15H3z M3 6V3h15v3 M16 12h5v5h-5z',
    'chart' => 'M3 3v18h18 M7 16v-4 M12 16V8 M17 16V5',
    'message' => 'M21 3H3v14h5v5l6-5h7z M7 8h10 M7 12h6',
    'settings' => 'M4 7h16 M4 17h16 M9 4v6 M15 14v6',
    'arrow' => 'M5 12h14 m-6-6 6 6-6 6',
    'logout' => 'M9 3H3v18h6 M9 12h12 m-5-5 5 5-5 5',
    'menu' => 'M3 6h18 M3 12h18 M3 18h18',
];
@endphp
<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['file'] }}"/></svg>
