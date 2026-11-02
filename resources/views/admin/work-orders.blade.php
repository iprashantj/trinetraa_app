@extends('layouts.admin')
@section('title', 'Work Orders')
@section('content')
<div id="erpAlerts"></div>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 mb-0">Work orders <span class="text-muted fs-6 fw-normal">repairs, lens orders &amp; delivery</span></h1>
    <div id="woChips" class="d-flex gap-2 flex-wrap"></div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">New work order</div>
    <div class="card-body">
        <form id="woForm" class="row g-3">
            <div class="col-md-3"><label class="form-label fw-semibold">Customer *</label><input class="form-control" id="woName" list="woCustList" required autocomplete="off" placeholder="Type a name or pick a customer"><datalist id="woCustList"></datalist></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Mobile</label><input class="form-control" id="woPhone" placeholder="10-digit mobile"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Branch</label><select class="form-select" id="woBranch"></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Expected on</label><input type="date" class="form-control" id="woDue"></div>
            <div class="col-md-9"><label class="form-label fw-semibold">What is being made / repaired</label><input class="form-control" id="woDesc" maxlength="500" placeholder="e.g. Progressive lenses, Ray-Ban frame fitting"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Bill no. (optional)</label><input class="form-control" id="woBill" placeholder="e.g. FB-2026…"></div>
            <div class="col-12 d-flex gap-2"><button type="submit" class="btn btn-primary" id="woSubmit">Create order</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Orders <span class="text-muted small fw-normal" id="woCount"></span></span>
        <div class="d-flex gap-2">
            <input type="search" class="form-control form-control-sm" id="woSearch" placeholder="Search order, name, phone…" style="width:240px">
            <select class="form-select form-select-sm" id="woStatus" style="width:auto">
                <option value="">All statuses</option><option value="ordered">Ordered</option><option value="in_fitting">In fitting</option>
                <option value="ready">Ready</option><option value="delivered">Delivered</option><option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Order #</th><th>Customer</th><th>Details</th><th>Branch</th><th>Expected</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody id="woRows"><tr><td colspan="7" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
        </table>
    </div></div>
</div>
<style>
    .wo-chip { padding:.3rem .8rem; border-radius:999px; font-size:.75rem; font-weight:700; border:0; cursor:pointer; }
</style>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var STATUS = { ordered: ['Ordered', 'secondary'], in_fitting: ['In fitting', 'info'], ready: ['Ready', 'success'], delivered: ['Delivered', 'primary'], cancelled: ['Cancelled', 'danger'] };
    var NEXT = { ordered: ['in_fitting', 'Start fitting'], in_fitting: ['ready', 'Mark ready'], ready: ['delivered', 'Mark delivered'] };
    var rows = [], customers = [];
    var branchSel = document.getElementById('woBranch'), statusSel = document.getElementById('woStatus');

    function badge(s) { var x = STATUS[s] || [s, 'secondary']; return '<span class="badge bg-' + x[1] + '">' + x[0] + '</span>'; }

    function chips(counts) {
        document.getElementById('woChips').innerHTML = Object.keys(STATUS).filter(function (k) { return counts[k]; }).map(function (k) {
            return '<button type="button" class="wo-chip bg-' + STATUS[k][1] + ' text-white" data-chip="' + k + '">' + STATUS[k][0] + ' · ' + counts[k] + '</button>';
        }).join('');
        document.querySelectorAll('[data-chip]').forEach(function (b) {
            b.addEventListener('click', function () { statusSel.value = b.getAttribute('data-chip'); load(); });
        });
    }

    function render() {
        var tb = document.getElementById('woRows');
        if (!rows.length) { tb.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No work orders.</td></tr>'; return; }
        tb.innerHTML = rows.map(function (r) {
            var next = NEXT[r.status], done = r.status === 'delivered' || r.status === 'cancelled';
            var late = !done && r.expected_date && r.expected_date < ERP.today();
            return '<tr><td><code>' + ERP.esc(r.order_number) + '</code></td>' +
                '<td>' + ERP.esc(r.customer_name || '—') + '<div class="small text-muted">' + ERP.esc(r.customer_contact || '') + '</div></td>' +
                '<td>' + ERP.esc(r.description || '—') + '</td>' +
                '<td>' + ERP.esc(r.branch_name || '—') + '</td>' +
                '<td' + (late ? ' class="text-danger fw-semibold"' : '') + '>' + (r.expected_date ? new Date(r.expected_date + 'T00:00:00').toLocaleDateString('en-IN') : '—') + (late ? ' (late)' : '') + '</td>' +
                '<td>' + badge(r.status) + (r.notified_at ? '<div class="small text-muted">customer notified</div>' : '') + '</td>' +
                '<td class="text-nowrap">' +
                    (next ? '<button class="btn btn-sm btn-outline-primary me-1" data-go="' + r.id + '" data-to="' + next[0] + '">' + next[1] + '</button>' : '') +
                    (r.status === 'ready' ? '<button class="btn btn-sm btn-outline-success me-1" data-renotify="' + r.id + '">Notify again</button>' : '') +
                    (!done ? '<button class="btn btn-sm btn-outline-secondary me-1" data-go="' + r.id + '" data-to="cancelled">Cancel</button>' : '') +
                    '<button class="btn btn-sm btn-outline-danger" data-del="' + r.id + '">Delete</button>' +
                '</td></tr>';
        }).join('');
        tb.querySelectorAll('[data-go]').forEach(function (b) {
            b.addEventListener('click', function () { setStatus(b.getAttribute('data-go'), b.getAttribute('data-to')); });
        });
        tb.querySelectorAll('[data-renotify]').forEach(function (b) {
            b.addEventListener('click', function () { renotify(b.getAttribute('data-renotify')); });
        });
        tb.querySelectorAll('[data-del]').forEach(function (b) {
            b.addEventListener('click', function () {
                if (!window.confirm('Delete this work order?')) return;
                ERP.api('/api/admin/work-orders/' + b.getAttribute('data-del'), { method: 'DELETE' }).then(load).catch(ERP.error);
            });
        });
    }

    /** Result of an automatic customer message; when no provider is set up, offer the WhatsApp / SMS links. */
    function showNotify(n) {
        if (!n) return;
        if (n.sent) { ERP.notify('Customer notified by ' + n.channel + '.', 'success'); return; }
        var host = document.getElementById('erpAlerts'), el = document.createElement('div');
        el.className = 'alert alert-warning d-flex justify-content-between align-items-center py-2';
        var links = (n.whatsapp_url ? '<a class="btn btn-sm btn-success ms-2" target="_blank" rel="noopener" href="' + ERP.esc(n.whatsapp_url) + '">Open WhatsApp</a>' : '') +
                    (n.sms_url ? '<a class="btn btn-sm btn-outline-secondary ms-2" href="' + ERP.esc(n.sms_url) + '">SMS</a>' : '');
        el.innerHTML = '<span>Automatic messaging is not set up' + (links ? ' — send the "ready" message yourself:' : ' and this order has no valid mobile number.') + '</span><span>' + links + '</span>';
        host.prepend(el);
    }

    function setStatus(id, to) {
        if (to === 'cancelled' && !window.confirm('Cancel this order?')) return;
        ERP.api('/api/admin/work-orders/' + id + '/status', { method: 'POST', body: { status: to } })
            .then(function (j) { showNotify(j.meta && j.meta.notify); return load(); }).catch(ERP.error);
    }
    function renotify(id) {
        ERP.api('/api/admin/work-orders/' + id + '/renotify', { method: 'POST' })
            .then(function (j) { showNotify(j.meta && j.meta.notify); return load(); }).catch(ERP.error);
    }

    function load() {
        var qs = '?per_page=100';
        if (ERP.currentBranch()) qs += '&branch_id=' + ERP.currentBranch();
        if (statusSel.value) qs += '&status=' + statusSel.value;
        var q = document.getElementById('woSearch').value.trim();
        if (q) qs += '&search=' + encodeURIComponent(q);
        return ERP.api('/api/admin/work-orders' + qs).then(function (j) {
            rows = j.data || [];
            document.getElementById('woCount').textContent = '(' + (j.meta ? j.meta.total : rows.length) + ')';
            chips((j.meta && j.meta.counts) || {});
            render();
        }).catch(ERP.error);
    }

    // Typing a customer's name exactly as listed fills their mobile number.
    document.getElementById('woName').addEventListener('input', function (e) {
        var c = customers.filter(function (x) { return x.name === e.target.value; })[0];
        if (c && c.phone && !document.getElementById('woPhone').value) document.getElementById('woPhone').value = c.phone;
        document.getElementById('woName').dataset.cid = c ? c.id : '';
    });

    document.getElementById('woForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var billNo = document.getElementById('woBill').value.trim();
        var go = function (billId) {
            var cid = document.getElementById('woName').dataset.cid;
            return ERP.api('/api/admin/work-orders', { method: 'POST', body: {
                customer_id: cid ? Number(cid) : null, customer_name: document.getElementById('woName').value.trim(),
                customer_contact: document.getElementById('woPhone').value.trim() || null, branch_id: branchSel.value ? Number(branchSel.value) : null,
                expected_date: document.getElementById('woDue').value || null, description: document.getElementById('woDesc').value.trim() || null,
                frame_bill_id: billId || null,
            } }).then(function (j) {
                ERP.notify('Work order ' + j.data.order_number + ' created.');
                document.getElementById('woForm').reset(); document.getElementById('woName').dataset.cid = '';
                return load();
            });
        };
        var p = billNo
            ? ERP.api('/api/admin/frame-bills?per_page=5&search=' + encodeURIComponent(billNo)).then(function (j) {
                var b = (j.data || []).filter(function (x) { return x.bill_number === billNo; })[0];
                if (!b) throw new Error('No bill with number ' + billNo + '.');
                return b.id;
            })
            : Promise.resolve(null);
        p.then(go).catch(ERP.error);
    });

    document.getElementById('woSearch').addEventListener('input', (function () { var t; return function () { clearTimeout(t); t = setTimeout(load, 300); }; })());
    statusSel.addEventListener('change', load);

    ERP.fillBranchSelect(branchSel, { all: false }).then(load);
    ERP.api('/api/admin/customers/lookup').then(function (j) {
        customers = j.data || [];
        document.getElementById('woCustList').innerHTML = customers.map(function (c) { return '<option value="' + ERP.esc(c.name) + '">' + ERP.esc(c.phone || '') + '</option>'; }).join('');
    }).catch(function () {});
})();
</script>
@endverbatim
@endpush
