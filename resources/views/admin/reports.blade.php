@extends('layouts.admin')
@section('title', 'Branch Reports')
@section('content')
<div id="erpAlerts"></div>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Stock &amp; Sales Reports</h1>
        <p class="text-muted small mb-0">Total stock, items sold, returns and invoices — by branch and for all branches.</p>
    </div>
    <form id="rpForm" class="d-flex flex-wrap align-items-end gap-2">
        <div><label class="form-label small mb-1">From</label><input type="date" class="form-control form-control-sm" id="rpFrom"></div>
        <div><label class="form-label small mb-1">To</label><input type="date" class="form-control form-control-sm" id="rpTo"></div>
        <button class="btn btn-primary btn-sm" type="submit">Apply</button>
        <button class="btn btn-outline-secondary btn-sm" type="button" data-range="today">Today</button>
        <button class="btn btn-outline-secondary btn-sm" type="button" data-range="month">This month</button>
        <button class="btn btn-outline-secondary btn-sm" type="button" data-range="year">This year</button>
        <a class="btn btn-outline-primary btn-sm" id="rpCsv" href="#">Export CSV</a>
    </form>
</div>

<div class="erp-card mb-4">
    <div class="erp-card__head"><h3 class="erp-card__title">By branch</h3><span class="text-muted small" id="rpRange"></span></div>
    <div class="table-responsive">
        <table class="table align-middle mb-0 rp-table">
            <thead>
                <tr>
                    <th>Branch</th><th class="text-end">Stock qty</th><th class="text-end">Stock lines</th><th class="text-end">Invoices</th>
                    <th class="text-end">Items sold</th><th class="text-end">Sales</th><th class="text-end">Returns</th>
                    <th class="text-end">Items returned</th><th class="text-end">Refunded</th>
                </tr>
            </thead>
            <tbody id="rpRows"><tr><td colspan="9" class="text-center text-muted py-4">Loading…</td></tr></tbody>
        </table>
    </div>
</div>

<div class="erp-row erp-row--even">
    <div class="erp-card">
        <div class="erp-card__head"><h3 class="erp-card__title">Sales by branch</h3></div>
        <div class="erp-card__body"><div id="rpChart"></div></div>
    </div>
    <div class="erp-card">
        <div class="erp-card__head"><h3 class="erp-card__title">Stock by category</h3><span class="text-muted small">all branches</span></div>
        <div class="erp-card__body" id="rpCats"></div>
    </div>
</div>

<style>
    .rp-table tfoot td, .rp-table tr.rp-total td { font-weight: 800; background: #f7f8fc; color: var(--erp-heading); }
    .rp-bar { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: .82rem; }
    .rp-bar .n { width: 110px; flex-shrink: 0; color: var(--erp-text); font-weight: 600; }
    .rp-bar .t { flex: 1; background: #eef0f6; border-radius: 6px; height: 22px; overflow: hidden; }
    .rp-bar .t > span { display: block; height: 100%; background: var(--erp-primary); border-radius: 6px; min-width: 2px; }
    .rp-bar .v { width: 100px; text-align: right; font-weight: 700; color: var(--erp-heading); }
</style>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var fromEl = document.getElementById('rpFrom'), toEl = document.getElementById('rpTo');
    function iso(d) { return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2); }
    function setRange(kind) {
        var n = new Date();
        if (kind === 'today') { fromEl.value = toEl.value = iso(n); }
        else if (kind === 'year') { fromEl.value = n.getFullYear() + '-01-01'; toEl.value = iso(n); }
        else { fromEl.value = iso(new Date(n.getFullYear(), n.getMonth(), 1)); toEl.value = iso(n); }
    }
    setRange('month');

    function num(n) { return (Number(n) || 0).toLocaleString('en-IN'); }
    function rowHtml(r, total) {
        return '<tr' + (total ? ' class="rp-total"' : '') + '><td>' + ERP.esc(r.branch) + '</td>' +
            '<td class="text-end">' + num(r.stock_quantity) + '</td><td class="text-end">' + num(r.stock_lines) + '</td>' +
            '<td class="text-end">' + num(r.invoices) + '</td><td class="text-end">' + num(r.items_sold) + '</td>' +
            '<td class="text-end">' + ERP.inr(r.sales) + '</td><td class="text-end">' + num(r.returns) + '</td>' +
            '<td class="text-end">' + num(r.items_returned) + '</td><td class="text-end">' + ERP.inr(r.refunded) + '</td></tr>';
    }

    function render(d) {
        document.getElementById('rpRange').textContent = ERP.fmtDate(d.from) + ' – ' + ERP.fmtDate(d.to);
        var body = (d.branches || []).map(function (r) { return rowHtml(r, false); }).join('');
        body += rowHtml(d.totals, true);
        document.getElementById('rpRows').innerHTML = body;

        var max = Math.max.apply(null, (d.branches || []).map(function (b) { return b.sales; }).concat([1]));
        document.getElementById('rpChart').innerHTML = (d.branches || []).length
            ? '<svg viewBox="0 0 400 ' + ((d.branches.length * 36) + 8) + '" width="100%" role="img" aria-label="Sales by branch">' +
              d.branches.map(function (b, i) {
                  var w = Math.round((b.sales / max) * 230), y = i * 36 + 4;
                  return '<text x="0" y="' + (y + 17) + '" font-size="12" fill="#283c50" font-weight="600">' + ERP.esc(b.branch) + '</text>' +
                      '<rect x="90" y="' + y + '" width="' + Math.max(w, 2) + '" height="24" rx="5" fill="#3454d1"><title>' + ERP.esc(b.branch) + ': ' + ERP.inr(b.sales) + '</title></rect>' +
                      '<text x="' + (96 + Math.max(w, 2)) + '" y="' + (y + 17) + '" font-size="11" fill="#1b2436" font-weight="700">' + ERP.inr(b.sales) + '</text>';
              }).join('') + '</svg>'
            : '<div class="text-muted">No branches.</div>';

        var cats = d.by_category || [], cmax = Math.max.apply(null, cats.map(function (c) { return c.quantity; }).concat([1]));
        document.getElementById('rpCats').innerHTML = cats.length ? cats.map(function (c) {
            return '<div class="rp-bar"><span class="n">' + ERP.esc(c.category) + '</span><span class="t"><span style="width:' + Math.round(c.quantity / cmax * 100) + '%"></span></span><span class="v">' + num(c.quantity) + '</span></div>';
        }).join('') : '<div class="text-muted">No stock recorded yet.</div>';
    }

    function load() {
        var q = '?from=' + fromEl.value + '&to=' + toEl.value;
        document.getElementById('rpCsv').href = '/api/admin/reports' + q + '&format=csv';
        return ERP.api('/api/admin/reports' + q).then(function (j) { render(j.data); }).catch(ERP.error);
    }

    document.getElementById('rpForm').addEventListener('submit', function (e) { e.preventDefault(); load(); });
    document.querySelectorAll('[data-range]').forEach(function (b) {
        b.addEventListener('click', function () { setRange(b.getAttribute('data-range')); load(); });
    });
    load();
})();
</script>
@endverbatim
@endpush
