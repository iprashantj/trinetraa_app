@extends('layouts.public')
@section('title', 'Create Account | Trinetraa Optician')
@section('no_index', '1')
@section('content')
<div class="pp-page-header">
    <div class="pp-container">
        <div class="pp-page-header__content">
            <h1 class="pp-page-header__title">Create Account</h1>
            <p class="pp-page-header__breadcrumb"><a href="/">Home</a> / Account / Register</p>
        </div>
    </div>
</div>

<section class="pp-section">
    <div class="pp-container" style="max-width:440px">
        <div style="background:var(--pp-gray-50);border-radius:20px;padding:2rem">
            <h2 style="font-family:var(--pp-font-heading);font-weight:800;margin-bottom:1.5rem;text-align:center">Join Trinetra</h2>
            <div id="regError" style="background:rgba(220,38,38,0.15);color:#F87171;padding:1rem;border-radius:12px;margin-bottom:1rem;display:none"></div>
            <form id="regForm" style="display:flex;flex-direction:column;gap:1rem">
                <input name="name" placeholder="Full name *" required style="padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit">
                <input type="email" name="email" placeholder="Email address *" required style="padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit">
                <input name="phone" placeholder="Phone number" style="padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit">
                <input type="password" name="password" placeholder="Password *" required minlength="6" style="padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit">
                <input type="password" name="confirmPassword" placeholder="Confirm password *" required style="padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit">
                <button type="submit" id="regBtn" class="pp-btn-primary" style="justify-content:center">Create Account</button>
            </form>
            <p style="text-align:center;margin-top:1.5rem;color:var(--pp-gray-500);font-size:.9rem">
                Already have an account? <a href="/account/login" style="color:var(--pp-gold);font-weight:600">Sign in</a>
            </p>
        </div>
    </div>
</section>
@endsection
@push('scripts')
@verbatim
<script>
document.getElementById('regForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    var err = document.getElementById('regError');
    var btn = document.getElementById('regBtn');
    err.style.display = 'none';
    if (this.password.value !== this.confirmPassword.value) {
        err.textContent = 'Passwords do not match.'; err.style.display = 'block'; return;
    }
    btn.disabled = true; btn.textContent = 'Creating account…';
    try {
        await window.TrinetraaAuth.register({ name: this.name.value, email: this.email.value, phone: this.phone.value, password: this.password.value });
        window.location.href = '/account';
    } catch (ex) {
        err.textContent = ex.message || 'Registration failed.';
        err.style.display = 'block';
        btn.disabled = false; btn.textContent = 'Create Account';
    }
});
</script>
@endverbatim
@endpush
