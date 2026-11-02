@extends('layouts.admin')
@section('title', 'Automated Reminders')
@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">⏰ Automated Reminders</h2>

    <div class="alert alert-info mb-4">
        <strong>How sending works:</strong> emails go out through the Resend API (<code>RESEND_API_KEY</code>). WhatsApp uses the WhatsApp Cloud API
        (<code>WHATSAPP_TOKEN</code>, <code>WHATSAPP_PHONE_ID</code>) and SMS uses MSG91 (<code>MSG91_AUTH_KEY</code>, <code>MSG91_SENDER</code>, <code>MSG91_TEMPLATE_ID</code>).
        Until a provider is set up, "Needs manual send" counts customers you can message yourself from their profile.
        Offers, birthday and follow-up messages only go to customers who gave marketing consent. A daily run happens automatically at 10:00 once the server cron is set.
    </div>

    <div id="remResult"></div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">👁️ Lens Subscription Reminders</h5>
                    <p class="card-text text-muted">Notify customers whose lens subscription is due in 3 days</p>
                    <button class="btn btn-outline-secondary me-1" data-run="lens_subscription" data-dry="1">Preview</button>
                    <button class="btn btn-primary" data-run="lens_subscription">Run Now</button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">📅 Appointment Reminders</h5>
                    <p class="card-text text-muted">Remind confirmed appointments happening tomorrow</p>
                    <button class="btn btn-primary" data-run="appointment">Run Now</button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h5 class="card-title">🩺 Eye-test Follow-ups</h5>
                <p class="card-text text-muted">Customers whose last eye test was about 6 or 12 months ago (consented customers only)</p>
                <button class="btn btn-outline-secondary me-1" data-run="eye_followup" data-dry="1">Preview</button>
                <button class="btn btn-primary" data-run="eye_followup">Run Now</button>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h5 class="card-title">🎂 Birthday Wishes</h5>
                <p class="card-text text-muted">Customers whose birthday is today (needs a date of birth on the customer record)</p>
                <button class="btn btn-outline-secondary me-1" data-run="birthday" data-dry="1">Preview</button>
                <button class="btn btn-primary" data-run="birthday">Run Now</button>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h5 class="card-title">⭐ Google Review Requests</h5>
                <p class="card-text text-muted">Customers who bought 2–7 days ago and have not been asked for a review yet</p>
                <button class="btn btn-outline-secondary me-1" data-run="review_request" data-dry="1">Preview</button>
                <button class="btn btn-primary" data-run="review_request">Run Now</button>
            </div></div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Reminder Log</h5>
            <small class="text-muted"><span id="remTotal">0</span> entries</small>
        </div>
        <div class="card-body p-0">
            <div id="remLoading" class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            <div id="remTableWrap" class="table-responsive" style="display:none">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Recipient</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Sent At</th>
                        </tr>
                    </thead>
                    <tbody id="remBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    
    var logs = [];
    var total = 0;
    var running = null;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function renderLogs() {
        document.getElementById('remTotal').textContent = total;
        var body = document.getElementById('remBody');
        if (logs.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No reminders sent yet</td></tr>';
            return;
        }
        body.innerHTML = logs.map(function (l) {
            var errLine = l.error ? '<small class="d-block text-danger">' + esc(l.error) + '</small>' : '';
            return '<tr>' +
                '<td><span class="badge bg-secondary">' + esc(l.type) + '</span></td>' +
                '<td><small>' + esc(l.email) + '</small></td>' +
                '<td><small>' + esc(l.subject) + '</small></td>' +
                '<td><span class="badge bg-' + (l.status === 'sent' ? 'success' : 'danger') + '">' + esc(l.status) + '</span>' + errLine + '</td>' +
                '<td><small>' + new Date(l.sent_at).toLocaleString('en-IN') + '</small></td>' +
                '</tr>';
        }).join('');
    }

    function setButtons() {
        document.querySelectorAll('[data-run]').forEach(function (b) {
            var type = b.getAttribute('data-run'), dry = !!b.getAttribute('data-dry');
            var label = dry ? 'Preview' : 'Run Now';
            if (running === type + (dry ? ':dry' : '')) {
                b.disabled = true;
                b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Running…';
            } else {
                b.disabled = !!running;
                b.textContent = label;
            }
        });
    }

    function load() {
        document.getElementById('remLoading').style.display = '';
        document.getElementById('remTableWrap').style.display = 'none';
        fetch('/api/admin/reminders')
            .then(function (r) { return r.json(); })
            .then(function (json) {
                logs = json.logs || [];
                total = json.total || 0;
                document.getElementById('remLoading').style.display = 'none';
                document.getElementById('remTableWrap').style.display = '';
                renderLogs();
            });
    }

    function runReminder(type, dry) {
        if (!dry && !confirm('Send ' + type + ' reminders now?')) return;
        running = type + (dry ? ':dry' : '');
        document.getElementById('remResult').innerHTML = '';
        setButtons();
        fetch('/api/admin/reminders', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: type, dry_run: dry })
        }).then(function (r) { return r.json(); }).then(function (json) {
            running = null;
            setButtons();
            if (json.message && !json.result) {
                document.getElementById('remResult').innerHTML = '<div class="alert alert-danger mb-4">' + esc(json.message) + '</div>';
                return;
            }
            var result = json.result || {};
            var cls = result.failed > 0 ? 'warning' : 'success';
            var manual = result.manual ? ', Needs manual send ' + result.manual : '';
            document.getElementById('remResult').innerHTML = result.dry_run
                ? '<div class="alert alert-secondary mb-4"><strong>Preview — ' + esc(type) + ':</strong> ' + (result.total || 0) + ' customer(s) would be messaged. Nothing was sent.</div>'
                : '<div class="alert alert-' + cls + ' mb-4"><strong>' + esc(type) + ' reminders:</strong> Sent ' +
                  (result.sent || 0) + ', Failed ' + (result.failed || 0) + manual + ', Total eligible: ' + (result.total || 0) + '</div>';
            load();
        });
    }

    document.querySelectorAll('[data-run]').forEach(function (b) {
        b.addEventListener('click', function () { runReminder(b.getAttribute('data-run'), !!b.getAttribute('data-dry')); });
    });

    load();
})();
</script>
@endverbatim
@endpush
