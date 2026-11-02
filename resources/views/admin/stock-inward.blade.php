@extends('layouts.admin')
@section('title', 'Stock Inward')
@section('content')
<div id="erpAlerts"></div>
<style>
    .in-match { background: rgba(23,198,102,.1); border: 1px solid rgba(23,198,102,.35); color: #0e9d4e; border-radius: 8px; padding: 10px 14px; font-weight: 600; }
    .in-nomatch { background: rgba(255,162,29,.1); border: 1px solid rgba(255,162,29,.35); color: #b86e00; border-radius: 8px; padding: 10px 14px; }
    .in-scanbox { background: #101a2e; border-radius: 12px; padding: 22px; color: #fff; text-align: center; }
    .in-scanbox svg { width: 72px; height: 72px; stroke: #fff; fill: none; stroke-width: 1.4; opacity: .85; }
    .in-codes { max-height: 120px; overflow: auto; font-family: ui-monospace, Consolas, monospace; font-size: .75rem; color: var(--erp-muted); }
    #inItemSel { min-height: 150px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Stock Inward</h1>
    <a href="/admin/stock-items" class="btn btn-outline-secondary btn-sm">← Inventory</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">1 · Scan or find the product</div>
            <div class="card-body">
                <div class="in-scanbox mb-3">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM20 14v3M14 20h3M20 20h1"/></svg>
                    <div class="small mt-2 mb-3" style="opacity:.8">Scan with a barcode scanner (focus the box and scan), the camera, or type the code.</div>
                    <div class="input-group">
                        <input class="form-control" id="inScan" placeholder="QR / barcode / model code, then Enter" autocomplete="off">
                        <button type="button" class="btn btn-light" id="inCam">Camera</button>
                    </div>
                </div>
                <div id="inMatch"></div>
                <hr>
                <label class="form-label fw-semibold">…or pick manually</label>
                <input class="form-control mb-2" id="inFilter" placeholder="Filter by name, brand, model code">
                <select class="form-select" id="inItemSel" size="6"></select>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">2 · Inward entry</div>
            <div class="card-body">
                <form id="inForm" class="row g-3">
                    <div class="col-12"><div class="small text-muted">Product</div><div class="fw-bold" id="inChosen">No product selected</div></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Date *</label><input type="date" class="form-control" id="inDate" required></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Receiving branch *</label><select class="form-select" id="inBranch" required></select></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Supplier</label><select class="form-select" id="inSupplier"><option value="">—</option></select></div>
                    <div class="col-12">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <label class="form-label fw-semibold mb-0">Quantity</label>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-primary" id="inModeAuto">Auto</button>
                                <button type="button" class="btn btn-outline-secondary" id="inModeManual">Manual</button>
                            </div>
                        </div>
                        <div class="row g-2" id="inAutoRow">
                            <div class="col-6 col-md-4"><div class="small text-muted">Units in each pack</div><input type="number" min="1" class="form-control" id="inPer" value="1"></div>
                            <div class="col-6 col-md-4"><div class="small text-muted">Number of packs</div><input type="number" min="1" class="form-control" id="inPacks" value="1"></div>
                            <div class="col-md-4"><div class="small text-muted">Total quantity</div><div class="form-control-plaintext fw-bold fs-5" id="inTotal">1</div></div>
                        </div>
                        <div class="row g-2 d-none" id="inManualRow">
                            <div class="col-md-4"><div class="small text-muted">Total quantity (type it in)</div><input type="number" min="1" class="form-control" id="inQty" value="1"></div>
                        </div>
                    </div>
                    <div class="col-md-5"><label class="form-label fw-semibold">Supplier invoice ref</label><input class="form-control" id="inRef"></div>
                    <div class="col-md-7"><label class="form-label fw-semibold">Notes</label><input class="form-control" id="inNotes"></div>
                    <div class="col-12"><div class="small text-muted" id="inHint">Every piece gets its own unique ID (QR label). IDs are never reused.</div></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary" id="inSave" disabled>Save entry</button></div>
                </form>
                <div id="inResult" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Recent inward entries</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Date</th><th>Product</th><th>Branch</th><th>Supplier</th><th class="text-end">Qty</th><th>Invoice</th></tr></thead>
            <tbody id="inRows"><tr><td colspan="6" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var items = [], chosen = null, mode = 'auto';

    $('inDate').value = ERP.today();
    if (!ERP.canScan()) $('inCam').style.display = 'none';

    function label(it) { return (it.brand ? it.brand + ' — ' : '') + it.name + (it.model_number ? ' (' + it.model_number + ')' : ''); }

    function choose(it) {
        chosen = it;
        $('inChosen').textContent = label(it);
        $('inSave').disabled = false;
        if (it.pack_quantity && it.pack_quantity > 1) { $('inPer').value = it.pack_quantity; calc(); }
        if (it.supplier_id) $('inSupplier').value = it.supplier_id;
        $('inItemSel').value = it.id;
    }

    function renderList() {
        var f = $('inFilter').value.trim().toLowerCase();
        var list = items.filter(function (i) { return !f || (label(i) + ' ' + (i.barcode || '')).toLowerCase().indexOf(f) !== -1; }).slice(0, 200);
        $('inItemSel').innerHTML = list.map(function (i) { return '<option value="' + i.id + '">' + ERP.esc(label(i)) + '</option>'; }).join('');
        if (chosen) $('inItemSel').value = chosen.id;
    }

    function scan(code) {
        ERP.api('/api/admin/stock-items/scan?code=' + encodeURIComponent(code)).then(function (j) {
            var it = j.data;
            $('inMatch').innerHTML = '<div class="in-match">✓ Match found: ' + ERP.esc(label(it)) + (it.unit ? ' <span class="small">(unit ' + ERP.esc(it.unit.unique_code) + ')</span>' : '') + '</div>';
            choose(it);
        }).catch(function (e) {
            $('inMatch').innerHTML = '<div class="in-nomatch">' + ERP.esc(e.message || 'No product matches this code.') + ' Pick it manually below, or <a href="/admin/stock-items">create the item first</a>.</div>';
        });
    }

    function calc() {
        var t = (parseInt($('inPer').value, 10) || 0) * (parseInt($('inPacks').value, 10) || 0);
        $('inTotal').textContent = t > 0 ? t : '—';
        return t;
    }
    function setMode(m) {
        mode = m;
        $('inAutoRow').classList.toggle('d-none', m !== 'auto');
        $('inManualRow').classList.toggle('d-none', m !== 'manual');
        $('inModeAuto').className = 'btn ' + (m === 'auto' ? 'btn-primary' : 'btn-outline-secondary');
        $('inModeManual').className = 'btn ' + (m === 'manual' ? 'btn-primary' : 'btn-outline-secondary');
    }
    $('inModeAuto').onclick = function () { setMode('auto'); };
    $('inModeManual').onclick = function () { setMode('manual'); $('inQty').value = calc() || 1; };
    $('inPer').addEventListener('input', calc); $('inPacks').addEventListener('input', calc);

    ERP.bindScan($('inScan'), $('inCam'), scan);
    $('inFilter').addEventListener('input', renderList);
    $('inItemSel').addEventListener('change', function () {
        var it = items.filter(function (i) { return String(i.id) === $('inItemSel').value; })[0];
        if (it) { $('inMatch').innerHTML = ''; choose(it); }
    });

    $('inForm').addEventListener('submit', function (e) {
        e.preventDefault();
        if (!chosen) { ERP.notify('Select a product first.', 'warning'); return; }
        var body = { stock_item_id: chosen.id, branch_id: +$('inBranch').value || null, supplier_id: +$('inSupplier').value || null,
            inward_date: $('inDate').value, invoice_ref: $('inRef').value.trim() || null, notes: $('inNotes').value.trim() || null };
        if (mode === 'auto') { body.units_per_pack = parseInt($('inPer').value, 10) || 1; body.packs = parseInt($('inPacks').value, 10) || 1; }
        else { body.quantity = parseInt($('inQty').value, 10) || 0; }
        $('inSave').disabled = true;
        ERP.api('/api/admin/stock-inwards', { method: 'POST', body: body }).then(function (j) {
            var codes = j.data.unit_codes || [];
            $('inResult').innerHTML = '<div class="in-match">Saved. ' + codes.length + ' new unique item ID' + (codes.length === 1 ? '' : 's') + ' created (never reused).'
                + ' <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="inPrint">Print labels</button></div>'
                + '<div class="in-codes mt-2">' + codes.map(ERP.esc).join(' · ') + '</div>';
            $('inPrint').onclick = function () { ERP.printLabels(codes.map(function (c) { return { code: c, name: label(chosen) }; })); };
            loadHistory();
        }).catch(ERP.error).then(function () { $('inSave').disabled = !chosen; });
    });

    function loadHistory() {
        ERP.api('/api/admin/stock-inwards').then(function (j) {
            var rows = j.data || [];
            $('inRows').innerHTML = rows.length ? rows.map(function (r) {
                return '<tr><td>' + ERP.esc(ERP.fmtDate(r.inward_date)) + '</td><td class="fw-semibold">' + ERP.esc(r.item_name) + '</td><td>' + ERP.esc(r.branch_name) + '</td><td>' + ERP.esc(r.supplier_name || '—')
                    + '</td><td class="text-end fw-bold">' + r.quantity + (r.packs > 1 || r.units_per_pack > 1 ? ' <span class="small text-muted">(' + r.packs + '×' + r.units_per_pack + ')</span>' : '') + '</td><td>' + ERP.esc(r.invoice_ref || '') + '</td></tr>';
            }).join('') : '<tr><td colspan="6" class="text-center py-4 text-muted">No inward entries yet.</td></tr>';
        }).catch(ERP.error);
    }

    Promise.all([
        ERP.api('/api/admin/stock-items/lookup'),
        ERP.api('/api/admin/suppliers').catch(function () { return { data: [] }; }),
        ERP.fillBranchSelect($('inBranch'), { all: false })
    ]).then(function (r) {
        items = r[0].data || [];
        $('inSupplier').innerHTML = '<option value="">—</option>' + (r[1].data || []).filter(function (s) { return s.is_active !== 0 && s.is_active !== false; })
            .map(function (s) { return '<option value="' + s.id + '">' + ERP.esc(s.name) + '</option>'; }).join('');
        renderList();
        var pre = new URLSearchParams(location.search).get('item');
        if (pre) { var it = items.filter(function (i) { return String(i.id) === pre; })[0]; if (it) choose(it); }
    }).catch(ERP.error);
    loadHistory();
    $('inScan').focus();
})();
</script>
@endverbatim
@endpush
