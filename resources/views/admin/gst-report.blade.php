@extends('layouts.admin')
@section('title', 'GST Report')
@section('content')
<div id="erpAlerts"></div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">GST Sales Register</h1>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <select class="form-select form-select-sm" id="gBranch" style="width:auto"></select>
        <input type="date" class="form-control form-control-sm" id="gFrom" style="width:auto">
        <input type="date" class="form-control form-control-sm" id="gTo" style="width:auto">
        <button class="btn btn-primary btn-sm" id="gGo">Show</button>
        <a class="btn btn-outline-secondary btn-sm" id="gCsv" href="#">Download CSV</a>
    </div>
</div>
<p class="text-muted small">Every frame and eye-checkup invoice in the period with taxable value and the CGST / SGST split — hand the CSV to your accountant. Taxable value is after discounts, coupons and loyalty points.</p>

<div class="row g-3 mb-3" id="gKpis"></div>

<div class="card mb-3">
    <div class="card-header">By GST rate</div>
    <div class="table-responsive"><table class="table mb-0 align-middle">
        <thead><tr><th>GST %</th><th class="text-end">Invoices</th><th class="text-end">Taxable</th><th class="text-end">CGST</th><th class="text-end">SGST</th><th class="text-end">Total</th></tr></thead>
        <tbody id="gRate"></tbody>
    </table></div>
</div>

<div class="card">
    <div class="card-header">Invoices</div>
    <div class="table-responsive"><table class="table table-hover mb-0 align-middle">
        <thead><tr><th>Invoice</th><th>Date</th><th>Type</th><th>Customer</th><th>GSTIN</th><th class="text-end">GST %</th><th class="text-end">Taxable</th><th class="text-end">CGST</th><th class="text-end">SGST</th><th class="text-end">Total</th></tr></thead>
        <tbody id="gRows"><tr><td colspan="10" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
    </table></div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var d = new Date(), pad = function (n) { return ('0' + n).slice(-2); };
    $('gFrom').value = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-01';
    $('gTo').value = ERP.today();

    function qs() {
        var p = ['from=' + $('gFrom').value, 'to=' + $('gTo').value];
        if ($('gBranch').value) p.push('branch_id=' + $('gBranch').value);
        return p.join('&');
    }
    function kpi(label, val) { return '<div class="col-6 col-md-3"><div class="erp-kpi card h-100"><div class="card-body"><div class="text-muted small">' + label + '</div><div class="fs-4 fw-bold">' + val + '</div></div></div></div>'; }
    function load() {
        $('gCsv').href = '/api/admin/reports/gst?format=csv&' + qs();
        ERP.api('/api/admin/reports/gst?' + qs()).then(function (j) {
            var r = j.data, t = r.totals;
            $('gKpis').innerHTML = kpi('Invoices', t.invoices) + kpi('Taxable value', ERP.inr(t.taxable)) + kpi('GST collected', ERP.inr(t.cgst + t.sgst)) + kpi('Refunds in period', ERP.inr(r.returns_refunded));
            $('gRate').innerHTML = r.by_rate.length ? r.by_rate.map(function (x) {
                return '<tr><td>' + x.rate + '%</td><td class="text-end">' + x.invoices + '</td><td class="text-end">' + ERP.inr(x.taxable) + '</td><td class="text-end">' + ERP.inr(x.cgst)
                    + '</td><td class="text-end">' + ERP.inr(x.sgst) + '</td><td class="text-end">' + ERP.inr(x.total) + '</td></tr>';
            }).join('') + '<tr class="fw-bold"><td>Total</td><td class="text-end">' + t.invoices + '</td><td class="text-end">' + ERP.inr(t.taxable) + '</td><td class="text-end">' + ERP.inr(t.cgst) + '</td><td class="text-end">' + ERP.inr(t.sgst) + '</td><td class="text-end">' + ERP.inr(t.total) + '</td></tr>'
                : '<tr><td colspan="6" class="text-center py-3 text-muted">No invoices in this period.</td></tr>';
            $('gRows').innerHTML = r.rows.length ? r.rows.map(function (x) {
                return '<tr><td><code>' + ERP.esc(x.invoice_no) + '</code></td><td>' + ERP.fmtDate(x.date) + '</td><td>' + ERP.esc(x.type) + '</td><td>' + ERP.esc(x.customer || '—') + '</td><td class="small">' + ERP.esc(x.gstin || '') + '</td>'
                    + '<td class="text-end">' + x.rate + '</td><td class="text-end">' + ERP.inr(x.taxable) + '</td><td class="text-end">' + ERP.inr(x.cgst) + '</td><td class="text-end">' + ERP.inr(x.sgst) + '</td><td class="text-end">' + ERP.inr(x.total) + '</td></tr>';
            }).join('') : '<tr><td colspan="10" class="text-center py-4 text-muted">No invoices in this period.</td></tr>';
        }).catch(ERP.error);
    }
    $('gGo').onclick = load;
    ERP.fillBranchSelect($('gBranch'), { all: 'All branches' }).then(load);
})();
</script>
@endverbatim
@endpush
