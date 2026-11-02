@extends('layouts.admin')
@section('title', 'Profit and Margin')
@section('content')
<div id="erpAlerts"></div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Profit &amp; Margin</h1>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <select class="form-select form-select-sm" id="pBranch" style="width:auto"></select>
        <input type="date" class="form-control form-control-sm" id="pFrom" style="width:auto">
        <input type="date" class="form-control form-control-sm" id="pTo" style="width:auto">
        <button class="btn btn-primary btn-sm" id="pGo">Show</button>
        <a class="btn btn-outline-secondary btn-sm" id="pCsv" href="#">Download CSV</a>
    </div>
</div>
<div class="alert alert-info py-2 small" id="pNote" style="display:none"></div>

<div class="row g-3 mb-3" id="pKpis"></div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">Margin by brand <span class="text-muted small">(returns removed, bill discounts spread over items)</span></div>
            <div class="table-responsive"><table class="table table-hover mb-0 align-middle">
                <thead><tr><th>Brand</th><th class="text-end">Units</th><th class="text-end">Revenue</th><th class="text-end">Cost</th><th class="text-end">Margin</th><th class="text-end">%</th></tr></thead>
                <tbody id="pBrands"><tr><td colspan="6" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
            </table></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">Sales by staff</div>
            <div class="table-responsive"><table class="table mb-0 align-middle">
                <thead><tr><th>Staff</th><th class="text-end">Invoices</th><th class="text-end">Sales (taxable)</th></tr></thead>
                <tbody id="pStaff"></tbody>
            </table></div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span>Slow-moving stock <span class="text-muted small">— in stock but not sold for</span></span>
        <select class="form-select form-select-sm" id="pSlow" style="width:auto"><option value="30">30 days</option><option value="60">60 days</option><option value="90" selected>90 days</option><option value="180">180 days</option></select>
    </div>
    <div class="table-responsive"><table class="table table-hover mb-0 align-middle">
        <thead><tr><th>Item</th><th>Brand</th><th class="text-end">In stock</th><th>Last sold</th><th class="text-end">Stock value (at cost)</th></tr></thead>
        <tbody id="pSlowRows"></tbody>
    </table></div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var d = new Date(), pad = function (n) { return ('0' + n).slice(-2); };
    $('pFrom').value = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-01';
    $('pTo').value = ERP.today();

    function qs() {
        var p = ['from=' + $('pFrom').value, 'to=' + $('pTo').value, 'slow_days=' + $('pSlow').value];
        if ($('pBranch').value) p.push('branch_id=' + $('pBranch').value);
        return p.join('&');
    }
    function kpi(label, val) { return '<div class="col-6 col-md-3"><div class="erp-kpi card h-100"><div class="card-body"><div class="text-muted small">' + label + '</div><div class="fs-4 fw-bold">' + val + '</div></div></div></div>'; }
    function load() {
        $('pCsv').href = '/api/admin/reports/profit?format=csv&' + qs();
        ERP.api('/api/admin/reports/profit?' + qs()).then(function (j) {
            var r = j.data, t = r.totals, pct = t.revenue > 0 ? Math.round(t.margin / t.revenue * 1000) / 10 : 0;
            $('pKpis').innerHTML = kpi('Revenue (taxable)', ERP.inr(t.revenue)) + kpi('Cost of goods', ERP.inr(t.cost)) + kpi('Gross margin', ERP.inr(t.margin)) + kpi('Margin %', pct + '%');
            var note = $('pNote');
            if (r.cost_coverage_pct < 100) { note.style.display = ''; note.textContent = 'Cost price was known for ' + r.cost_coverage_pct + '% of sold lines. Set a cost price on stock items (Stock Items → Edit) so margins cover everything; lines without a cost are left out of cost and margin.'; }
            else note.style.display = 'none';
            $('pBrands').innerHTML = r.brands.length ? r.brands.map(function (b) {
                return '<tr><td class="fw-semibold">' + ERP.esc(b.brand) + '</td><td class="text-end">' + b.units + '</td><td class="text-end">' + ERP.inr(b.revenue) + '</td><td class="text-end">' + ERP.inr(b.cost)
                    + '</td><td class="text-end ' + (b.margin < 0 ? 'text-danger' : '') + '">' + ERP.inr(b.margin) + '</td><td class="text-end">' + (b.margin_pct == null ? '—' : b.margin_pct + '%') + '</td></tr>';
            }).join('') : '<tr><td colspan="6" class="text-center py-4 text-muted">No sales in this period.</td></tr>';
            $('pStaff').innerHTML = r.staff.length ? r.staff.map(function (s) {
                return '<tr><td>' + ERP.esc(s.staff) + '</td><td class="text-end">' + s.invoices + '</td><td class="text-end">' + ERP.inr(s.sales) + '</td></tr>';
            }).join('') : '<tr><td colspan="3" class="text-center py-3 text-muted">No sales in this period.</td></tr>';
            $('pSlowRows').innerHTML = r.slow_moving.length ? r.slow_moving.map(function (s) {
                return '<tr><td class="fw-semibold">' + ERP.esc(s.name) + '</td><td>' + ERP.esc(s.brand || '') + '</td><td class="text-end">' + s.quantity + '</td><td>' + (s.last_sold ? ERP.fmtDate(s.last_sold) : '<span class="text-muted">Never</span>') + '</td><td class="text-end">' + ERP.inr(s.stock_value) + '</td></tr>';
            }).join('') : '<tr><td colspan="5" class="text-center py-3 text-muted">Nothing slow-moving — every item in stock has sold recently.</td></tr>';
        }).catch(ERP.error);
    }
    $('pGo').onclick = load; $('pSlow').onchange = load;
    ERP.fillBranchSelect($('pBranch'), { all: 'All branches' }).then(load);
})();
</script>
@endverbatim
@endpush
