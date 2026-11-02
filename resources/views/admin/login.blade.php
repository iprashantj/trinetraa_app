<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | Admin — Trinetraa</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="/css/globals.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f3f4f9;font-family:'Inter',sans-serif">
    <div style="background:#fff;border:1px solid #e8eaf0;border-radius:12px;padding:40px 36px;width:380px;max-width:92vw;box-shadow:0 10px 40px rgba(27,36,54,0.08)">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:32px">
            <img src="/icons/favicon.svg" width="38" height="38" alt="" >
            <span style="font-weight:700;font-size:1.2rem;color:#1b2436">Trinetraa Admin</span>
        </div>
        <h1 style="font-size:1.5rem;font-weight:700;color:#1b2436;margin-bottom:8px">Sign in</h1>
        <p style="color:#6b7a90;font-size:0.875rem;margin-bottom:28px">Owner &amp; team access only</p>
        <div id="loginError" style="background:rgba(239,68,68,0.15);color:#c23b3b;border:1px solid rgba(239,68,68,0.35);border-radius:12px;padding:10px 14px;margin-bottom:16px;font-size:0.875rem;display:none"></div>
        <form id="loginForm">
            <div style="margin-bottom:16px">
                <label style="display:block;font-weight:600;font-size:0.8rem;color:#283c50;margin-bottom:6px">Email</label>
                <input type="email" id="email" required
                    style="width:100%;padding:10px 14px;background:#fff;border:1px solid #dcdfe8;border-radius:8px;font-size:0.875rem;outline:none;font-family:inherit;color:#283c50;box-sizing:border-box"
                    placeholder="admin@trinetraa.com">
            </div>
            <div style="margin-bottom:24px">
                <label style="display:block;font-weight:600;font-size:0.8rem;color:#283c50;margin-bottom:6px">Password</label>
                <input type="password" id="password" required
                    style="width:100%;padding:10px 14px;background:#fff;border:1px solid #dcdfe8;border-radius:8px;font-size:0.875rem;outline:none;font-family:inherit;color:#283c50;box-sizing:border-box"
                    placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
            </div>
            <div id="mfaWrap" style="margin-bottom:24px;display:none">
                <label style="display:block;font-weight:600;font-size:0.8rem;color:#283c50;margin-bottom:6px">Authentication code</label>
                <input type="text" id="mfaCode" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="\d{6}"
                    style="width:100%;padding:10px 14px;background:#fff;border:1px solid #dcdfe8;border-radius:8px;font-size:1.1rem;letter-spacing:.35em;text-align:center;outline:none;font-family:inherit;color:#283c50;box-sizing:border-box"
                    placeholder="000000">
                <div style="font-size:.75rem;color:#6b7a90;margin-top:6px">Open your authenticator app and enter the 6-digit code.</div>
            </div>
            <button type="submit" id="loginBtn"
                style="width:100%;padding:12px;background:#3454d1;color:#fff;border:none;border-radius:8px;font-weight:700;font-size:0.95rem;cursor:pointer;font-family:inherit;box-shadow:0 8px 20px rgba(52,84,209,0.3)">
                Sign In
            </button>
        </form>
    </div>
</div>
@verbatim
<script>
(function () {
    var form = document.getElementById('loginForm');
    var errBox = document.getElementById('loginError');
    var btn = document.getElementById('loginBtn');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        errBox.style.display = 'none';
        errBox.textContent = '';
        btn.disabled = true;
        btn.style.cursor = 'not-allowed';
        btn.style.opacity = '0.7';
        btn.textContent = 'Signing in…';

        var email = document.getElementById('email').value;
        var password = document.getElementById('password').value;

        try {
            var payload = { email: email, password: password };
            var codeEl = document.getElementById('mfaCode');
            var wrap = document.getElementById('mfaWrap');
            if (wrap.style.display !== 'none') payload.code = codeEl.value;
            var res = await fetch('/api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            var data = await res.json();
            if (res.ok && data.mfa_required) {
                // Second step: reveal the code field and wait for the 6-digit code.
                wrap.style.display = '';
                codeEl.focus();
                btn.disabled = false;
                btn.style.cursor = 'pointer';
                btn.style.opacity = '1';
                btn.textContent = 'Verify & Sign In';
                return;
            }
            if (!res.ok) {
                // 423 = account locked after repeated failures; the API message says for how long.
                throw new Error(data.message || (res.status === 423 ? 'Account temporarily locked. Try again later.' : 'Login failed.'));
            }
            window.location.href = '/admin/dashboard';
        } catch (err) {
            errBox.textContent = err.message || 'Login failed.';
            errBox.style.display = 'block';
            btn.disabled = false;
            btn.style.cursor = 'pointer';
            btn.style.opacity = '1';
            btn.textContent = document.getElementById('mfaWrap').style.display === 'none' ? 'Sign In' : 'Verify & Sign In';
        }
    });
})();
</script>
@endverbatim
</body>
</html>
