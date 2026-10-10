@once
<style>
    .address-dialog { position: fixed; inset: 0; margin: auto; padding: 0; width: min(576px, calc(100% - 32px)); max-width: none; max-height: calc(100vh - 32px); max-height: calc(100dvh - 32px); border: 1px solid #E5E1EA; border-radius: 20px; background: #fff; color: #282133; box-shadow: 0 24px 80px rgba(40,33,51,.25); overflow: hidden; }
    .address-dialog[open] { display: flex; flex-direction: column; }
    .address-dialog::backdrop { background: rgba(25,20,38,.5); backdrop-filter: blur(3px); }
    .address-dialog-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 20px 24px; border-bottom: 1px solid #F0EDF3; background: #FAF9FC; flex-shrink: 0; }
    .address-dialog-header h3 { margin: 0; font: 700 17px/1.4 'Lato', sans-serif; }
    .address-dialog-header p { margin-top: 4px; font-size: 12px; color: #766D80; }
    .address-dialog-close { flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%; font-size: 24px; line-height: 1; color: #766D80; cursor: pointer; }
    .address-dialog-close:hover { background: #EDE9F2; color: #282133; }
    .address-dialog-body { min-height: 0; padding: 20px 24px; overflow-y: auto; overscroll-behavior: contain; }
    .address-dialog :is(button, select, input):focus-visible { outline: 2px solid #91879E; outline-offset: 2px; }
    .settings-card { padding: 24px; border: 1px solid #E5E1EA; border-radius: 16px; background: #fff; box-shadow: 0 2px 5px rgba(40,33,51,.025); }
    .settings-section-heading { padding-bottom: 18px; margin-bottom: 20px; border-bottom: 1px solid #F0EDF3; }
    .settings-section-heading h3 { font-family: 'Lato', 'Segoe UI', sans-serif; font-size: 18px; line-height: 1.4; font-weight: 700; color: #282133; margin: 0; }
    .settings-section-heading p { font-size: 13px; line-height: 1.6; color: #766D80; margin: 5px 0 0; }
    .settings-label { display: block; margin-bottom: 7px; color: #51475D; font-size: 13px; font-weight: 700; }
    .settings-input { width: 100%; border: 1px solid #DDD7E5; border-radius: 8px; padding: 10px 12px; font: inherit; font-size: 14px; background: #fff; color: #282133; }
    .settings-input:focus { outline: 2px solid #D8D0E3; outline-offset: 1px; border-color: #91879E; }
    .settings-input[readonly] { background: #F8F7FA; color: #766D80; }
    .settings-button { display: inline-flex; justify-content: center; border: 0; border-radius: 8px; padding: 10px 18px; background: #564B68; color: white; font-size: 13px; font-weight: 700; cursor: pointer; }
    .settings-button:hover { background: #443A54; }
    .settings-text-button { color: #665477; font-size: 13px; font-weight: 700; cursor: pointer; }
    .settings-text-button:hover { text-decoration: underline; }
    .settings-status { display: inline-flex; border-radius: 6px; padding: 4px 8px; font-size: 11px; font-weight: 700; background: #F1EFF5; color: #665477; }
    .settings-address { padding: 18px; border: 1px solid #E7E3EC; border-radius: 12px; }
    .settings-edit { margin-top: 12px; }
    .settings-edit > summary, .settings-add > summary { cursor: pointer; font-size: 13px; font-weight: 700; color: #665477; }
    .settings-add { margin-top: 20px; padding: 16px; background: #FAF9FC; border: 1px dashed #DCD5E5; border-radius: 10px; }
    .settings-empty { padding: 20px; text-align: center; color: #766D80; font-size: 14px; }
    .settings-error { margin: 12px 0 18px; padding: 12px 16px; background: #FFF1F2; border-radius: 8px; color: #9F1239; font-size: 13px; }
    .settings-card :is(button, summary, a):focus-visible { outline: 2px solid #91879E; outline-offset: 3px; }
    @media(max-width: 640px) { .settings-card { padding: 18px; } }
</style>
@endonce
