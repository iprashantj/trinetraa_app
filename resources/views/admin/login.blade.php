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
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:radial-gradient(70rem 45rem at 80% -10%, rgba(15,118,110,0.25) 0%, transparent 55%), radial-gradient(50rem 35rem at 10% 110%, rgba(20,184,166,0.12) 0%, transparent 55%), #0B1220;font-family:'Inter',sans-serif">
    <div style="background:rgba(255,255,255,0.06);-webkit-backdrop-filter:blur(24px) saturate(1.4);backdrop-filter:blur(24px) saturate(1.4);border:1px solid rgba(255,255,255,0.16);border-radius:24px;padding:40px 36px;width:380px;box-shadow:0 30px 80px rgba(0,0,0,0.45)">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:32px">
            <img src="/icons/favicon.svg" width="38" height="38" alt="" style="filter:drop-shadow(0 2px 8px rgba(0,0,0,0.35))">
            <span style="font-weight:700;font-size:1.2rem;color:#F8FAFC">Trinetraa Admin</span>
        </div>
        <h1 style="font-size:1.5rem;font-weight:700;color:#F8FAFC;margin-bottom:8px">Sign in</h1>
        <p style="color:#9FB1C7;font-size:0.875rem;margin-bottom:28px">Owner &amp; team access only</p>
        <div id="loginError" style="background:rgba(239,68,68,0.15);color:#F87171;border:1px solid rgba(239,68,68,0.35);border-radius:12px;padding:10px 14px;margin-bottom:16px;font-size:0.875rem;display:none"></div>
        <form id="loginForm">
            <div style="margin-bottom:16px">
                <label style="display:block;font-weight:600;font-size:0.8rem;color:#C0CDDD;margin-bottom:6px">Email</label>
                <input type="email" id="email" required
                    style="width:100%;padding:10px 14px;background:rgba(255,255,255,0.07);border:1.5px solid rgba(255,255,255,0.18);border-radius:12px;font-size:0.875rem;outline:none;font-family:inherit;color:#F1F5F9;color-scheme:dark;box-sizing:border-box"
                    placeholder="admin@trinetraa.com">
            </div>
            <div style="margin-bottom:24px">
                <label style="display:block;font-weight:600;font-size:0.8rem;color:#C0CDDD;margin-bottom:6px">Password</label>
                <input type="password" id="password" required
                    style="width:100%;padding:10px 14px;background:rgba(255,255,255,0.07);border:1.5px solid rgba(255,255,255,0.18);border-radius:12px;font-size:0.875rem;outline:none;font-family:inherit;color:#F1F5F9;color-scheme:dark;box-sizing:border-box"
                    placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
            </div>
            <button type="submit" id="loginBtn"
                style="width:100%;padding:12px;background:linear-gradient(135deg,#0F766E 0%,#0D9488 100%);color:#fff;border:none;border-radius:12px;font-weight:700;font-size:0.95rem;cursor:pointer;font-family:inherit;box-shadow:0 12px 30px rgba(15,118,110,0.45)">
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
            var res = await fetch('/api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, password: password })
            });
            var data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Login failed.');
            }
            window.location.href = '/admin/dashboard';
        } catch (err) {
            errBox.textContent = err.message || 'Login failed.';
            errBox.style.display = 'block';
            btn.disabled = false;
            btn.style.cursor = 'pointer';
            btn.style.opacity = '1';
            btn.textContent = 'Sign In';
        }
    });
})();
</script>
@endverbatim
</body>
</html>
