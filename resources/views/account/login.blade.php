@extends('layouts.public')
@section('title', 'Sign In | Trinetraa Optician')
@section('no_index', '1')
@section('content')
<div class="pp-page-header">
    <div class="pp-container">
        <div class="pp-page-header__content">
            <h1 class="pp-page-header__title">Sign In</h1>
            <p class="pp-page-header__breadcrumb"><a href="/">Home</a> / Account / Login</p>
        </div>
    </div>
</div>

<section class="pp-section">
    <div class="pp-container" style="max-width:440px">
        <div style="background:var(--pp-gray-50);border-radius:20px;padding:2rem">
            <h2 style="font-family:var(--pp-font-heading);font-weight:800;margin-bottom:1.5rem;text-align:center">Welcome Back</h2>
            <div id="loginError" style="background:rgba(220,38,38,0.15);color:#F87171;padding:1rem;border-radius:12px;margin-bottom:1rem;display:none"></div>
            <form id="loginForm" style="display:flex;flex-direction:column;gap:1rem">
                <input type="email" name="email" placeholder="Email address *" required style="padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit">
                <input type="password" name="password" placeholder="Password *" required style="padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit">
                <button type="submit" id="loginBtn" class="pp-btn-primary" style="justify-content:center">Sign In</button>
            </form>
            <p style="text-align:center;margin-top:1.5rem;color:var(--pp-gray-500);font-size:.9rem">
                Don't have an account? <a href="/account/register" style="color:var(--pp-gold);font-weight:600">Create one</a>
            </p>
        </div>
    </div>
</section>
@endsection
@push('scripts')
@verbatim
<script>
document.getElementById('loginForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    var btn = document.getElementById('loginBtn');
    var err = document.getElementById('loginError');
    err.style.display = 'none';
    btn.disabled = true; btn.textContent = 'Signing in…';
    try {
        await window.TrinetraaAuth.login(this.email.value, this.password.value);
        window.location.href = '/account';
    } catch (ex) {
        err.textContent = ex.message || 'Login failed.';
        err.style.display = 'block';
        btn.disabled = false; btn.textContent = 'Sign In';
    }
});
</script>
@endverbatim
@endpush
