@extends('layouts.admin')
@section('title', 'Automated Reminders')
@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">⏰ Automated Reminders</h2>

    <!-- SMTP Warning -->
    <div class="alert alert-warning mb-4">
        <strong>⚙️ Setup Required:</strong> Add SMTP credentials to <code>.env</code> to enable email sending:
        <code class="d-block mt-1">SMTP_HOST=smtp.gmail.com &nbsp; SMTP_PORT=587 &nbsp; SMTP_USER=you@gmail.com &nbsp; SMTP_PASS=app-password</code>
    </div>

    <div id="remResult"></div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">👁️ Lens Subscription Reminders</h5>
                    <p class="card-text text-muted">Notify customers whose lens subscription is due in 3 days</p>
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
                            <th>Email</th>
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
    var LABELS = { lens_subscription: 'Lens Subscription Reminders', appointment: 'Appointment Reminders' };
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
            var type = b.getAttribute('data-run');
            if (running === type) {
                b.disabled = true;
                b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Running…';
            } else {
                b.disabled = false;
                b.textContent = 'Run Now';
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

    function runReminder(type) {
        if (!confirm('Send ' + type + ' reminders now?')) return;
        running = type;
        document.getElementById('remResult').innerHTML = '';
        setButtons();
        fetch('/api/admin/reminders', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: type })
        }).then(function (r) { return r.json(); }).then(function (json) {
            running = null;
            setButtons();
            var result = json.result || {};
            var cls = result.failed > 0 ? 'warning' : 'success';
            document.getElementById('remResult').innerHTML =
                '<div class="alert alert-' + cls + ' mb-4"><strong>' + esc(type) + ' reminders:</strong> Sent ' +
                (result.sent || 0) + ', Failed ' + (result.failed || 0) + ', Total eligible: ' + (result.total || 0) + '</div>';
            load();
        });
    }

    document.querySelectorAll('[data-run]').forEach(function (b) {
        b.addEventListener('click', function () { runReminder(b.getAttribute('data-run')); });
    });

    load();
})();
</script>
@endverbatim
@endpush
