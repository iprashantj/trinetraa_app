@extends('layouts.admin')
@section('title', 'Push Notifications')
@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">🔔 Push Notifications</h2>

    <div class="alert alert-info mb-4">
        <strong>Setup Required:</strong> Generate VAPID keys with <code>npx web-push generate-vapid-keys</code> and add to <code>.env</code>:
        <code class="d-block mt-1">VAPID_PUBLIC_KEY=... &nbsp; VAPID_PRIVATE_KEY=... &nbsp; VAPID_EMAIL=mailto:admin@trinetraa.com</code>
        Also update <code>public/sw.js</code> to handle push events.
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size:40px">📱</div>
                    <h2 class="text-primary mb-0" id="pushCount">–</h2>
                    <small class="text-muted">Push Subscribers</small>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0">Send Push Notification</h5></div>
                <div class="card-body">
                    <div id="pushResult"></div>
                    <div id="pushError"></div>
                    <form id="pushForm">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input class="form-control" required id="pushTitle" placeholder="e.g. New Arrivals!">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message *</label>
                            <textarea class="form-control" rows="3" required id="pushBody" placeholder="Short notification text"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link (optional)</label>
                            <input class="form-control" type="url" id="pushUrl" placeholder="https://...">
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="pushSubmit" disabled>Send to 0 subscribers</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var count = null;
    var sending = false;

    function updateSubmit() {
        var btn = document.getElementById('pushSubmit');
        if (sending) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending…';
        } else {
            btn.disabled = !count;
            btn.textContent = 'Send to ' + (count == null ? 0 : count) + ' subscribers';
        }
    }

    fetch('/api/admin/push-notify')
        .then(function (r) { return r.json(); })
        .then(function (d) {
            count = d.subscriberCount;
            document.getElementById('pushCount').textContent = count == null ? '–' : count;
            updateSubmit();
        });

    document.getElementById('pushForm').addEventListener('submit', function (e) {
        e.preventDefault();
        sending = true;
        document.getElementById('pushResult').innerHTML = '';
        document.getElementById('pushError').innerHTML = '';
        updateSubmit();
        var payload = {
            title: document.getElementById('pushTitle').value,
            body: document.getElementById('pushBody').value,
            url: document.getElementById('pushUrl').value
        };
        fetch('/api/admin/push-notify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (res) {
            return res.json().then(function (data) { return { ok: res.ok, data: data }; });
        }).then(function (r) {
            sending = false;
            updateSubmit();
            if (r.ok) {
                var d = r.data;
                document.getElementById('pushResult').innerHTML =
                    '<div class="alert alert-success">Sent ' + d.sent + ' / ' + d.total +
                    ' | Failed: ' + d.failed + ' | Removed stale: ' + d.removed + '</div>';
            } else {
                document.getElementById('pushError').innerHTML =
                    '<div class="alert alert-danger">' + (r.data.message || 'Send failed') + '</div>';
            }
        });
    });
})();
</script>
@endverbatim
@endpush
