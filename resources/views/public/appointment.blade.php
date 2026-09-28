@extends('layouts.public')
@section('title', 'Book an Appointment | Trinetraa Optician')
@section('content')
<div class="pp-page-header">
    <div class="pp-container">
        <div class="pp-page-header__content">
            <h1 class="pp-page-header__title">Book an Appointment</h1>
            <p class="pp-page-header__breadcrumb"><a href="/">Home</a> / Appointment</p>
        </div>
    </div>
</div>

<section class="pp-section">
    <div class="pp-container" style="max-width:560px">
        <div id="apptSuccess" style="text-align:center;padding:3rem 0;display:none">
            <div style="font-size:4rem;margin-bottom:1rem">✅</div>
            <h2 style="font-family:var(--pp-font-heading);font-weight:800;margin-bottom:1rem">Appointment Booked!</h2>
            <p id="apptSuccessMsg" style="color:var(--pp-gray-600);margin-bottom:2rem"></p>
            <a href="/" class="pp-btn-primary">Back to Home</a>
        </div>
        <div id="apptFormWrap">
            <div id="apptError" style="background:rgba(220,38,38,0.15);color:#F87171;padding:1rem;border-radius:12px;margin-bottom:1rem;display:none"></div>
            <form id="apptForm" style="display:flex;flex-direction:column;gap:1.25rem">
                <input name="name" placeholder="Your full name *" required style="padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit">
                <input type="email" name="email" placeholder="Email address *" required style="padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit">
                <input name="phone" placeholder="Phone number *" required style="padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit">
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:.5rem">Service *</label>
                    <select name="service" style="width:100%;padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit">
                        <option value="eye-test">Eye Test</option>
                        <option value="lens-fitting">Lens Fitting</option>
                        <option value="frame-selection">Frame Selection</option>
                        <option value="consultation">General Consultation</option>
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:.5rem">Date *</label>
                        <input type="date" name="appt_date" required id="apptDate" style="width:100%;padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit">
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:.5rem">Time *</label>
                        <select name="time_slot" required style="width:100%;padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit">
                            <option value="">Select time</option>
                            @foreach(['10:00 AM','11:00 AM','12:00 PM','2:00 PM','3:00 PM','4:00 PM','5:00 PM','6:00 PM'] as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <textarea name="notes" placeholder="Notes (optional)" rows="3" style="padding:.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:.95rem;font-family:inherit;resize:vertical"></textarea>
                <button type="submit" id="apptBtn" class="pp-btn-primary" style="justify-content:center">Book Appointment</button>
            </form>
        </div>
    </div>
</section>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var dateEl = document.getElementById('apptDate');
    if (dateEl) dateEl.min = new Date().toISOString().split('T')[0];
    var form = document.getElementById('apptForm');
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        var btn = document.getElementById('apptBtn'), err = document.getElementById('apptError');
        err.style.display = 'none'; btn.disabled = true; btn.textContent = 'Booking…';
        var payload = { name: form.name.value, email: form.email.value, phone: form.phone.value, service: form.service.value, appt_date: form.appt_date.value, time_slot: form.time_slot.value, notes: form.notes.value };
        try {
            var r = await fetch('/api/public/appointments', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            var d = await r.json();
            if (!r.ok) { err.textContent = d.message || 'Failed to book.'; err.style.display = 'block'; return; }
            document.getElementById('apptFormWrap').style.display = 'none';
            document.getElementById('apptSuccessMsg').textContent = d.message || '';
            document.getElementById('apptSuccess').style.display = 'block';
        } finally { btn.disabled = false; btn.textContent = 'Book Appointment'; }
    });
})();
</script>
@endverbatim
@endpush
