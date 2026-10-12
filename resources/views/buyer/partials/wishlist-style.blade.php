@once
<style>
.wishlist-page { font-family:'Lato',sans-serif; }
.wishlist-page .wishlist-heading { gap:20px; padding:24px 28px; margin-bottom:24px; border:1px solid var(--accent-primary,#D4A69A); border-radius:18px; background:linear-gradient(115deg,var(--brand-light,#F1EFF5),var(--accent-soft,#FFF5F3)); }
.wishlist-page .wishlist-heading h2 { font:600 clamp(32px,3vw,42px)/1.1 'Cormorant Garamond',Georgia,serif; letter-spacing:-.025em; color:var(--brand-darkest,#282133); }
.wishlist-heading .commerce-eyebrow { color:var(--accent-dark,#8C5A50); margin-bottom:8px; }
.wishlist-heading-actions { display:flex; align-items:center; flex-wrap:wrap; gap:16px; }
.wishlist-browse { display:inline-flex; align-items:center; gap:10px; min-height:44px; color:var(--brand-dark,#564B68); font-size:13px; font-weight:700; text-decoration:none; }
.wishlist-browse:hover { text-decoration:underline; text-underline-offset:4px; }
.wishlist-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(min(100%,230px),1fr)); align-items:stretch; gap:20px; }
.wishlist-page .wishlist-card { display:flex; flex-direction:column; min-width:0; overflow:hidden; border:1px solid var(--accent-primary,#D4A69A); border-radius:16px; background:#fff; transition:box-shadow .2s,border-color .2s; }
.wishlist-page .wishlist-card:hover { border-color:var(--accent-dark,#8C5A50); box-shadow:0 8px 24px #564B6812; }
.wishlist-media { position:relative; aspect-ratio:4/3; margin:8px 8px 0; overflow:hidden; border-radius:10px; background:var(--brand-light,#F1EFF5); }
.wishlist-media img { display:block; width:100%; height:100%; object-fit:cover; }
.wishlist-remove { position:absolute; top:8px; right:8px; display:grid; place-items:center; width:44px; height:44px; padding:0; border:1px solid var(--accent-muted,#F5CFC7); border-radius:50%; background:#fff; color:var(--accent-dark,#8C5A50); cursor:pointer; transition:background .2s; }
.wishlist-remove svg { width:19px; height:19px; }
.wishlist-remove:hover { background:var(--accent-light,#FCEAE6); }
.wishlist-body { display:flex; flex-direction:column; flex:1; padding:16px; }
.wishlist-page .wishlist-name { display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:2; overflow:hidden; min-height:42px; margin:0; font:700 14px/1.5 'Lato',sans-serif; letter-spacing:0; color:var(--brand-darkest,#282133); overflow-wrap:anywhere; }
.wishlist-variant { margin:6px 0 0; color:var(--brand-deep,#6F6382); font-size:12px; line-height:1.5; overflow-wrap:anywhere; }
.wishlist-price { margin:16px 0; padding-top:14px; border-top:1px solid var(--brand-border,#E1DDE7); font-size:22px; line-height:1.2; font-weight:900; font-variant-numeric:tabular-nums; letter-spacing:-.03em; color:var(--brand-dark,#564B68); }
.wishlist-cart { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; min-height:44px; margin-top:auto; padding:10px 12px; border:0; border-radius:9px; background:linear-gradient(110deg,var(--brand-deep,#6F6382),var(--accent-dark,#8C5A50)); color:#fff; font:700 13px/1.5 'Lato',sans-serif; cursor:pointer; }
.wishlist-cart svg { width:18px; height:18px; }
.wishlist-cart:hover { background:linear-gradient(110deg,var(--brand-dark,#564B68),var(--accent-darkest,#4A2520)); }
.wishlist-page :is(button,a):focus-visible { outline:2px solid var(--brand-deep,#6F6382); outline-offset:3px; }
@media(max-width:540px) {
    .wishlist-page .wishlist-heading { padding:20px; margin-bottom:18px; }
    .wishlist-heading-actions { width:100%; justify-content:space-between; gap:8px; }
    .wishlist-grid { gap:14px; }
    .wishlist-page .wishlist-card { flex-direction:row; }
    .wishlist-media { flex:0 0 32%; align-self:flex-start; aspect-ratio:3/4; margin:10px 0 10px 10px; }
    .wishlist-remove { top:5px; right:5px; }
    .wishlist-body { min-width:0; padding:14px; }
    .wishlist-price { font-size:20px; margin:12px 0; padding-top:10px; }
}
@media(prefers-reduced-motion:reduce) { .wishlist-page .wishlist-card,.wishlist-remove { transition:none; } }
</style>
@endonce
