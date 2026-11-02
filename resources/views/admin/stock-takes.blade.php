@extends('layouts.admin')
@section('title', 'Stock Take')
@section('content')
<div id="erpAlerts"></div>
<style>
    .st-var-neg { color: #dc2626; font-weight: 700; } .st-var-pos { color: #0e9d4e; font-weight: 700; } .st-var-zero { color: var(--erp-muted); }
    .st-count { width: 100px; }
    .st-scanbox { background: #101a2e; border-radius: 12px; padding: 16px; color: #fff; }
    #stLast { min-height: 1.5rem; font-size: .85rem; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 mb-0">Stock Take</h1>
    <a href="/admin/stock-ledger" class="btn btn-outline-secondary btn-sm">Stock ledger</a>
</div>

{{-- Start --}}
<div class="card mb-3" id="stStart">
    <div class="card-header">Start a count</div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label fw-semibold">Branch *</label><select class="form-select" id="stBranch"></select></div>
            <div class="col-md-5"><label class="form-label fw-semibold">Notes</label><input class="form-control" id="stNotes" maxlength="2000" placeholder="e.g. Monthly count, showroom shelves"></div>
            <div class="col-md-3"><button type="button" class="btn btn-primary w-100" id="stBegin">Start stock take</button></div>
        </div>
        <div class="small text-muted mt-2">Every product that has stock at the branch is listed. Count what is on the shelves, then complete — differences are posted to the stock ledger as corrections with the reason “Stock take #n”.</div>
    </div>
</div>

{{-- Active count --}}
<div class="card mb-3" id="stWork" style="display:none">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span id="stTitle"></span>
        <div class="d-flex gap-2"><button class="btn btn-sm btn-outline-danger" id="stCancel">Cancel take</button><button class="btn btn-sm btn-outline-secondary" id="stClose" aria-label="Close">✕</button></div>
    </div>
    <div class="card-body">
        <div class="st-scanbox mb-3" id="stScanWrap">
            <div class="input-group">
                <input class="form-control" id="stScan" placeholder="Scan an item QR / barcode / model code (adds 1 to its count), then Enter" autocomplete="off">
                <button type="button" class="btn btn-light" id="stCam">Camera</button>
            </div>
            <div id="stLast" class="mt-2" style="opacity:.9"></div>
        </div>
        <div class="d-flex gap-3 flex-wrap mb-2 small">
            <span>Counted: <b id="stCounted">0</b> / <b id="stTotal">0</b></span>
            <span>Items with a difference: <b id="stDiffs">0</b></span>
            <span>Net difference: <b id="stNet">0</b></span>
            <label class="ms-auto"><input type="checkbox" id="stOnlyDiff"> Show only differences</label>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-3">
                <thead><tr><th>Product</th><th class="text-end">System qty</th><th style="width:130px">Counted</th><th class="text-end">Difference</th><th>Note</th></tr></thead>
                <tbody id="stLines"></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <label class="form-check mb-0"><input type="checkbox" class="form-check-input" id="stIgnore"> <span class="form-check-label">Leave uncounted items unchanged</span></label>
            <button type="button" class="btn btn-primary" id="stComplete">Complete &amp; apply differences</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Stock takes</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>#</th><th>Branch</th><th>Started</th><th>Completed</th><th class="text-end">Counted</th><th class="text-end">Differences</th><th class="text-end">Net</th><th>Status</th><th></th></tr></thead>
            <tbody id="stRows"><tr><td colspan="9" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var take = null, seenUnits = {}, saveTimer = {};
    var BADGE = { open: 'primary', completed: 'success', cancelled: 'secondary' };

    if (!ERP.canScan()) $('stCam').style.display = 'none';
    function varHtml(v) { return v === null || v === undefined ? '<span class="text-muted">—</span>' : '<span class="' + (v < 0 ? 'st-var-neg' : v > 0 ? 'st-var-pos' : 'st-var-zero') + '">' + (v > 0 ? '+' : '') + v + '</span>'; }

    function renderTake() {
        if (!take) { $('stWork').style.display = 'none'; return; }
        var open = take.status === 'open';
        $('stWork').style.display = '';
        $('stTitle').innerHTML = '<strong>Stock take #' + take.id + '</strong> · ' + ERP.esc(take.branch_name || '') + ' <span class="badge bg-' + (BADGE[take.status] || 'secondary') + '">' + take.status + '</span>';
        $('stScanWrap').style.display = open ? '' : 'none';
        $('stComplete').style.display = $('stCancel').style.display = $('stIgnore').parentNode.style.display = open ? '' : 'none';
        var only = $('stOnlyDiff').checked;
        var list = take.items.filter(function (r) { return !only || (r.variance !== null && r.variance !== 0); });
        $('stLines').innerHTML = list.map(function (r) {
            var sys = open ? r.system_now : r.system_qty;
            return '<tr><td>' + ERP.esc((r.brand ? r.brand + ' — ' : '') + r.item_name) + (r.model_number ? ' <span class="text-muted">(' + ERP.esc(r.model_number) + ')</span>' : '') + '</td>' +
                '<td class="text-end">' + sys + (open && r.system_now !== r.system_qty ? ' <span class="text-muted small" title="Was ' + r.system_qty + ' when the count started">*</span>' : '') + '</td>' +
                '<td>' + (open ? '<input type="number" min="0" class="form-control form-control-sm st-count" data-item="' + r.stock_item_id + '" value="' + (r.counted_qty === null ? '' : r.counted_qty) + '">' : (r.counted_qty === null ? '—' : r.counted_qty)) + '</td>' +
                '<td class="text-end" data-var="' + r.stock_item_id + '">' + varHtml(r.variance) + '</td>' +
                '<td class="text-muted small">' + ERP.esc(r.note || '') + '</td></tr>';
        }).join('') || '<tr><td colspan="5" class="text-center text-muted py-3">Nothing to show.</td></tr>';
        summary();
        $('stLines').querySelectorAll('.st-count').forEach(function (inp) {
            inp.addEventListener('change', function () { setCount(inp); });
            inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); inp.blur(); var n = inp.closest('tr').nextElementSibling; if (n && n.querySelector('.st-count')) n.querySelector('.st-count').focus(); } });
        });
    }
    function summary() {
        $('stCounted').textContent = take.items_counted; $('stTotal').textContent = take.items_total;
        $('stDiffs').textContent = take.items_with_variance; $('stNet').textContent = (take.variance_total > 0 ? '+' : '') + take.variance_total;
    }

    function setCount(inp) {
        var v = inp.value === '' ? null : Math.max(0, parseInt(inp.value, 10) || 0);
        ERP.api('/api/admin/stock-takes/' + take.id + '/items/' + inp.getAttribute('data-item'), { method: 'PUT', body: { counted_qty: v } })
            .then(function (j) { take = j.data; var only = $('stOnlyDiff').checked; if (only) renderTake(); else { var r = take.items.filter(function (x) { return String(x.stock_item_id) === inp.getAttribute('data-item'); })[0]; var cell = document.querySelector('[data-var="' + r.stock_item_id + '"]'); if (cell) cell.innerHTML = varHtml(r.variance); summary(); } })
            .catch(ERP.error);
    }

    function scan(code) {
        // a unit QR code is one physical piece: count each only once per session
        var key = code.toUpperCase();
        if (/^TR[A-Z0-9]{8}$/.test(key)) { if (seenUnits[key]) { $('stLast').textContent = '⚠ ' + key + ' was already scanned.'; return; } seenUnits[key] = 1; }
        ERP.api('/api/admin/stock-takes/' + take.id + '/scan', { method: 'POST', body: { code: code } }).then(function (j) {
            take = j.data.take;
            var r = take.items.filter(function (x) { return x.stock_item_id === j.data.item.id; })[0];
            $('stLast').textContent = '✓ ' + j.data.item.name + ' — counted ' + r.counted_qty + ' (system ' + r.system_now + ')';
            renderTake();
        }).catch(function (e) { delete seenUnits[key]; $('stLast').textContent = '✗ ' + e.message; });
    }
    ERP.bindScan($('stScan'), $('stCam'), scan);

    function open(id) {
        ERP.api('/api/admin/stock-takes/' + id).then(function (j) { take = j.data; seenUnits = {}; renderTake(); $('stWork').scrollIntoView({ behavior: 'smooth', block: 'start' }); if (take.status === 'open') $('stScan').focus(); }).catch(ERP.error);
    }

    function load() {
        ERP.api('/api/admin/stock-takes').then(function (j) {
            var rows = j.data || [];
            $('stRows').innerHTML = rows.length ? rows.map(function (r) {
                return '<tr><td>#' + r.id + '</td><td>' + ERP.esc(r.branch_name || '') + '</td><td>' + ERP.fmtDateTime(r.created_at) + '</td><td>' + (ERP.fmtDateTime(r.completed_at) || '—') + '</td>' +
                    '<td class="text-end">' + r.items_counted + ' / ' + r.items_total + '</td><td class="text-end">' + r.items_with_variance + '</td><td class="text-end">' + varHtml(r.variance_total) + '</td>' +
                    '<td><span class="badge bg-' + (BADGE[r.status] || 'secondary') + '">' + r.status + '</span></td><td class="text-end"><button class="btn btn-sm btn-outline-primary" data-open="' + r.id + '">' + (r.status === 'open' ? 'Continue' : 'View') + '</button></td></tr>';
            }).join('') : '<tr><td colspan="9" class="text-center py-4 text-muted">No stock takes yet.</td></tr>';
            $('stRows').querySelectorAll('[data-open]').forEach(function (b) { b.addEventListener('click', function () { open(b.getAttribute('data-open')); }); });
        }).catch(ERP.error);
    }

    $('stBegin').addEventListener('click', function () {
        $('stBegin').disabled = true;
        ERP.api('/api/admin/stock-takes', { method: 'POST', body: { branch_id: $('stBranch').value, notes: $('stNotes').value } })
            .then(function (j) { take = j.data; seenUnits = {}; ERP.notify('Stock take #' + take.id + ' started with ' + take.items_total + ' product(s).'); renderTake(); load(); $('stScan').focus(); })
            .catch(ERP.error).then(function () { $('stBegin').disabled = false; });
    });
    $('stOnlyDiff').addEventListener('change', function () { if (take) renderTake(); });
    $('stClose').addEventListener('click', function () { take = null; renderTake(); });
    $('stCancel').addEventListener('click', function () {
        if (!confirm('Cancel this stock take? Counts entered so far are discarded and no stock changes.')) return;
        ERP.api('/api/admin/stock-takes/' + take.id + '/cancel', { method: 'POST' }).then(function () { ERP.notify('Stock take cancelled.'); take = null; renderTake(); load(); }).catch(ERP.error);
    });
    $('stComplete').addEventListener('click', function () {
        var uncounted = take.items_total - take.items_counted, ignore = $('stIgnore').checked;
        if (uncounted && !ignore) { ERP.notify(uncounted + ' item(s) are not counted yet. Count them, or tick “Leave uncounted items unchanged”.', 'warning'); return; }
        if (!confirm('Apply ' + take.items_with_variance + ' difference(s) (net ' + take.variance_total + ') to ' + (take.branch_name || 'the branch') + ' stock? This posts corrections to the ledger.')) return;
        $('stComplete').disabled = true;
        ERP.api('/api/admin/stock-takes/' + take.id + '/complete', { method: 'POST', body: { ignore_uncounted: ignore } })
            .then(function (j) { ERP.notify('Stock take completed — ' + j.meta.items_adjusted + ' item(s) adjusted.'); take = j.data; renderTake(); load(); })
            .catch(ERP.error).then(function () { $('stComplete').disabled = false; });
    });

    ERP.fillBranchSelect($('stBranch'), { all: false }).then(function () {
        load();
        return ERP.api('/api/admin/stock-takes?status=open');
    }).then(function (j) { if (j.data && j.data.length) open(j.data[0].id); }).catch(ERP.error);
})();
</script>
@endverbatim
@endpush
