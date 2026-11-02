@extends('layouts.admin')
@section('title', 'Returns and Exchanges')
@section('content')
<div id="erpAlerts"></div>
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Returns &amp; Exchanges</h1></div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">New return / exchange</div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-6 position-relative">
                <label class="form-label fw-semibold">Find the bill</label>
                <input class="form-control" id="rtSearch" placeholder="Bill number, customer name or phone" autocomplete="off">
                <div id="rtSuggest" class="rt-suggest" style="display:none"></div>
            </div>
        </div>
        <div id="rtBill" style="display:none">
            <div class="mb-2"><strong id="rtBillNo"></strong> <span class="text-muted" id="rtBillMeta"></span></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Item</th><th class="text-end">Sold</th><th class="text-end">Price ₹</th><th style="width:120px">Return qty</th><th style="width:110px">Restock</th></tr></thead>
                    <tbody id="rtLines"></tbody>
                </table>
            </div>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label fw-semibold">Type</label>
                    <select class="form-select" id="rtType"><option value="return">Return</option><option value="exchange">Exchange</option></select></div>
                <div class="col-md-5"><label class="form-label fw-semibold">Reason *</label><input class="form-control" id="rtReason" maxlength="255" placeholder="e.g. Wrong size, defective lens"></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Refund ₹</label><input type="number" step="0.01" min="0" class="form-control" id="rtRefund" value="0"></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Refund via</label>
                    <select class="form-select" id="rtMethod"><option value="">—</option><option value="cash">Cash</option><option value="upi">UPI</option><option value="card">Card</option><option value="online">Online</option><option value="credit_note">Credit note</option></select></div>
            </div>
            <div class="alert alert-info py-2 mt-3" id="rtExHint" style="display:none">
                For an exchange, record the item coming back here, then bill the replacement item as a new sale.
                <a class="btn btn-sm btn-outline-primary ms-2" href="/admin/billing">Open billing for the replacement</a>
            </div>
            <div class="mt-3 d-flex gap-2"><button class="btn btn-primary" id="rtSubmit">Record return</button><button class="btn btn-outline-secondary" id="rtClear" type="button">Clear</button></div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">Recent returns &amp; exchanges</div>
    <div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Return #</th><th>Bill</th><th>Type</th><th>Reason</th><th class="text-end">Refund ₹</th><th>Date</th></tr></thead>
            <tbody id="rtRows"><tr><td colspan="6" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
        </table>
    </div></div>
</div>
<style>
    .rt-suggest { position:absolute; z-index:30; left:12px; right:12px; top:100%; background:#fff; border:1px solid #e8eaf0; border-radius:8px; box-shadow:0 8px 24px rgba(27,36,54,.12); max-height:260px; overflow:auto; }
    .rt-suggest div { padding:.5rem .8rem; cursor:pointer; font-size:.85rem; display:flex; justify-content:space-between; gap:1rem; }
    .rt-suggest div:hover { background:#f3f4f9; }
</style>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var bill = null, found = [], timer;
    var search = document.getElementById('rtSearch'), sug = document.getElementById('rtSuggest');

    function loadList() {
        ERP.api('/api/admin/bill-returns').then(function (j) {
            var rows = j.data || [], tb = document.getElementById('rtRows');
            tb.innerHTML = rows.length ? rows.map(function (r) {
                return '<tr><td><code>' + ERP.esc(r.return_number) + '</code></td><td>' + ERP.esc(r.bill_number) + '</td>' +
                    '<td><span class="badge bg-' + (r.type === 'exchange' ? 'info' : 'secondary') + '">' + ERP.esc(r.type) + '</span></td>' +
                    '<td>' + ERP.esc(r.reason) + '</td><td class="text-end">' + ERP.inr(r.refund_amount) + '</td><td>' + ERP.fmtDateTime(r.created_at) + '</td></tr>';
            }).join('') : '<tr><td colspan="6" class="text-center py-4 text-muted">No returns yet.</td></tr>';
        }).catch(ERP.error);
    }

    function pick(b) {
        bill = b; sug.style.display = 'none'; search.value = b.bill_number;
        document.getElementById('rtBill').style.display = '';
        document.getElementById('rtBillNo').textContent = b.bill_number;
        document.getElementById('rtBillMeta').textContent = '· ' + (b.customer_name || 'Walk-in') + ' · ' + ERP.fmtDate(b.bill_date) + ' · ' + ERP.inr(b.total) + (b.branch_name ? ' · ' + b.branch_name : '');
        document.getElementById('rtLines').innerHTML = (b.items || []).map(function (it) {
            return '<tr><td>' + ERP.esc(it.brand_name + (it.model_number ? ' · ' + it.model_number : '')) + '</td>' +
                '<td class="text-end">' + it.quantity + '</td><td class="text-end">' + ERP.inr(it.price - it.discount) + '</td>' +
                '<td><input type="number" min="0" max="' + it.quantity + '" value="0" class="form-control form-control-sm" data-item="' + it.id + '"></td>' +
                '<td>' + (it.stock_item_id ? '<div class="form-check"><input class="form-check-input" type="checkbox" checked data-restock="' + it.id + '"></div>' : '<span class="text-muted small">not tracked</span>') + '</td></tr>';
        }).join('');
        // default refund = value of returned lines
        document.getElementById('rtLines').querySelectorAll('[data-item]').forEach(function (i) { i.addEventListener('input', autoRefund); });
        autoRefund();
    }
    function autoRefund() {
        if (!bill) return;
        var sum = 0;
        document.querySelectorAll('#rtLines [data-item]').forEach(function (i) {
            var it = bill.items.filter(function (x) { return String(x.id) === i.getAttribute('data-item'); })[0];
            sum += (parseInt(i.value, 10) || 0) * (it.price - it.discount);
        });
        document.getElementById('rtRefund').value = sum.toFixed(2);
    }

    search.addEventListener('input', function () {
        clearTimeout(timer);
        var q = search.value.trim();
        if (q.length < 2) { sug.style.display = 'none'; return; }
        timer = setTimeout(function () {
            ERP.api('/api/admin/frame-bills?per_page=8&search=' + encodeURIComponent(q)).then(function (j) {
                found = j.data || [];
                sug.innerHTML = found.length ? found.map(function (b, i) {
                    return '<div data-i="' + i + '"><span>' + ERP.esc(b.bill_number + ' · ' + (b.customer_name || 'Walk-in')) + '</span><span class="text-muted">' + ERP.inr(b.total) + '</span></div>';
                }).join('') : '<div class="text-muted">No bills found</div>';
                sug.style.display = '';
                sug.querySelectorAll('[data-i]').forEach(function (d) { d.addEventListener('click', function () { pick(found[+d.getAttribute('data-i')]); }); });
            }).catch(ERP.error);
        }, 250);
    });
    document.addEventListener('click', function (e) { if (!sug.contains(e.target) && e.target !== search) sug.style.display = 'none'; });
    document.getElementById('rtType').addEventListener('change', function () { document.getElementById('rtExHint').style.display = this.value === 'exchange' ? '' : 'none'; });

    function clear() { bill = null; search.value = ''; document.getElementById('rtBill').style.display = 'none'; document.getElementById('rtReason').value = ''; }
    document.getElementById('rtClear').addEventListener('click', clear);

    document.getElementById('rtSubmit').addEventListener('click', function () {
        var reason = document.getElementById('rtReason').value.trim();
        if (reason.length < 3) { ERP.notify('A reason is required.', 'warning'); return; }
        var items = [];
        document.querySelectorAll('#rtLines [data-item]').forEach(function (i) {
            var q = parseInt(i.value, 10) || 0;
            if (q > 0) {
                var id = i.getAttribute('data-item'), rs = document.querySelector('[data-restock="' + id + '"]');
                items.push({ frame_bill_item_id: +id, quantity: q, restock: rs ? rs.checked : false });
            }
        });
        if (!items.length) { ERP.notify('Enter a return quantity for at least one item.', 'warning'); return; }
        var btn = this; btn.disabled = true;
        ERP.api('/api/admin/bill-returns', { method: 'POST', body: {
            frame_bill_id: bill.id, type: document.getElementById('rtType').value, reason: reason,
            refund_amount: parseFloat(document.getElementById('rtRefund').value) || 0,
            refund_method: document.getElementById('rtMethod').value || null, items: items
        } }).then(function (j) {
            ERP.notify('Recorded ' + j.data.return_number + '.');
            var wasEx = document.getElementById('rtType').value === 'exchange';
            clear(); loadList();
            if (wasEx) ERP.notify('Now bill the replacement item from the billing screen.', 'info');
        }).catch(ERP.error).then(function () { btn.disabled = false; });
    });

    var pre = new URLSearchParams(location.search).get('bill');
    if (pre) {
        // Load the preselected bill by searching the list, then matching its id.
        ERP.api('/api/admin/frame-bills?per_page=500').then(function (j) {
            var b = (j.data || []).filter(function (x) { return String(x.id) === pre; })[0];
            if (b) pick(b); else ERP.notify('Bill not found.', 'warning');
        }).catch(ERP.error);
    }
    loadList();
})();
</script>
@endverbatim
@endpush
