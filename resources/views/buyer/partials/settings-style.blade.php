@once
<style>
    .address-dialog { position: fixed; inset: 0; margin: auto; padding: 0; width: min(576px, calc(100% - 32px)); max-width: none; max-height: calc(100vh - 32px); max-height: calc(100dvh - 32px); border: 1px solid var(--dm-border, #E5E1EA); border-radius: 20px; background: var(--dm-surface, #fff); color: var(--dm-ink, #282133); box-shadow: 0 24px 80px rgba(40,33,51,.25); overflow: hidden; }
    .address-dialog[open] { display: flex; flex-direction: column; }
    .address-dialog::backdrop { background: rgba(25,20,38,.5); backdrop-filter: blur(3px); }
    .address-dialog-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 20px 24px; border-bottom: 1px solid var(--dm-border, #F0EDF3); background: var(--dm-surface, #FAF9FC); flex-shrink: 0; }
    .address-dialog-header h3 { margin: 0; font: 700 17px/1.4 'Inter', sans-serif; }
    .address-dialog-header p { margin-top: 4px; font-size: 12px; color: var(--dm-muted, #766D80); }
    .address-dialog-close { flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%; font-size: 24px; line-height: 1; color: var(--dm-muted, #766D80); cursor: pointer; }
    .address-dialog-close:hover { background: var(--dm-elevated, #EDE9F2); color: var(--dm-ink, #282133); }
    .address-dialog-body { min-height: 0; padding: 20px 24px; overflow-y: auto; overscroll-behavior: contain; }
    .address-dialog :is(button, select, input):focus-visible { outline: 2px solid #91879E; outline-offset: 2px; }
    .settings-card { padding: 24px; border: 1px solid var(--dm-border, #E5E1EA); border-radius: 16px; background: var(--dm-surface, #fff); box-shadow: 0 2px 5px rgba(40,33,51,.025); }
    .settings-section-heading { padding-bottom: 18px; margin-bottom: 20px; border-bottom: 1px solid var(--dm-border, #F0EDF3); }
    .settings-section-heading h3 { font-family: 'Inter', 'Segoe UI', sans-serif; font-size: 18px; line-height: 1.4; font-weight: 700; color: var(--dm-ink, #282133); margin: 0; }
    .settings-section-heading p { font-size: 13px; line-height: 1.6; color: var(--dm-muted, #766D80); margin: 5px 0 0; }
    .settings-label { display: block; margin-bottom: 7px; color: var(--dm-ink, #51475D); font-size: 13px; font-weight: 700; }
    .settings-input { width: 100%; border: 1px solid var(--dm-border, #DDD7E5); border-radius: 8px; padding: 10px 12px; font: inherit; font-size: 14px; background: var(--dm-surface, #fff); color: var(--dm-ink, #282133); }
    .settings-input:focus { outline: 2px solid #D8D0E3; outline-offset: 1px; border-color: #91879E; }
    .settings-input[readonly] { background: var(--dm-elevated, #F8F7FA); color: var(--dm-muted, #766D80); }
    .settings-button { display: inline-flex; justify-content: center; border: 0; border-radius: 8px; padding: 10px 18px; background: #564B68; color: white; font-size: 13px; font-weight: 700; cursor: pointer; }
    .settings-button:hover { background: #443A54; }
    .settings-text-button { color: var(--dm-muted, #665477); font-size: 13px; font-weight: 700; cursor: pointer; }
    .settings-text-button:hover { text-decoration: underline; }
    .settings-status { display: inline-flex; border-radius: 6px; padding: 4px 8px; font-size: 11px; font-weight: 700; background: var(--dm-elevated, #F1EFF5); color: var(--dm-muted, #665477); }
    .settings-address { padding: 18px; border: 1px solid var(--dm-border, #E7E3EC); border-radius: 12px; }
    .settings-edit { margin-top: 12px; }
    .settings-edit > summary, .settings-add > summary { cursor: pointer; font-size: 13px; font-weight: 700; color: var(--dm-muted, #665477); }
    .settings-add { margin-top: 20px; padding: 16px; background: var(--dm-surface, #FAF9FC); border: 1px dashed var(--dm-border, #DCD5E5); border-radius: 10px; }
    .settings-empty { padding: 20px; text-align: center; color: var(--dm-muted, #766D80); font-size: 14px; }
    .settings-error { margin: 12px 0 18px; padding: 12px 16px; background: var(--dm-elevated, #FFF1F2); border-radius: 8px; color: var(--dm-red-ink, #9F1239); font-size: 13px; }
    .settings-card :is(button, summary, a):focus-visible { outline: 2px solid #91879E; outline-offset: 3px; }
    .account-settings { max-width: 1180px; margin: 0 auto; padding: 8px 0 24px; }
    .settings-page-heading { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 10px 2px 26px; }
    .settings-eyebrow { margin: 0 0 10px; color: var(--dm-muted, #9A726D); font-size: 10px; font-weight: 900; letter-spacing: .18em; }
    .settings-page-heading h2 { margin: 0; font: 800 clamp(28px, 3vw, 36px)/1.2 'Inter', sans-serif; letter-spacing: -1.1px; color: var(--dm-ink, #30283C); }
    .settings-page-heading h2 span { color: #C08B7F; }
    .settings-page-heading h2 + p { margin: 10px 0 0; color: var(--dm-muted, #80768A); font-size: 14px; line-height: 1.6; }
    .settings-heading-icon { display: grid; place-items: center; flex: 0 0 64px; height: 64px; border-radius: 22px; background: linear-gradient(140deg, var(--dm-elevated, #EAE4F0), var(--dm-elevated, #F5E8E3)); color: var(--dm-muted, #8A6D87); transform: rotate(5deg); }
    .settings-heading-icon svg { width: 30px; height: 30px; transform: rotate(-5deg); }
    .settings-shortcuts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 24px; }
    .settings-shortcuts button { display: flex; align-items: center; gap: 10px; padding: 16px 18px; border: 1px solid var(--dm-border, #E6DFEA); border-radius: 14px; background: var(--dm-surface, #FFFFFFB3); color: var(--dm-ink, #62546E); font: 700 13px 'Inter', sans-serif; text-align: left; cursor: pointer; transition: background .15s, border-color .15s; }
    .settings-shortcuts button:hover { background: var(--dm-elevated, #F1EBF5); border-color: var(--dm-border, #C7B8D3); }
    .settings-shortcuts button:focus-visible { outline: 2px solid #91879E; outline-offset: 3px; }
    .settings-shortcuts svg { width: 18px; height: 18px; flex-shrink: 0; }
    .settings-shortcuts span { margin-left: auto; color: var(--dm-muted, #B5A8BD); font-size: 10px; }
    .account-settings .settings-card { margin-bottom: 22px; border: 1px solid var(--dm-border, #E7E0EC); border-radius: 22px; box-shadow: 0 8px 30px -18px rgba(67,48,83,.2); padding: 30px; scroll-margin-top: 180px; }
    .account-settings .settings-split-card { display: grid; grid-template-columns: 210px minmax(0, 1fr); gap: 34px; }
    .settings-split-card > .settings-section-heading { margin: 0; padding: 0 24px 0 0; border-bottom: 0; border-right: 1px solid var(--dm-border, #F0EBF3); }
    .settings-section-number { display: block; margin-bottom: 14px; font-size: 10px; font-weight: 900; letter-spacing: .12em; color: var(--dm-muted, #AE8178); }
    .account-settings .settings-section-heading h3 { font-size: 20px; letter-spacing: -.4px; }
    .settings-split-card .settings-section-heading p { margin-top: 10px; line-height: 1.8; }
    .settings-section-content { min-width: 0; }
    .settings-photo-form { display: flex; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 28px; padding: 20px; border: 1px solid var(--dm-border, #EEE7F1); border-radius: 16px; background: linear-gradient(120deg, var(--dm-elevated, #F8F5FB), var(--dm-elevated, #FCF7F4)); }
    .settings-photo-avatar { display: flex; align-items: center; justify-content: center; flex: 0 0 72px; height: 72px; overflow: hidden; border-radius: 22px; background: var(--dm-elevated, #E8E0ED); color: var(--dm-muted, #6F6382); font-size: 28px; font-weight: 700; outline: 4px solid #FFFFFF; box-shadow: 0 5px 14px #564B681A; }
    .settings-photo-details { flex: 1 1 180px; min-width: 0; }
    .settings-file-input { width: 100%; max-width: 260px; font-size: 11px; color: var(--dm-muted, #80748D); border-radius: 6px; }
    .settings-file-input::file-selector-button { margin-right: 8px; border: 1px solid var(--dm-border, #DED4E6); border-radius: 6px; background: var(--dm-surface, white); padding: 6px 9px; color: var(--dm-muted, #665477); font: inherit; font-weight: 700; cursor: pointer; }
    .settings-file-input:focus-visible { outline: 2px solid #91879E; outline-offset: 3px; }
    .account-settings .settings-label { font-size: 12px; color: var(--dm-ink, #665971); }
    .account-settings .settings-input { min-height: 44px; border-radius: 10px; background: var(--dm-surface, #FDFCFD); transition: border-color .15s, box-shadow .15s; }
    .account-settings .settings-input:focus { background: var(--dm-surface, white); outline: none; border-color: #A693B6; box-shadow: 0 0 0 3px #A693B61A; }
    .account-settings .settings-input[readonly] { background: var(--dm-elevated, #F5F2F7); }
    .account-settings .settings-button { align-items: center; gap: 16px; min-height: 42px; border-radius: 10px; background: #655274; box-shadow: 0 3px 8px #564B6814; transition: background .15s, box-shadow .15s; }
    .account-settings .settings-button:hover { background: #4E3E5D; box-shadow: 0 4px 12px #564B6826; }
    .account-settings .settings-button-secondary { border: 1px solid var(--dm-border, #DCD1E4); background: var(--dm-surface, white); color: var(--dm-muted, #665477); box-shadow: none; font-size: 12px; }
    .account-settings .settings-button-secondary:hover { background: var(--dm-elevated, #F1EBF5); }
    .settings-form-footer { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding-top: 22px; border-top: 1px solid var(--dm-border, #F0EBF3); }
    .settings-form-footer > span { max-width: 240px; font-size: 11px; line-height: 1.6; color: var(--dm-muted, #91859B); }
    .account-settings .settings-address { background: var(--dm-surface, #FDFCFD); border-radius: 14px; }
    .account-settings .settings-empty { border: 1px dashed var(--dm-border, #DCD1E4); border-radius: 14px; padding: 32px 20px; background: var(--dm-surface, #FAF8FC); }
    @media(max-width: 900px) {
        .account-settings .settings-split-card { grid-template-columns: 1fr; gap: 22px; }
        .settings-split-card > .settings-section-heading { border-right: 0; border-bottom: 1px solid var(--dm-border, #F0EBF3); padding: 0 0 20px; }
        .settings-section-number { margin-bottom: 8px; }
    }
    @media(max-width: 640px) {
        .settings-card, .account-settings .settings-card { padding: 20px; }
        .settings-page-heading { gap: 12px; }
        .settings-heading-icon { flex-basis: 48px; height: 48px; border-radius: 16px; }
        .settings-shortcuts { gap: 6px; }
        .settings-shortcuts button { flex-direction: column; align-items: flex-start; gap: 8px; padding: 12px; font-size: 11px; }
        .settings-shortcuts span { display: none; }
        .settings-photo-form { padding: 16px; }
        .settings-form-footer { flex-wrap: wrap; }
        .settings-form-footer .settings-button { width: 100%; }
    }
</style>
@endonce
