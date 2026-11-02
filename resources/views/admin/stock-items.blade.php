@extends('layouts.admin')
@section('title', 'Stock Items')
@section('content')
<div id="erpAlerts"></div>
<style>
    .si-stat { padding: 18px 20px; }
    .si-stat__label { font-size: .75rem; color: var(--erp-muted); text-transform: uppercase; letter-spacing: .06em; font-weight: 700; }
    .si-stat__value { font-size: 1.7rem; font-weight: 800; color: var(--erp-heading); line-height: 1.2; }
    .si-code { font-family: ui-monospace, Consolas, monospace; font-size: .8rem; font-weight: 600; }
    .si-bq { display: inline-block; margin-right: .6rem; font-size: .78rem; white-space: nowrap; }
    .si-bq b { color: var(--erp-heading); }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 mb-0">Branch Inventory</h1>
    <div class="d-flex gap-2">
        <a href="/admin/stock-inward" class="btn btn-outline-primary btn-sm">+ Stock inward</a>
        <button type="button" class="btn btn-primary btn-sm" id="siNewBtn">+ New item</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-4"><div class="card si-stat"><div class="si-stat__label">Total quantity</div><div class="si-stat__value" id="siStatQty">—</div></div></div>
    <div class="col-6 col-md-4"><div class="card si-stat"><div class="si-stat__label">Items sold</div><div class="si-stat__value" id="siStatSold">—</div></div></div>
    <div class="col-12 col-md-4"><div class="card si-stat"><div class="si-stat__label">Low-stock alerts</div><div class="si-stat__value" id="siStatLow">—</div></div></div>
</div>

<div class="card mb-3" id="siFormCard" style="display:none">
    <div class="card-header" id="siFormTitle">New Stock Item</div>
    <div class="card-body">
        <form id="siForm" class="row g-3" autocomplete="off">
            <input type="hidden" id="si_id" value="">
            <div class="col-md-6"><label class="form-label fw-semibold">Name *</label><input class="form-control" id="si_name" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Brand</label><input class="form-control" id="si_brand" list="siBrandList"><datalist id="siBrandList"></datalist></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Model code</label><input class="form-control" id="si_model_number" placeholder="e.g. ABZY-ZYAB"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">SKU</label><input class="form-control" id="si_sku"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Barcode / QR value</label><input class="form-control" id="si_barcode"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Pack quantity</label><input type="number" min="1" class="form-control" id="si_pack_quantity" value="1"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Category</label><select class="form-select" id="si_category_id"><option value="">None</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Supplier</label><select class="form-select" id="si_supplier_id"><option value="">None</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Sale price (₹)</label><input type="number" step="0.01" min="0" class="form-control" id="si_sale_price"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Cost price (₹)</label><input type="number" step="0.01" min="0" class="form-control" id="si_cost_price"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">HSN code</label><input class="form-control" id="si_hsn_code" maxlength="12" placeholder="9003 frames · 9001 lenses · 9004 sunglasses"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">GST %</label><input type="number" step="0.01" min="0" max="100" class="form-control" id="si_gst_rate" placeholder="Default for this product"></div>
            <div class="col-md-3" id="siOpenWrap"><label class="form-label fw-semibold">Opening stock</label><input type="number" min="0" class="form-control" id="si_opening_stock" value="0"></div>
            <div class="col-md-3" id="siOpenBranchWrap"><label class="form-label fw-semibold">Opening stock branch</label><select class="form-select" id="si_branch_id"></select></div>
            <div class="col-md-6 d-none" id="siQtyRead"><label class="form-label fw-semibold">Current quantity</label>
                <div class="form-control-plaintext"><span id="siQtyText">—</span> <span class="text-muted small">· read-only. Change it with <a href="/admin/stock-inward">Stock inward</a>, a transfer, a sale or a <a href="/admin/stock-ledger">correction</a>.</span></div></div>
            <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea class="form-control" rows="2" id="si_description"></textarea></div>
            <div class="col-12"><label class="form-label fw-semibold">Image URL</label><input class="form-control" id="si_image"></div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="siSubmit">Create</button>
                <button type="button" class="btn btn-outline-secondary" id="siCancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body border-bottom">
        <div class="row g-2 align-items-end">
            <div class="col-md-4"><input class="form-control" id="siSearch" placeholder="Search product code, brand, name, barcode…"></div>
            <div class="col-md-2"><select class="form-select" id="siBrandFilter"><option value="">Brand: All</option></select></div>
            <div class="col-md-2"><select class="form-select" id="siCatFilter"><option value="">Category: All</option></select></div>
            <div class="col-md-2"><select class="form-select" id="siBranchFilter"></select></div>
            <div class="col-md-2 text-md-end"><span class="text-muted small" id="siCount"></span></div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Code</th><th>Brand — Model</th><th>Category</th><th>Quantity</th><th>Status</th><th class="text-end">Sale ₹</th><th class="text-end">Actions</th></tr></thead>
            <tbody id="siRows"><tr><td colspan="7" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
        </table>
    </div>
    <div class="card-body d-flex justify-content-between align-items-center border-top">
        <button class="btn btn-outline-secondary btn-sm" id="siPrev">← Prev</button>
        <span class="small text-muted" id="siPage"></span>
        <button class="btn btn-outline-secondary btn-sm" id="siNext">Next →</button>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var state = { page: 1, perPage: 20, total: 0, rows: [], branches: {}, editing: null, timer: null };

    function branchName(id) { return state.branches[id] || ('#' + id); }

    function loadLookups() {
        return Promise.all([
            ERP.branches(),
            ERP.api('/api/admin/categories').catch(function () { return { data: [] }; }),
            ERP.api('/api/admin/suppliers').catch(function () { return { data: [] }; }),
            ERP.api('/api/admin/brands').catch(function () { return { data: [] }; })
        ]).then(function (r) {
            r[0].forEach(function (b) { state.branches[b.id] = b.name; });
            var cats = r[1].data || [], sups = r[2].data || [], brands = r[3].data || [];
            var catOpts = cats.map(function (c) { return '<option value="' + c.id + '">' + ERP.esc(c.name) + '</option>'; }).join('');
            $('si_category_id').innerHTML = '<option value="">None</option>' + catOpts;
            $('siCatFilter').innerHTML = '<option value="">Category: All</option>' + catOpts;
            $('si_supplier_id').innerHTML = '<option value="">None</option>' + sups.filter(function (s) { return s.is_active !== 0 && s.is_active !== false; })
                .map(function (s) { return '<option value="' + s.id + '">' + ERP.esc(s.name) + '</option>'; }).join('');
            var names = brands.map(function (b) { return b.name; }).filter(Boolean);
            $('siBrandList').innerHTML = names.map(function (n) { return '<option value="' + ERP.esc(n) + '">'; }).join('');
            $('siBrandFilter').innerHTML = '<option value="">Brand: All</option>' + names.map(function (n) { return '<option>' + ERP.esc(n) + '</option>'; }).join('');
            return ERP.fillBranchSelect($('siBranchFilter'), { all: 'Branch: All' });
        }).then(function () {
            return ERP.fillBranchSelect($('si_branch_id'), { all: false, remember: false, value: ERP.currentBranch() });
        });
    }

    function load() {
        var q = '?page=' + state.page + '&per_page=' + state.perPage;
        var s = $('siSearch').value.trim();
        if (s) q += '&search=' + encodeURIComponent(s);
        if ($('siBrandFilter').value) q += '&brand=' + encodeURIComponent($('siBrandFilter').value);
        if ($('siCatFilter').value) q += '&category_id=' + $('siCatFilter').value;
        if ($('siBranchFilter').value) q += '&branch_id=' + $('siBranchFilter').value;
        return ERP.api('/api/admin/stock-items' + q).then(function (j) {
            state.rows = j.data || [];
            var m = j.meta || {};
            state.total = m.total || 0;
            $('siStatQty').textContent = (m.total_quantity || 0).toLocaleString('en-IN');
            $('siStatSold').textContent = (m.items_sold || 0).toLocaleString('en-IN');
            $('siStatLow').textContent = m.low_stock_count || 0;
            $('siStatLow').style.color = (m.low_stock_count || 0) > 0 ? 'var(--erp-warning)' : '';
            render();
        }).catch(ERP.error);
    }

    function render() {
        var tb = $('siRows');
        if (!state.rows.length) { tb.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No items found.</td></tr>'; }
        else tb.innerHTML = state.rows.map(function (r) {
            var bs = r.branch_stock || {};
            var per = Object.keys(bs).map(function (k) { return '<span class="si-bq">' + ERP.esc(branchName(k)) + ': <b>' + bs[k] + '</b></span>'; }).join('');
            var code = r.model_number || r.sku || r.barcode || '—';
            var title = (r.brand ? ERP.esc(r.brand) + ' — ' : '') + ERP.esc(r.name);
            return '<tr><td class="si-code">' + ERP.esc(code) + '</td>'
                + '<td><div class="fw-semibold">' + title + '</div>' + (r.model_number && r.model_number !== code ? '<div class="small text-muted">' + ERP.esc(r.model_number) + '</div>' : '') + '</td>'
                + '<td>' + ERP.esc(r.category ? r.category.name : '') + '</td>'
                + '<td><div class="fw-bold">' + r.quantity + '</div><div>' + per + '</div></td>'
                + '<td>' + ERP.stockBadge(r.quantity, r.is_low) + (r.is_low && r.low_threshold != null ? '<div class="small text-muted">alert ≤ ' + r.low_threshold + '</div>' : '') + '</td>'
                + '<td class="text-end">' + ERP.inr(r.sale_price) + '</td>'
                + '<td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary" data-edit="' + r.id + '">Edit</button> '
                + '<button class="btn btn-sm btn-outline-secondary" data-labels="' + r.id + '">Labels</button> '
                + '<button class="btn btn-sm btn-outline-danger" data-del="' + r.id + '">Delete</button></td></tr>';
        }).join('');
        var pages = Math.max(1, Math.ceil(state.total / state.perPage));
        $('siPage').textContent = 'Page ' + state.page + ' of ' + pages;
        $('siCount').textContent = state.total + ' item' + (state.total === 1 ? '' : 's');
        $('siPrev').disabled = state.page <= 1;
        $('siNext').disabled = state.page >= pages;
    }

    function openForm(row) {
        state.editing = row || null;
        $('siFormCard').style.display = '';
        $('siFormTitle').textContent = row ? 'Edit Stock Item' : 'New Stock Item';
        $('siSubmit').textContent = row ? 'Save changes' : 'Create';
        ['name', 'brand', 'model_number', 'sku', 'barcode', 'category_id', 'supplier_id', 'sale_price', 'cost_price', 'hsn_code', 'gst_rate', 'description', 'image'].forEach(function (f) {
            $('si_' + f).value = row && row[f] != null ? row[f] : '';
        });
        $('si_pack_quantity').value = row && row.pack_quantity ? row.pack_quantity : 1;
        $('si_opening_stock').value = 0;
        $('siOpenWrap').classList.toggle('d-none', !!row);
        $('siOpenBranchWrap').classList.toggle('d-none', !!row);
        $('siQtyRead').classList.toggle('d-none', !row);
        if (row) $('siQtyText').textContent = row.current_stock != null ? row.current_stock : row.quantity;
        $('siFormCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
        $('si_name').focus();
    }
    function closeForm() { $('siFormCard').style.display = 'none'; state.editing = null; }

    function val(id) { var v = $('si_' + id).value.trim(); return v === '' ? null : v; }

    $('siForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var body = {
            name: val('name'), brand: val('brand'), model_number: val('model_number'), sku: val('sku'), barcode: val('barcode'),
            pack_quantity: parseInt($('si_pack_quantity').value, 10) || 1,
            category_id: val('category_id') ? +val('category_id') : null, supplier_id: val('supplier_id') ? +val('supplier_id') : null,
            sale_price: val('sale_price') === null ? 0 : +val('sale_price'), cost_price: val('cost_price') === null ? null : +val('cost_price'),
            hsn_code: val('hsn_code'), gst_rate: val('gst_rate') === null ? null : +val('gst_rate'),
            description: val('description'), image: val('image')
        };
        var url = '/api/admin/stock-items', method = 'POST';
        if (state.editing) { url += '/' + state.editing.id; method = 'PUT'; }
        else {
            body.opening_stock = parseInt($('si_opening_stock').value, 10) || 0;
            if (body.opening_stock > 0) body.branch_id = +$('si_branch_id').value || null;
        }
        $('siSubmit').disabled = true;
        ERP.api(url, { method: method, body: body }).then(function () {
            ERP.notify(state.editing ? 'Item updated.' : 'Item created.');
            closeForm(); return load();
        }).catch(ERP.error).then(function () { $('siSubmit').disabled = false; });
    });

    $('siNewBtn').onclick = function () { openForm(null); };
    $('siCancel').onclick = closeForm;
    $('siPrev').onclick = function () { if (state.page > 1) { state.page--; load(); } };
    $('siNext').onclick = function () { state.page++; load(); };
    ['siBrandFilter', 'siCatFilter', 'siBranchFilter'].forEach(function (id) { $(id).addEventListener('change', function () { state.page = 1; load(); }); });
    $('siSearch').addEventListener('input', function () { clearTimeout(state.timer); state.timer = setTimeout(function () { state.page = 1; load(); }, 300); });

    $('siRows').addEventListener('click', function (e) {
        var t = e.target.closest('button'); if (!t) return;
        var find = function (id) { return state.rows.filter(function (r) { return String(r.id) === String(id); })[0]; };
        if (t.dataset.edit) openForm(find(t.dataset.edit));
        else if (t.dataset.del) {
            var r = find(t.dataset.del);
            if (!confirm('Delete "' + r.name + '"? This also removes its stock records.')) return;
            ERP.api('/api/admin/stock-items/' + r.id, { method: 'DELETE' }).then(function () { ERP.notify('Item deleted.'); return load(); }).catch(ERP.error);
        } else if (t.dataset.labels) {
            var it = find(t.dataset.labels);
            var br = $('siBranchFilter').value;
            ERP.api('/api/admin/stock-items/' + it.id + '/units' + (br ? '?branch_id=' + br : '')).then(function (j) {
                var units = (j.data || []).filter(function (u) { return u.status === 'in_stock'; });
                if (!units.length) { ERP.notify('No in-stock units with codes to print for this item.', 'warning'); return; }
                ERP.printLabels(units.map(function (u) { return { code: u.unique_code, name: (it.brand ? it.brand + ' ' : '') + it.name }; }));
            }).catch(ERP.error);
        }
    });

    loadLookups().then(load).catch(ERP.error);
})();
</script>
@endverbatim
@endpush
