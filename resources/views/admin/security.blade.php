@extends('layouts.admin')
@section('title', 'Security')
@section('content')
<div id="erpAlerts"></div>

<div class="mb-4">
    <h1 class="h3 mb-1">Security</h1>
    <p class="text-muted small mb-0">Protect your account with a second sign-in step and a strong password.</p>
</div>

<div class="erp-row erp-row--even">
    <div>
        <div class="erp-card mb-4">
            <div class="erp-card__head">
                <h3 class="erp-card__title">Multi-factor authentication</h3>
                <span id="mfaBadge" class="badge bg-secondary">…</span>
            </div>
            <div class="erp-card__body">
                <p class="text-muted small">When on, signing in needs a 6-digit code from an authenticator app (Google Authenticator, Microsoft Authenticator, Authy) as well as your password.</p>

                {{-- Off: start setup --}}
                <div id="mfaOff" style="display:none">
                    <button class="btn btn-primary" id="mfaStart">Set up authenticator app</button>
                    <div id="mfaSetup" style="display:none" class="mt-4">
                        <ol class="small ps-3">
                            <li class="mb-2">Open your authenticator app and add an account by scanning this QR code (or type the key).</li>
                        </ol>
                        <div class="d-flex flex-wrap gap-4 align-items-center mb-3">
                            <div id="mfaQr" style="background:#fff;padding:8px;border:1px solid var(--erp-border);border-radius:8px"></div>
                            <div>
                                <div class="small text-muted">Setup key</div>
                                <div id="mfaSecret" style="font-family:monospace;font-size:1.05rem;letter-spacing:.08em;word-break:break-all"></div>
                            </div>
                        </div>
                        <form id="mfaEnableForm" class="d-flex flex-wrap gap-2 align-items-end">
                            <div>
                                <label class="form-label small mb-1">Enter the 6-digit code to confirm</label>
                                <input class="form-control" id="mfaEnableCode" inputmode="numeric" maxlength="6" placeholder="000000" autocomplete="one-time-code" style="width:160px;letter-spacing:.3em;text-align:center" required>
                            </div>
                            <button class="btn btn-primary" type="submit">Turn on MFA</button>
                        </form>
                    </div>
                </div>

                {{-- On: allow disabling --}}
                <div id="mfaOn" style="display:none">
                    <div class="alert alert-success py-2 small">MFA is on for your account.</div>
                    <form id="mfaDisableForm" class="row g-2 align-items-end">
                        <div class="col-md-5"><label class="form-label small mb-1">Password</label><input type="password" class="form-control" id="mfaDisPw" autocomplete="current-password" required></div>
                        <div class="col-md-4"><label class="form-label small mb-1">Current code</label><input class="form-control" id="mfaDisCode" inputmode="numeric" maxlength="6" placeholder="000000" required></div>
                        <div class="col-md-3"><button class="btn btn-outline-danger w-100" type="submit">Turn off</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="erp-card">
            <div class="erp-card__head"><h3 class="erp-card__title">Change password</h3></div>
            <div class="erp-card__body">
                <form id="pwForm" class="row g-3" style="max-width:480px">
                    <div class="col-12"><label class="form-label">Current password</label><input type="password" class="form-control" id="pwCur" autocomplete="current-password" required></div>
                    <div class="col-12"><label class="form-label">New password <span class="text-muted">(min 8 characters)</span></label><input type="password" class="form-control" id="pwNew" minlength="8" maxlength="100" autocomplete="new-password" required></div>
                    <div class="col-12"><label class="form-label">Confirm new password</label><input type="password" class="form-control" id="pwNew2" minlength="8" autocomplete="new-password" required></div>
                    <div class="col-12"><button class="btn btn-primary" type="submit">Update password</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="erp-card" style="align-self:start">
        <div class="erp-card__head"><h3 class="erp-card__title">How your account is protected</h3></div>
        <div class="erp-card__body small">
            <div class="erp-item"><div class="erp-item__body"><div class="erp-item__title">Automatic sign-out</div><div class="erp-item__sub">You are signed out after 30 minutes without activity, so an unattended counter screen is not left open.</div></div></div>
            <div class="erp-item"><div class="erp-item__body"><div class="erp-item__title">Lock after failed sign-ins</div><div class="erp-item__sub">After 5 wrong passwords or codes the account is locked for 15 minutes. Only the owner needs to know about it — it unlocks itself.</div></div></div>
            <div class="erp-item"><div class="erp-item__body"><div class="erp-item__title">Audit trail</div><div class="erp-item__sub">Sign-ins, failed attempts, stock changes, price edits and returns are recorded with who, what and when (owners see the Activity Log).</div></div></div>
            <div class="erp-item"><div class="erp-item__body"><div class="erp-item__title">Lost your phone?</div><div class="erp-item__sub">Ask an owner to reset MFA for your account from the Team page, then set it up again.</div></div></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var offBox = document.getElementById('mfaOff'), onBox = document.getElementById('mfaOn'), badge = document.getElementById('mfaBadge');

    function showStatus(on) {
        offBox.style.display = on ? 'none' : '';
        onBox.style.display = on ? '' : 'none';
        badge.textContent = on ? 'On' : 'Off';
        badge.className = 'badge ' + (on ? 'bg-success' : 'bg-secondary');
    }
    function refresh() {
        return ERP.api('/api/auth/me').then(function (j) { showStatus(!!j.data.mfa_enabled); }).catch(ERP.error);
    }

    document.getElementById('mfaStart').addEventListener('click', function () {
        ERP.api('/api/auth/mfa/setup', { method: 'POST' }).then(function (j) {
            document.getElementById('mfaSetup').style.display = '';
            document.getElementById('mfaSecret').textContent = String(j.data.secret).replace(/(.{4})/g, '$1 ').trim();
            ERP.qr(document.getElementById('mfaQr'), j.data.uri, 176);
            document.getElementById('mfaEnableCode').focus();
        }).catch(ERP.error);
    });

    document.getElementById('mfaEnableForm').addEventListener('submit', function (e) {
        e.preventDefault();
        ERP.api('/api/auth/mfa/enable', { method: 'POST', body: { code: document.getElementById('mfaEnableCode').value } })
            .then(function (j) {
                ERP.notify(j.message || 'MFA is now on.');
                document.getElementById('mfaSetup').style.display = 'none';
                document.getElementById('mfaEnableCode').value = '';
                refresh();
            }).catch(ERP.error);
    });

    document.getElementById('mfaDisableForm').addEventListener('submit', function (e) {
        e.preventDefault();
        ERP.api('/api/auth/mfa/disable', { method: 'POST', body: { password: document.getElementById('mfaDisPw').value, code: document.getElementById('mfaDisCode').value } })
            .then(function (j) {
                ERP.notify(j.message || 'MFA is now off.');
                document.getElementById('mfaDisPw').value = ''; document.getElementById('mfaDisCode').value = '';
                refresh();
            }).catch(ERP.error);
    });

    document.getElementById('pwForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var n = document.getElementById('pwNew').value;
        if (n !== document.getElementById('pwNew2').value) { ERP.notify('The new passwords do not match.', 'danger'); return; }
        ERP.api('/api/auth/change-password', { method: 'POST', body: { current_password: document.getElementById('pwCur').value, new_password: n } })
            .then(function (j) { ERP.notify(j.message || 'Password updated.'); e.target.reset(); }).catch(ERP.error);
    });

    refresh();
})();
</script>
@endverbatim
@endpush
