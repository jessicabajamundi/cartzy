@once
<style>
.commerce-page { max-width:1180px; margin-inline:auto; color:#3E354C; }
.commerce-heading { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; padding:8px 0 26px; margin-bottom:0; }
.commerce-eyebrow { margin:0 0 10px; color:#95695F; font:800 10px/1.5 'Lato',sans-serif; letter-spacing:.16em; text-transform:uppercase; }
.commerce-heading h1,.commerce-heading h2 { font:800 clamp(27px,3vw,34px)/1.2 'Lato',sans-serif; letter-spacing:-.9px; color:#3E354C; margin:0; }
.commerce-heading p:not(.commerce-eyebrow) { font-size:14px; line-height:1.6; color:#81758B; margin:8px 0 0; }
.commerce-count { display:inline-flex; align-items:center; gap:7px; padding:8px 13px; border:1px solid #E6D9D4; border-radius:30px; background:#FAF0EC; color:#8C5A50; font-size:12px; font-weight:700; }
.commerce-empty { position:relative; overflow:hidden; border:1px solid #E6DFEB; border-radius:24px; background:linear-gradient(125deg,#FAF7FC 0%,#FFFFFF 42%,#FFFFFF 66%,#FCF4F0 100%); min-height:420px; padding:40px 24px 0; text-align:center; box-shadow:0 8px 28px -24px #564B6860; }
.commerce-empty::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#6F6382,#A8A0B2 50%,#C08B7F); }
.commerce-empty-art { position:relative; width:130px; height:118px; margin:0 auto 22px; display:grid; place-items:center; }
.commerce-orbit { position:absolute; width:116px; height:104px; border:1px solid #E6D5CF; border-radius:38px; transform:rotate(-16deg); }
.commerce-art-icon { display:grid; place-items:center; width:88px; height:88px; border:1px solid #DED5E7; border-radius:27px; background:linear-gradient(140deg,#F0EAF6,#E6DFEE); color:#6F6382; box-shadow:0 8px 20px -12px #564B6850; }
.commerce-art-icon svg { width:40px; height:40px; }
.commerce-art-spark { position:absolute; right:1px; bottom:6px; display:grid; place-items:center; width:28px; height:28px; border:4px solid white; border-radius:50%; background:#C08B7F; color:white; font-size:17px; line-height:1; }
.commerce-empty h3 { font:700 clamp(21px,2.5vw,26px)/1.3 'Lato',sans-serif; letter-spacing:-.5px; color:#3E354C; margin:0; }
.commerce-empty-copy { max-width:440px; margin:12px auto 24px; color:#80738A; font-size:14px; line-height:1.8; }
.commerce-button { display:inline-flex; align-items:center; justify-content:center; gap:20px; min-height:44px; padding:12px 22px; border-radius:10px; background:#655274; color:white; font:700 13px/1.5 'Lato',sans-serif; text-decoration:none; transition:background .15s; }
.commerce-button:hover { background:#4E3E5D; }
.commerce-button svg { width:17px; height:17px; }
.commerce-button:focus-visible { outline:2px solid #C08B7F; outline-offset:4px; }
.commerce-empty-note { margin:32px -24px 0; padding:16px 24px; border-top:1px solid #ECE5EF; background:#FFFFFF99; color:#918197; font-size:12px; line-height:1.6; }
.commerce-page article { border-color:#E6DFEB; border-radius:18px; box-shadow:none; }
.commerce-page article:hover { box-shadow:0 8px 24px -16px #564B6850; transform:none; }
.commerce-page table thead { background:#F0EBF5; color:#6F6382; }
.commerce-page table tbody tr:hover { background:#FCF5F1; }
.commerce-track-search { padding:20px; border:1px solid #E6DFEB; border-radius:18px; background:white; }
.commerce-track-search label { display:block; margin-bottom:10px; font-size:12px; font-weight:700; color:#6F6382; }
.commerce-track-search input[type=text] { background:#FAF8FC; border-color:#DED5E7; min-width:0; }
.commerce-tracking-card { padding:26px; border:1px solid #E6DFEB; border-radius:22px; background:white; }
.tracking-status-panel { display:flex; flex-wrap:wrap; gap:20px; justify-content:space-between; padding:18px; margin-top:18px; border-radius:14px; background:linear-gradient(115deg,#F0EBF5,#FBF0EB); color:#564B68; }
.tracking-caption { display:block; font-size:12px; margin-bottom:5px; color:#62546E; }
.tracking-details { display:flex; flex-wrap:wrap; gap:20px 36px; margin:20px 0; }
.tracking-details dt { font-size:12px; color:#6B6173; }
.tracking-details dd { margin:4px 0 0; font-size:14px; font-weight:600; color:#3E3548; }
.tracking-explainer { margin:12px 0; font-size:13px; line-height:1.6; color:#625B6A; }
.tracking-attention { padding:14px 16px; border-left:3px solid #8C5A50; background:#FFF5F3; color:#75473E; border-radius:8px; font-size:14px; line-height:1.6; }
.tracking-refresh { display:inline-block; padding:10px 16px; border:1px solid #D9CDDF; border-radius:10px; color:#564B68; font-size:13px; font-weight:700; }
.tracking-refresh:hover { background:#F0EBF5; }
.tracking-current { display:inline-block; margin-left:6px; padding:2px 8px; border-radius:20px; background:#F0EBF5; color:#564B68; font-size:11px; }
.tracking-history { margin-top:24px; border-top:1px solid #E6DFEB; padding-top:18px; }
.tracking-history summary { cursor:pointer; color:#564B68; font-size:14px; font-weight:700; }
.tracking-history article { padding:14px 0; border-bottom:1px solid #EEE8F0; font-size:14px; color:#3E3548; overflow-wrap:anywhere; }
.tracking-history article p { font-size:13px; line-height:1.6; margin-top:4px; color:#625B6A; }
.commerce-tracking-summary { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:24px; padding:24px; border:1px solid #E7DFED; border-radius:14px; background:linear-gradient(115deg,#F0EBF5,#FBF0EB); }
.commerce-tracking-summary p:first-child { margin-bottom:8px; font-size:10px; font-weight:800; letter-spacing:.09em; text-transform:uppercase; color:#86778F; }
.commerce-tracking-summary p:last-child { font-size:14px; font-weight:800; color:#564B68; overflow-wrap:anywhere; }
.commerce-tracking-summary > div:last-child p:last-child { color:#95695F; }
.commerce-timeline-title { margin:24px 0 0; padding-top:22px; border-top:1px solid #EEE7F2; font:700 15px 'Lato',sans-serif; color:#564B68; }
.commerce-tracking-card ol { margin-left:8px; }
.commerce-tracking-card li > div { padding:2px 0 10px; }
.notification-read-all { display:inline-flex; align-items:center; gap:8px; padding:11px 16px; border:1px solid #E1D6E8; border-radius:10px; background:white; color:#655274; font-size:12px; font-weight:700; cursor:pointer; }
.notification-read-all svg { width:18px; height:18px; }
.notification-read-all:hover { background:#F0EBF5; }
.notification-inbox { overflow:hidden; border:1px solid #E6DFEB; border-radius:22px; background:white; }
.notification-toolbar { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; padding:22px 26px; border-bottom:1px solid #E9E1EE; background:linear-gradient(110deg,#F5F0F9,#FCF4F0); }
.notification-inbox-title { display:flex; align-items:center; gap:10px; font-size:14px; font-weight:800; color:#564B68; }
.notification-inbox-title span { padding:3px 9px; border:1px solid #E6D3CB; border-radius:20px; background:#FAEDE7; color:#95695F; font-size:11px; }
.notification-search { position:relative; width:320px; max-width:100%; }
.notification-search input { width:100%; min-height:42px; padding:10px 14px 10px 38px; border:1px solid #E1D6E8; border-radius:10px; background:white; color:#564B68; font-size:12px; }
.notification-search svg { position:absolute; left:13px; top:13px; width:16px; height:16px; color:#9989A5; pointer-events:none; }
.notification-row { position:relative; width:100%; gap:18px; padding:24px 26px; border:0; border-bottom:1px solid #F0EAF4; background:white; text-align:left; cursor:pointer; transition:background .15s; }
.notification-row:not(.hidden) { display:flex; }
.notification-row:has(.dash-notif-dot) { background:#FCF7F4; box-shadow:inset 3px 0 #C08B7F; }
.notification-row:hover { background:#F6F2F9; }
.notification-type-icon { display:grid; place-items:center; flex:0 0 44px; height:44px; border:1px solid #E7DFED; border-radius:14px; background:#F2EDF7; color:#776188; }
.notification-type-icon svg { width:22px; height:22px; }
.notification-body { display:block; flex:1; min-width:0; }
.notification-meta { display:flex; justify-content:space-between; flex-wrap:wrap; gap:5px 16px; margin-bottom:7px; color:#95859F; font-size:11px; }
.notification-meta > span:first-child { color:#A17165; font-weight:700; }
.notification-title { display:block; color:#4E405D; font-size:14px; font-weight:700; line-height:1.5; overflow-wrap:anywhere; }
.notification-message { display:block; margin-top:5px; color:#83738E; font-size:13px; line-height:1.75; overflow-wrap:anywhere; }
.notification-unread { flex:0 0 7px; height:7px; margin-top:7px; border-radius:50%; background:#B47F72; }
.notification-inbox-footer { margin:0; padding:16px 26px; background:#FCFAFD; color:#9A8BA4; font-size:11px; }
.notification-no-match { padding:40px 24px; text-align:center; color:#83738E; font-size:13px; }
.notification-read-all:focus-visible,.notification-row:focus-visible,.notification-search input:focus-visible { outline:2px solid #8A739D; outline-offset:-3px; }
@media(max-width:640px) { .notification-toolbar,.notification-row { padding:18px; } .notification-row { gap:12px; } .notification-search { width:100%; } .notification-type-icon { flex-basis:36px; height:36px; border-radius:11px; } }
.orders-filter-bar { display:flex; gap:6px; padding:8px; overflow-x:auto; border:1px solid #E6DFEB; border-radius:16px; background:white; }
.orders-filter-bar .order-filter { display:flex; align-items:center; justify-content:center; flex:1 0 auto; gap:8px; padding:12px 15px; border-radius:10px; color:#80738A; font-size:12px; white-space:nowrap; }
.orders-filter-bar .order-filter span { margin:0; padding:2px 6px; border-radius:5px; background:#F2EDF7; color:#8A7897; font-size:10px; opacity:1; }
.orders-filter-bar .order-filter.is-active { background:#655274; color:white; }
.orders-filter-bar .order-filter.is-active span { background:#FFFFFF21; color:#F4E8E4; }
.commerce-page .buyer-order-card { overflow:hidden; border:1px solid #E6DFEB; border-radius:18px; background:white; }
.buyer-order-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; padding:20px 22px; border-bottom:1px solid #ECE5F0; background:linear-gradient(110deg,#F6F2FA,#FCF5F1); }
.buyer-order-store { display:flex; align-items:center; gap:12px; min-width:0; }
.buyer-order-store > div { min-width:0; }
.buyer-order-store p { margin:0 0 4px; color:#564B68; font-size:14px; font-weight:800; overflow-wrap:anywhere; }
.buyer-order-store div > span { color:#95869D; font-size:11px; overflow-wrap:anywhere; }
.buyer-store-icon { display:grid; place-items:center; width:38px; height:38px; flex-shrink:0; border:1px solid #E1D6E8; border-radius:12px; background:white; color:#8A739D; }
.buyer-store-icon svg { width:19px; height:19px; }
.buyer-order-status { display:inline-flex; align-items:center; gap:7px; padding:7px 11px; border:1px solid #E4D7CF; border-radius:20px; background:#FAEEE8; color:#95695F; font-size:11px; font-weight:700; }
.buyer-order-status > span { width:5px; height:5px; border-radius:50%; background:currentColor; }
.buyer-order-card[data-status=completed] .buyer-order-status { color:#655274; background:#EDE7F3; border-color:#DDD3E6; }
.buyer-order-card[data-status=cancelled] .buyer-order-status { color:#897D92; background:#F3EFF6; border-color:#E4DCEB; }
.buyer-order-product { padding-block:22px; }
.buyer-order-product img { width:76px; height:76px; border-color:#EEE6F2; background:#F8F5FB; }
.buyer-order-product-name { color:#564B68; overflow-wrap:anywhere; line-height:1.6; margin-bottom:4px; }
.buyer-order-footer { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:20px; padding:20px 22px; border-top:1px solid #EEE7F2; }
.buyer-order-total { margin:0; font-size:11px; color:#95869D; }
.buyer-order-total strong { display:block; margin-top:3px; font-size:22px; letter-spacing:-.6px; color:#564B68; }
.buyer-order-actions { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
.buyer-order-secondary { display:inline-flex; align-items:center; justify-content:center; min-height:44px; padding:10px 15px; border:1px solid #E1D6E8; border-radius:10px; background:white; color:#776188; font-size:12px; font-weight:700; cursor:pointer; }
.buyer-order-secondary:hover { background:#F4EFF8; }
.buyer-order-secondary[data-cancel-order] { color:#A57970; border-color:transparent; }
.buyer-order-secondary:focus-visible,.orders-filter-bar button:focus-visible { outline:2px solid #8A739D; outline-offset:2px; }
.orders-filter-empty { padding:42px 24px; border:1px solid #E6DFEB; border-radius:18px; background:linear-gradient(120deg,#F8F4FB,#FCF4F0); text-align:center; color:#655274; font-size:14px; }
.orders-filter-empty p { margin-top:8px; font-size:12px; color:#95869D; }
@media(max-width:640px) { .buyer-order-header,.buyer-order-footer { padding:16px; } .buyer-order-product img { width:56px; height:56px; } .buyer-order-product { gap:12px; } .buyer-order-actions { width:100%; } .buyer-order-actions > * { flex:1 1 auto; } .buyer-order-actions form .commerce-button { width:100%; } }
.commerce-cart { background:#F6F4F8; }
.commerce-cart h3 { font-family:'Lato',sans-serif; }
.commerce-cart .cart-summary { border-top:3px solid #C08B7F; background:linear-gradient(150deg,white,#FBF5F2); }
@media(max-width:640px) {
 .commerce-empty { min-height:0; padding-top:30px; border-radius:18px; }
 .commerce-heading { padding-top:0; }
 .commerce-heading h1,.commerce-heading h2 { font-size:27px; }
 .commerce-tracking-summary { grid-template-columns:1fr; gap:16px; padding:18px; }
 .commerce-tracking-summary > div + div { padding-top:14px; border-top:1px solid #E4D9E5; }
 .commerce-tracking-card { padding:18px; }
 .commerce-track-search { padding:16px; }
}
</style>
@endonce
