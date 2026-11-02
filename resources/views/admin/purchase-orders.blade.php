@extends('layouts.admin')
@section('title', 'Purchase Orders')
@section('content')
<div id="erpAlerts"></div>
<style>
    .po-status { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; }
    .po-line-input { width: 110px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 mb-0">Purchase Orders</h1>
    <div class="d-flex gap-2">
        <a href="/admin/stock-inward" class="btn btn-outline-secondary btn-sm">Stock inward</a>
        <button type="button" class="btn btn-primary btn-sm" id="poNew">+ New purchase order</button>
    </div>
</div>

{{-- Create / edit form --}}
<div class="card mb-3" id="poFormCard" style="display:none">
    <div class="card-header" id="poFormTitle">New purchase order</div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3"><label class="form-label fw-semibold">Supplier</label><select class="form-select" id="poSupplier"><option value="">—</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Deliver to branch *</label><select class="form-select" id="poBranch"></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Order date</label><input type="date" class="form-control" id="poDate"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Expected on</label><input type="date" class="form-control" id="poExpected"></div>
            <div class="col-12"><label class="form-label fw-semibold">Notes</label><input class="form-control" id="poNotes" maxlength="2000"></div>
        </div>

        <label class="form-label fw-semibold">Add products</label>
        <div class="row g-2 mb-2 align-items-end">
            <div class="col-md-4"><input class="form-control" id="poFilter" placeholder="Filter by name, brand, model code" autocomplete="off"></div>
            <div class="col-md-4"><select class="form-select" id="poItemSel"></select></div>
            <div class="col-6 col-md-1"><input type="number" min="1" class="form-control" id="poAddQty" value="1" title="Quantity"></div>
            <div class="col-6 col-md-2"><input type="number" min="0" step="0.01" class="form-control" id="poAddCost" placeholder="Unit cost ₹" title="Unit cost"></div>
            <div class="col-md-1"><button type="button" class="btn btn-outline-primary w-100" id="poAdd">Add</button></div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-2">
                <thead><tr><th>Product</th><th style="width:120px">Qty</th><th style="width:150px">Unit cost ₹</th><th class="text-end" style="width:130px">Line total</th><th style="width:50px"></th></tr></thead>
                <tbody id="poLines"><tr><td colspan="5" class="text-center text-muted py-3">No products added yet.</td></tr></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <strong>Total: <span id="poTotal">₹0</span></strong>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" id="poCancelForm">Cancel</button>
                <button type="button" class="btn btn-outline-primary" id="poSaveDraft">Save as draft</button>
                <button type="button" class="btn btn-primary" id="poSavePlace">Save &amp; place order</button>
            </div>
        </div>
    </div>
</div>

{{-- Detail / receive --}}
<div class="card mb-3" id="poDetail" style="display:none"></div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Orders</span>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" id="poBranchFilter" style="width:auto"></select>
            <select class="form-select form-select-sm" id="poStatusFilter" style="width:auto">
                <option value="">All statuses</option><option value="draft">Draft</option><option value="ordered">Ordered</option>
                <option value="partial">Partly received</option><option value="received">Received</option><option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>PO #</th><th>Supplier</th><th>Branch</th><th>Ordered</th><th>Expected</th><th class="text-end">Qty (recv/ord)</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
            <tbody id="poRows"><tr><td colspan="9" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var items = [], lines = [], editId = null, openId = null;
    var BADGE = { draft: 'secondary', ordered: 'primary', partial: 'warning', received: 'success', cancelled: 'danger' };
    var LABEL = { draft: 'Draft', ordered: 'Ordered', partial: 'Partly received', received: 'Received', cancelled: 'Cancelled' };

    function badge(s) { return '<span class="badge bg-' + (BADGE[s] || 'secondary') + ' po-status">' + (LABEL[s] || s) + '</span>'; }
    function label(it) { return (it.brand ? it.brand + ' — ' : '') + it.name + (it.model_number ? ' (' + it.model_number + ')' : ''); }

    // ── pickers ──────────────────────────────────────────────────────
    function fillItems() {
        var f = $('poFilter').value.trim().toLowerCase();
        var list = items.filter(function (i) { return !f || (label(i) + ' ' + (i.barcode || '')).toLowerCase().indexOf(f) !== -1; }).slice(0, 200);
        $('poItemSel').innerHTML = list.map(function (i) { return '<option value="' + i.id + '">' + ERP.esc(label(i)) + '</option>'; }).join('') || '<option value="">No match</option>';
        syncCost();
    }
    function syncCost() {
        var it = items.filter(function (i) { return String(i.id) === $('poItemSel').value; })[0];
        if (it) { $('poAddCost').value = it.cost_price != null ? it.cost_price : ''; if (it.pack_quantity > 1) $('poAddQty').value = it.pack_quantity; }
    }

    // ── form lines ───────────────────────────────────────────────────
    function renderLines() {
        var tb = $('poLines'), total = 0;
        if (!lines.length) { tb.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No products added yet.</td></tr>'; $('poTotal').textContent = ERP.inr(0); return; }
        tb.innerHTML = lines.map(function (l, i) {
            var lt = l.quantity * l.unit_cost; total += lt;
            return '<tr><td>' + ERP.esc(l.name) + '</td>' +
                '<td><input type="number" min="1" class="form-control form-control-sm" data-q="' + i + '" value="' + l.quantity + '"></td>' +
                '<td><input type="number" min="0" step="0.01" class="form-control form-control-sm" data-c="' + i + '" value="' + l.unit_cost + '"></td>' +
                '<td class="text-end">' + ERP.inr(lt) + '</td>' +
                '<td><button type="button" class="btn btn-sm btn-outline-danger" data-rm="' + i + '" aria-label="Remove">×</button></td></tr>';
        }).join('');
        $('poTotal').textContent = ERP.inr(total);
        tb.querySelectorAll('[data-q],[data-c]').forEach(function (inp) {
            inp.addEventListener('change', function () {
                var q = inp.getAttribute('data-q'), c = inp.getAttribute('data-c');
                if (q !== null) lines[q].quantity = Math.max(1, parseInt(inp.value, 10) || 1);
                if (c !== null) lines[c].unit_cost = Math.max(0, parseFloat(inp.value) || 0);
                renderLines();
            });
        });
        tb.querySelectorAll('[data-rm]').forEach(function (b) { b.addEventListener('click', function () { lines.splice(Number(b.getAttribute('data-rm')), 1); renderLines(); }); });
    }

    $('poAdd').addEventListener('click', function () {
        var it = items.filter(function (i) { return String(i.id) === $('poItemSel').value; })[0];
        if (!it) { ERP.notify('Pick a product first.', 'warning'); return; }
        var qty = Math.max(1, parseInt($('poAddQty').value, 10) || 1), cost = Math.max(0, parseFloat($('poAddCost').value) || 0);
        var ex = lines.filter(function (l) { return l.stock_item_id === it.id; })[0];
        if (ex) { ex.quantity += qty; ex.unit_cost = cost || ex.unit_cost; } else lines.push({ stock_item_id: it.id, name: label(it), quantity: qty, unit_cost: cost });
        $('poAddQty').value = 1; renderLines();
    });
    $('poFilter').addEventListener('input', fillItems);
    $('poItemSel').addEventListener('change', syncCost);

    function openForm(po) {
        editId = po ? po.id : null;
        $('poFormTitle').textContent = po ? 'Edit draft ' + po.po_number : 'New purchase order';
        $('poSupplier').value = po && po.supplier_id ? po.supplier_id : '';
        $('poDate').value = (po && po.order_date ? String(po.order_date).slice(0, 10) : ERP.today());
        $('poExpected').value = po && po.expected_date ? String(po.expected_date).slice(0, 10) : '';
        $('poNotes').value = po ? po.notes || '' : '';
        if (po) { $('poBranch').value = po.branch_id; $('poBranch').disabled = true; } else { $('poBranch').disabled = false; }
        lines = po ? po.items.map(function (i) { return { stock_item_id: i.stock_item_id, name: (i.brand ? i.brand + ' — ' : '') + i.item_name + (i.model_number ? ' (' + i.model_number + ')' : ''), quantity: i.quantity_ordered, unit_cost: Number(i.unit_cost) }; }) : [];
        renderLines();
        $('poFormCard').style.display = '';
        $('poFormCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    function closeForm() { $('poFormCard').style.display = 'none'; editId = null; }

    function save(place) {
        if (!lines.length) { ERP.notify('Add at least one product.', 'warning'); return; }
        var body = {
            supplier_id: $('poSupplier').value || null, order_date: $('poDate').value || null, expected_date: $('poExpected').value || null,
            notes: $('poNotes').value, items: lines.map(function (l) { return { stock_item_id: l.stock_item_id, quantity: l.quantity, unit_cost: l.unit_cost }; })
        };
        var p;
        if (editId) {
            p = ERP.api('/api/admin/purchase-orders/' + editId, { method: 'PUT', body: body }).then(function (j) {
                return place ? ERP.api('/api/admin/purchase-orders/' + editId + '/place', { method: 'POST' }) : j;
            });
        } else {
            body.branch_id = $('poBranch').value; body.place = !!place;
            p = ERP.api('/api/admin/purchase-orders', { method: 'POST', body: body });
        }
        p.then(function (j) { ERP.notify('Saved ' + j.data.po_number + (place ? ' — order placed.' : ' as a draft.')); closeForm(); load(); showDetail(j.data.id); }).catch(ERP.error);
    }
    $('poSaveDraft').addEventListener('click', function () { save(false); });
    $('poSavePlace').addEventListener('click', function () { save(true); });
    $('poNew').addEventListener('click', function () { openForm(null); });
    $('poCancelForm').addEventListener('click', closeForm);

    // ── list ─────────────────────────────────────────────────────────
    function load() {
        var q = [];
        if ($('poStatusFilter').value) q.push('status=' + $('poStatusFilter').value);
        if ($('poBranchFilter').value) q.push('branch_id=' + $('poBranchFilter').value);
        ERP.api('/api/admin/purchase-orders' + (q.length ? '?' + q.join('&') : '')).then(function (j) {
            var rows = j.data || [];
            $('poRows').innerHTML = rows.length ? rows.map(function (r) {
                return '<tr><td><code>' + ERP.esc(r.po_number) + '</code></td><td>' + ERP.esc(r.supplier_name || '—') + '</td><td>' + ERP.esc(r.branch_name || '—') + '</td>' +
                    '<td>' + ERP.fmtDate(r.order_date) + '</td><td>' + (ERP.fmtDate(r.expected_date) || '—') + '</td>' +
                    '<td class="text-end">' + r.qty_received + ' / ' + r.qty_ordered + '</td><td class="text-end">' + ERP.inr(r.total) + '</td><td>' + badge(r.status) + '</td>' +
                    '<td class="text-end"><button class="btn btn-sm btn-outline-primary" data-view="' + r.id + '">Open</button></td></tr>';
            }).join('') : '<tr><td colspan="9" class="text-center py-4 text-muted">No purchase orders yet.</td></tr>';
            $('poRows').querySelectorAll('[data-view]').forEach(function (b) { b.addEventListener('click', function () { showDetail(b.getAttribute('data-view')); }); });
        }).catch(ERP.error);
    }
    $('poStatusFilter').addEventListener('change', load);
    $('poBranchFilter').addEventListener('change', load);

    // ── detail + receive ─────────────────────────────────────────────
    function showDetail(id) {
        openId = id;
        ERP.api('/api/admin/purchase-orders/' + id).then(function (j) {
            var po = j.data, canReceive = po.status === 'ordered' || po.status === 'partial', box = $('poDetail');
            var rows = po.items.map(function (i) {
                return '<tr><td>' + ERP.esc((i.brand ? i.brand + ' — ' : '') + i.item_name) + (i.model_number ? ' <span class="text-muted">(' + ERP.esc(i.model_number) + ')</span>' : '') + '</td>' +
                    '<td class="text-end">' + i.quantity_ordered + '</td><td class="text-end">' + i.quantity_received + '</td><td class="text-end">' + i.outstanding + '</td>' +
                    '<td class="text-end">' + ERP.inr(i.unit_cost) + '</td><td class="text-end">' + ERP.inr(i.unit_cost * i.quantity_ordered) + '</td>' +
                    (canReceive ? '<td><input type="number" min="0" max="' + i.outstanding + '" value="' + i.outstanding + '" class="form-control form-control-sm po-line-input" data-recv="' + i.id + '"' + (i.outstanding ? '' : ' disabled') + '></td>' : '') + '</tr>';
            }).join('');
            var acts = '<a class="btn btn-sm btn-outline-secondary" href="/api/admin/purchase-orders/' + po.id + '/csv">CSV</a>' +
                '<button class="btn btn-sm btn-outline-secondary" id="poPrint">Print</button>';
            if (po.status === 'draft') acts += '<button class="btn btn-sm btn-outline-primary" id="poEdit">Edit</button><button class="btn btn-sm btn-primary" id="poPlace">Place order</button><button class="btn btn-sm btn-outline-danger" id="poDel">Delete</button>';
            if (po.status === 'draft' || po.status === 'ordered') acts += '<button class="btn btn-sm btn-outline-danger" id="poCancel">Cancel order</button>';
            box.innerHTML = '<div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"><span><strong>' + ERP.esc(po.po_number) + '</strong> ' + badge(po.status) + '</span><div class="d-flex gap-2 flex-wrap">' + acts + '<button class="btn btn-sm btn-outline-secondary" id="poClose" aria-label="Close">✕</button></div></div>' +
                '<div class="card-body"><div class="row g-2 mb-3 small text-muted"><div class="col-md-3">Supplier: <b class="text-body">' + ERP.esc(po.supplier_name || '—') + '</b></div><div class="col-md-3">Branch: <b class="text-body">' + ERP.esc(po.branch_name || '—') + '</b></div><div class="col-md-3">Ordered: <b class="text-body">' + (ERP.fmtDate(po.order_date) || '—') + '</b></div><div class="col-md-3">Expected: <b class="text-body">' + (ERP.fmtDate(po.expected_date) || '—') + '</b></div>' + (po.notes ? '<div class="col-12">Notes: ' + ERP.esc(po.notes) + '</div>' : '') + '</div>' +
                '<div class="table-responsive"><table class="table align-middle mb-3"><thead><tr><th>Product</th><th class="text-end">Ordered</th><th class="text-end">Received</th><th class="text-end">Outstanding</th><th class="text-end">Unit cost</th><th class="text-end">Line total</th>' + (canReceive ? '<th>Receive now</th>' : '') + '</tr></thead><tbody>' + rows + '</tbody></table></div>' +
                '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2"><strong>Total ' + ERP.inr(po.total) + '</strong>' +
                (canReceive ? '<div class="d-flex gap-2 align-items-center"><input class="form-control form-control-sm" id="poInv" placeholder="Supplier invoice ref" style="width:200px"><button class="btn btn-primary" id="poReceive">Receive stock</button></div>' : '') + '</div>' +
                (canReceive ? '<div class="small text-muted mt-2">Received quantities are added to ' + ERP.esc(po.branch_name || 'the branch') + ' stock with a unique ID (QR label) for every piece. You can receive in parts.</div>' : '') + '</div>';
            box.style.display = '';
            bind(po);
            box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }).catch(ERP.error);
    }

    function act(id, path, msg, method) {
        return ERP.api('/api/admin/purchase-orders/' + id + (path || ''), { method: method || 'POST' }).then(function () { ERP.notify(msg); load(); if (method === 'DELETE') $('poDetail').style.display = 'none'; else showDetail(id); }).catch(ERP.error);
    }
    function bind(po) {
        var g = function (id) { return $(id); };
        g('poClose').onclick = function () { $('poDetail').style.display = 'none'; };
        g('poPrint').onclick = function () { window.print(); };
        if (g('poEdit')) g('poEdit').onclick = function () { openForm(po); };
        if (g('poPlace')) g('poPlace').onclick = function () { act(po.id, '/place', 'Order placed.'); };
        if (g('poCancel')) g('poCancel').onclick = function () { if (confirm('Cancel ' + po.po_number + '?')) act(po.id, '/cancel', 'Order cancelled.'); };
        if (g('poDel')) g('poDel').onclick = function () { if (confirm('Delete draft ' + po.po_number + '?')) act(po.id, '', 'Draft deleted.', 'DELETE'); };
        if (g('poReceive')) g('poReceive').onclick = function () {
            var recv = [].slice.call(document.querySelectorAll('[data-recv]')).map(function (i) { return { id: Number(i.getAttribute('data-recv')), quantity: parseInt(i.value, 10) || 0 }; });
            var n = recv.reduce(function (s, r) { return s + r.quantity; }, 0);
            if (!n) { ERP.notify('Enter a quantity to receive.', 'warning'); return; }
            g('poReceive').disabled = true;
            ERP.api('/api/admin/purchase-orders/' + po.id + '/receive', { method: 'POST', body: { lines: recv, invoice_ref: g('poInv').value } })
                .then(function (j) { ERP.notify(n + ' piece(s) received into stock' + (j.data.status === 'received' ? ' — order complete.' : '.')); load(); showDetail(po.id); })
                .catch(function (e) { g('poReceive').disabled = false; ERP.error(e); });
        };
    }

    // ── boot ─────────────────────────────────────────────────────────
    Promise.all([
        ERP.api('/api/admin/stock-items/lookup'), ERP.api('/api/admin/suppliers'),
        ERP.fillBranchSelect($('poBranch'), { all: false }), ERP.fillBranchSelect($('poBranchFilter'), { all: 'All branches', remember: false })
    ]).then(function (r) {
        items = r[0].data || [];
        $('poSupplier').innerHTML = '<option value="">—</option>' + (r[1].data || []).filter(function (s) { return s.is_active !== false && s.is_active !== 0; }).map(function (s) { return '<option value="' + s.id + '">' + ERP.esc(s.name) + '</option>'; }).join('');
        fillItems(); load();
    }).catch(ERP.error);
})();
</script>
@endverbatim
@endpush
