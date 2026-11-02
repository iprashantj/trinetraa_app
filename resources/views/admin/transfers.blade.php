@extends('layouts.admin')
@section('title', 'Stock Transfers')
@section('content')
<div id="erpAlerts"></div>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 mb-0">Stock transfer</h1>
    <div id="tfChips" class="d-flex gap-2 flex-wrap"></div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-4"><label class="form-label fw-semibold">From</label><select class="form-select" id="tfFrom"></select></div>
            <div class="col-md-auto text-center pb-2 fs-4" style="color:var(--erp-primary)">→</div>
            <div class="col-md-4"><label class="form-label fw-semibold">To</label><select class="form-select" id="tfTo"></select></div>
        </div>

        <label class="form-label fw-semibold">Add items</label>
        <div class="row g-2 mb-2">
            <div class="col-md-6 position-relative">
                <input class="form-control" id="tfSearch" placeholder="Scan or type code / name, press Enter" autocomplete="off">
                <div id="tfSuggest" class="tf-suggest" style="display:none"></div>
            </div>
            <div class="col-auto"><button type="button" class="btn btn-outline-secondary" id="tfCam">📷 Scan</button></div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-3">
                <thead><tr><th>Code</th><th>Brand / Model</th><th class="text-end">At source</th><th style="width:140px">Transfer</th><th style="width:60px"></th></tr></thead>
                <tbody id="tfLines"><tr><td colspan="5" class="text-center text-muted py-3">No items added yet.</td></tr></tbody>
            </table>
        </div>

        <div class="alert alert-success py-2 mb-3" id="tfNote">
            <strong>No rescanning needed.</strong> Item IDs stay the same. Stock reduces in <span id="tfNoteFrom">the source branch</span> and increases in <span id="tfNoteTo">the destination branch</span> once the transfer is confirmed.
        </div>
        <textarea class="form-control mb-3" id="tfNotes" rows="1" placeholder="Notes (optional)"></textarea>
        <div id="tfDup" class="alert alert-warning" style="display:none"></div>
        <div class="d-flex gap-2 justify-content-end align-items-center">
            <span class="text-muted me-auto" id="tfSummary"></span>
            <button type="button" class="btn btn-outline-secondary" id="tfDraft">Save as draft</button>
            <button type="button" class="btn btn-primary" id="tfDispatch">Create &amp; dispatch</button>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Transfers</span>
        <select class="form-select form-select-sm" id="tfStatus" style="width:auto">
            <option value="">All statuses</option><option value="draft">Draft</option><option value="in_transit">In transit</option>
            <option value="received">Received</option><option value="cancelled">Cancelled</option>
        </select>
    </div>
    <div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Transfer #</th><th>From → To</th><th>Items</th><th>Status</th><th>Dates</th><th>Actions</th></tr></thead>
            <tbody id="tfRows"><tr><td colspan="6" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
        </table>
    </div></div>
</div>
<style>
    .tf-suggest { position:absolute; z-index:30; left:0; right:0; top:100%; background:#fff; border:1px solid var(--erp-border,#e8eaf0); border-radius:8px; box-shadow:0 8px 24px rgba(27,36,54,.12); max-height:260px; overflow:auto; }
    .tf-suggest div { padding:.5rem .8rem; cursor:pointer; font-size:.85rem; display:flex; justify-content:space-between; gap:1rem; }
    .tf-suggest div:hover { background:#f3f4f9; }
    .tf-chip { padding:.3rem .8rem; border-radius:999px; font-size:.75rem; font-weight:700; cursor:pointer; border:0; }
</style>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var from = document.getElementById('tfFrom'), to = document.getElementById('tfTo');
    var lines = []; // {id, name, brand, model_number, barcode, avail, qty}
    var catalog = [], pendingPayload = null;

    var STATUS = { draft: ['Draft', 'secondary'], in_transit: ['In transit', 'warning'], received: ['Received', 'success'], cancelled: ['Cancelled', 'danger'] };
    function badge(s) { var x = STATUS[s] || [s, 'secondary']; return '<span class="badge bg-' + x[1] + '">' + x[0] + '</span>'; }
    function branchName(sel) { var o = sel.options[sel.selectedIndex]; return o ? o.text : ''; }

    function updateNote() {
        document.getElementById('tfNoteFrom').textContent = branchName(from) || 'the source branch';
        document.getElementById('tfNoteTo').textContent = branchName(to) || 'the destination branch';
    }

    function loadCatalog() {
        if (!from.value) return Promise.resolve();
        return ERP.api('/api/admin/stock-items/lookup?branch_id=' + from.value).then(function (j) { catalog = j.data || []; });
    }

    function renderLines() {
        var tb = document.getElementById('tfLines');
        if (!lines.length) { tb.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No items added yet.</td></tr>'; }
        else tb.innerHTML = lines.map(function (l, i) {
            return '<tr><td><code>' + ERP.esc(l.barcode || l.model_number || ('#' + l.id)) + '</code></td>' +
                '<td>' + ERP.esc((l.brand ? l.brand + ' · ' : '') + l.name) + '</td>' +
                '<td class="text-end">' + l.avail + '</td>' +
                '<td><input type="number" min="1" max="' + l.avail + '" class="form-control form-control-sm" data-q="' + i + '" value="' + l.qty + '"></td>' +
                '<td><button class="btn btn-sm btn-outline-danger" data-rm="' + i + '">✕</button></td></tr>';
        }).join('');
        tb.querySelectorAll('[data-q]').forEach(function (inp) {
            inp.addEventListener('input', function () {
                var l = lines[+inp.getAttribute('data-q')], v = parseInt(inp.value, 10) || 0;
                if (v > l.avail) { v = l.avail; inp.value = v; }
                l.qty = v; summary();
            });
        });
        tb.querySelectorAll('[data-rm]').forEach(function (b) { b.addEventListener('click', function () { lines.splice(+b.getAttribute('data-rm'), 1); renderLines(); }); });
        summary();
    }
    function summary() {
        var n = lines.reduce(function (s, l) { return s + (l.qty || 0); }, 0);
        document.getElementById('tfSummary').textContent = lines.length ? lines.length + ' item(s), ' + n + ' unit(s) to transfer' : '';
    }

    function addItem(it) {
        var avail = it.branch_quantity != null ? it.branch_quantity : 0;
        if (avail < 1) { ERP.notify((it.name || 'Item') + ' has no stock at ' + branchName(from) + '.', 'warning'); return; }
        var ex = lines.filter(function (l) { return l.id === it.id; })[0];
        if (ex) { ex.qty = Math.min(ex.avail, ex.qty + 1); }
        else lines.push({ id: it.id, name: it.name, brand: it.brand, model_number: it.model_number, barcode: it.barcode, avail: avail, qty: 1 });
        renderLines();
    }

    var search = document.getElementById('tfSearch'), sug = document.getElementById('tfSuggest');
    function suggest() {
        var q = search.value.trim().toLowerCase();
        if (!q) { sug.style.display = 'none'; return; }
        var hits = catalog.filter(function (c) {
            return c.branch_quantity > 0 && [c.name, c.brand, c.model_number, c.barcode].join(' ').toLowerCase().indexOf(q) > -1;
        }).slice(0, 8);
        sug.innerHTML = hits.length ? hits.map(function (c) {
            return '<div data-id="' + c.id + '"><span>' + ERP.esc((c.brand ? c.brand + ' · ' : '') + c.name + (c.model_number ? ' (' + c.model_number + ')' : '')) + '</span><span class="text-muted">' + c.branch_quantity + ' avail</span></div>';
        }).join('') : '<div class="text-muted">No matching item with stock here</div>';
        sug.style.display = '';
        sug.querySelectorAll('[data-id]').forEach(function (d) {
            d.addEventListener('click', function () {
                addItem(catalog.filter(function (c) { return String(c.id) === d.getAttribute('data-id'); })[0]);
                search.value = ''; sug.style.display = 'none';
            });
        });
    }
    search.addEventListener('input', suggest);
    document.addEventListener('click', function (e) { if (!sug.contains(e.target) && e.target !== search) sug.style.display = 'none'; });
    ERP.bindScan(search, document.getElementById('tfCam'), function (code) {
        ERP.api('/api/admin/stock-items/scan?code=' + encodeURIComponent(code) + '&branch_id=' + from.value).then(function (j) {
            var it = j.data; it.branch_quantity = (it.branch_stock && it.branch_stock[from.value]) || it.branch_quantity || 0;
            addItem(it); search.value = ''; sug.style.display = 'none';
        }).catch(ERP.error);
    });

    function build(dispatch, force) {
        if (!from.value || !to.value) { ERP.notify('Choose both branches.', 'warning'); return null; }
        if (from.value === to.value) { ERP.notify('Source and destination must be different branches.', 'warning'); return null; }
        var items = lines.filter(function (l) { return l.qty > 0; }).map(function (l) { return { stock_item_id: l.id, quantity: l.qty }; });
        if (!items.length) { ERP.notify('Add at least one item with a quantity.', 'warning'); return null; }
        var p = { from_branch_id: +from.value, to_branch_id: +to.value, notes: document.getElementById('tfNotes').value, items: items };
        if (dispatch) p.dispatch = true;
        if (force) p.force = true;
        return p;
    }

    function submit(p) {
        if (!p) return;
        pendingPayload = p;
        var dup = document.getElementById('tfDup'); dup.style.display = 'none';
        ERP.api('/api/admin/stock-transfers', { method: 'POST', body: p }).then(function (j) {
            ERP.notify('Transfer ' + j.data.transfer_number + (p.dispatch ? ' dispatched.' : ' saved as draft.'));
            lines = []; renderLines(); document.getElementById('tfNotes').value = ''; pendingPayload = null;
            loadCatalog(); loadList();
        }).catch(function (e) {
            if (e.status === 409) {
                dup.innerHTML = ERP.esc(e.message) + ' <button class="btn btn-sm btn-warning ms-2" id="tfForce">Create anyway</button>';
                dup.style.display = '';
                document.getElementById('tfForce').onclick = function () { var q = Object.assign({}, pendingPayload, { force: true }); submit(q); };
            } else ERP.error(e);
        });
    }
    document.getElementById('tfDraft').addEventListener('click', function () { submit(build(false)); });
    document.getElementById('tfDispatch').addEventListener('click', function () { submit(build(true)); });

    // ── list ──
    var all = [];
    function loadList() {
        return ERP.api('/api/admin/stock-transfers').then(function (j) { all = j.data || []; renderList(); chips(); }).catch(ERP.error);
    }
    function chips() {
        var c = { draft: 0, in_transit: 0, received: 0 };
        all.forEach(function (t) { if (c[t.status] != null) c[t.status]++; });
        document.getElementById('tfChips').innerHTML =
            '<button class="tf-chip bg-secondary text-white" data-s="draft">' + c.draft + ' Draft</button>' +
            '<button class="tf-chip bg-warning" data-s="in_transit">' + c.in_transit + ' In transit</button>' +
            '<button class="tf-chip bg-success text-white" data-s="received">' + c.received + ' Received</button>';
        document.querySelectorAll('#tfChips [data-s]').forEach(function (b) {
            b.onclick = function () { document.getElementById('tfStatus').value = b.getAttribute('data-s'); renderList(); };
        });
    }
    function renderList() {
        var f = document.getElementById('tfStatus').value;
        var rows = all.filter(function (t) { return !f || t.status === f; });
        var tb = document.getElementById('tfRows');
        if (!rows.length) { tb.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No transfers.</td></tr>'; return; }
        tb.innerHTML = rows.map(function (t) {
            var items = (t.items || []).map(function (i) { return ERP.esc(i.item_name) + ' ×' + i.quantity; }).join('<br>');
            var dates = 'Created ' + ERP.fmtDateTime(t.created_at) + (t.dispatched_at ? '<br>Sent ' + ERP.fmtDateTime(t.dispatched_at) : '') + (t.received_at ? '<br>Received ' + ERP.fmtDateTime(t.received_at) : '');
            var act = '';
            if (t.status === 'draft') act += '<button class="btn btn-sm btn-primary me-1" data-act="dispatch" data-id="' + t.id + '">Dispatch</button>';
            if (t.status === 'in_transit') act += '<button class="btn btn-sm btn-success me-1" data-act="receive" data-id="' + t.id + '">Receive</button>';
            if (t.status === 'draft' || t.status === 'in_transit') act += '<button class="btn btn-sm btn-outline-danger" data-act="cancel" data-id="' + t.id + '">Cancel</button>';
            return '<tr><td><code>' + ERP.esc(t.transfer_number) + '</code></td><td>' + ERP.esc(t.from_name) + ' → ' + ERP.esc(t.to_name) + '</td><td class="small">' + items + '</td><td>' + badge(t.status) + '</td><td class="small text-muted">' + dates + '</td><td>' + act + '</td></tr>';
        }).join('');
        tb.querySelectorAll('[data-act]').forEach(function (b) {
            b.addEventListener('click', function () {
                var a = b.getAttribute('data-act');
                if (a === 'cancel' && !confirm('Cancel this transfer? Stock in transit goes back to the source branch.')) return;
                b.disabled = true;
                ERP.api('/api/admin/stock-transfers/' + b.getAttribute('data-id') + '/' + a, { method: 'POST' }).then(function () {
                    ERP.notify('Transfer ' + (a === 'receive' ? 'received' : a === 'dispatch' ? 'dispatched' : 'cancelled') + '.'); loadList(); loadCatalog();
                }).catch(function (e) { b.disabled = false; ERP.error(e); });
            });
        });
    }
    document.getElementById('tfStatus').addEventListener('change', renderList);

    function onBranch() { updateNote(); lines = []; renderLines(); loadCatalog(); }
    Promise.all([
        ERP.fillBranchSelect(from, { all: false, value: ERP.currentBranch() }),
        ERP.fillBranchSelect(to, { all: false, remember: false })
    ]).then(function (res) {
        if (res[1].length > 1) { var other = res[1].filter(function (b) { return String(b.id) !== from.value; })[0]; if (other) to.value = other.id; }
        updateNote(); loadCatalog(); loadList();
        from.addEventListener('change', onBranch);
        to.addEventListener('change', updateNote);
    }).catch(ERP.error);
})();
</script>
@endverbatim
@endpush
