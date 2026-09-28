@extends('layouts.admin')
@section('title', 'Customer Loyalty')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">🏆 Customer Loyalty</h2>
        <small class="text-muted"><span id="loyTotal">0</span> accounts</small>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div style="font-size:28px">👥</div>
                    <h4 class="text-primary mb-0" id="statTotal">0</h4>
                    <small class="text-muted">Total Accounts</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div style="font-size:28px">🥇</div>
                    <h4 class="text-warning mb-0" id="statGold">0</h4>
                    <small class="text-muted">Gold Members</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-secondary">
                <div class="card-body text-center">
                    <div style="font-size:28px">🥈</div>
                    <h4 class="text-secondary mb-0" id="statSilver">0</h4>
                    <small class="text-muted">Silver Members</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <div style="font-size:28px">🥉</div>
                    <h4 class="text-danger mb-0" id="statBronze">0</h4>
                    <small class="text-muted">Bronze Members</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div id="loyLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            <div id="loyTableWrap" class="table-responsive" style="display:none">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Customer</th>
                            <th>Points</th>
                            <th>Tier</th>
                            <th>Total Earned</th>
                            <th>Recent Activity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="loyBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Adjust Points Modal -->
    <div id="loyModal" class="modal" style="background:rgba(0,0,0,0.5);display:none">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Points — <span id="modalCustomerName"></span></h5>
                    <button type="button" class="btn-close" id="modalClose"></button>
                </div>
                <form id="adjustForm">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            Current: <strong><span id="modalPoints">0</span> pts</strong> | Tier: <strong><span id="modalTier"></span></strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" id="formType">
                                <option value="credit">Credit (Add Points)</option>
                                <option value="debit">Debit (Deduct Points)</option>
                                <option value="redeem">Redeem</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Points</label>
                            <input class="form-control" type="number" min="1" required id="formPoints">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input class="form-control" type="text" id="formDescription" placeholder="e.g. Purchase bonus">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="modalCancel">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="formSubmit">Apply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var state = { accounts: [], total: 0, page: 1 };
    var current = null;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function tierBadge(tier) {
        var cls = tier === 'Gold' ? 'bg-warning text-dark' : tier === 'Silver' ? 'bg-secondary' : 'bg-danger';
        return '<span class="badge ' + cls + '">' + esc(tier) + '</span>';
    }

    function render() {
        document.getElementById('loyTotal').textContent = state.total;
        document.getElementById('statTotal').textContent = state.total;
        document.getElementById('statGold').textContent = state.accounts.filter(function (a) { return a.tier === 'Gold'; }).length;
        document.getElementById('statSilver').textContent = state.accounts.filter(function (a) { return a.tier === 'Silver'; }).length;
        document.getElementById('statBronze').textContent = state.accounts.filter(function (a) { return a.tier === 'Bronze'; }).length;

        var body = document.getElementById('loyBody');
        if (state.accounts.length === 0) {
            body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No loyalty accounts yet</td></tr>';
            return;
        }
        body.innerHTML = state.accounts.map(function (a) {
            var activity = (a.transactions || []).slice(0, 2).map(function (t) {
                return '<div class="small"><span class="' + (t.points > 0 ? 'text-success' : 'text-danger') + '">' +
                    (t.points > 0 ? '+' : '') + t.points + '</span> <span class="text-muted">' + esc(t.description || t.type) + '</span></div>';
            }).join('');
            var cust = a.customer || {};
            return '<tr>' +
                '<td><div class="fw-semibold">' + esc(cust.name) + '</div><small class="text-muted">' + esc(cust.email) + '</small></td>' +
                '<td><span class="fw-bold text-success">' + Number(a.points).toLocaleString() + '</span></td>' +
                '<td>' + tierBadge(a.tier) + '</td>' +
                '<td>' + Number(a.total_earned).toLocaleString() + '</td>' +
                '<td>' + activity + '</td>' +
                '<td><button class="btn btn-sm btn-outline-primary" data-adjust="' + a.id + '">Adjust Points</button></td>' +
                '</tr>';
        }).join('');

        body.querySelectorAll('[data-adjust]').forEach(function (b) {
            b.addEventListener('click', function () {
                var id = Number(b.getAttribute('data-adjust'));
                var acct = state.accounts.find(function (a) { return a.id === id; });
                if (acct) openModal(acct);
            });
        });
    }

    function load(page) {
        page = page || 1;
        document.getElementById('loyLoading').style.display = '';
        document.getElementById('loyTableWrap').style.display = 'none';
        fetch('/api/admin/loyalty?page=' + page + '&limit=20')
            .then(function (r) { return r.json(); })
            .then(function (json) {
                state = { accounts: json.accounts || [], total: json.total || 0, page: page };
                document.getElementById('loyLoading').style.display = 'none';
                document.getElementById('loyTableWrap').style.display = '';
                render();
            });
    }

    function openModal(account) {
        current = account;
        document.getElementById('modalCustomerName').textContent = (account.customer || {}).name || '';
        document.getElementById('modalPoints').textContent = account.points;
        document.getElementById('modalTier').textContent = account.tier;
        document.getElementById('formType').value = 'credit';
        document.getElementById('formPoints').value = '';
        document.getElementById('formDescription').value = '';
        document.getElementById('loyModal').style.display = 'block';
    }

    function closeModal() {
        current = null;
        document.getElementById('loyModal').style.display = 'none';
    }

    document.getElementById('modalClose').addEventListener('click', closeModal);
    document.getElementById('modalCancel').addEventListener('click', closeModal);

    document.getElementById('adjustForm').addEventListener('submit', function (e) {
        e.preventDefault();
        if (!current) return;
        var type = document.getElementById('formType').value;
        var pointsVal = Math.abs(Number(document.getElementById('formPoints').value));
        var pts = type === 'debit' ? -pointsVal : pointsVal;
        var btn = document.getElementById('formSubmit');
        btn.disabled = true; btn.textContent = 'Saving…';
        fetch('/api/admin/loyalty', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                customerId: current.customer_id,
                points: pts,
                type: type,
                description: document.getElementById('formDescription').value
            })
        }).then(function () {
            btn.disabled = false; btn.textContent = 'Apply';
            var page = state.page;
            closeModal();
            load(page);
        });
    });

    load(1);
})();
</script>
@endverbatim
@endpush
