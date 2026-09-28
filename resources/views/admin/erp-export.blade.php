@extends('layouts.admin')
@section('title', 'ERP / Data Export')
@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">📤 ERP / Data Export</h2>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0">Export Settings</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Data Type</label>
                        <div class="row g-2" id="erpTypes">
                            <div class="col-6">
                                <div class="card p-2 text-center cursor-pointer border-primary bg-primary bg-opacity-10" style="cursor:pointer" data-type="orders">
                                    <div style="font-size:24px">🛒</div>
                                    <small class="fw-semibold">Sales Orders</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card p-2 text-center cursor-pointer" style="cursor:pointer" data-type="frameBills">
                                    <div style="font-size:24px">👓</div>
                                    <small class="fw-semibold">Frame Bills</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card p-2 text-center cursor-pointer" style="cursor:pointer" data-type="eyeCheckup">
                                    <div style="font-size:24px">👁️</div>
                                    <small class="fw-semibold">Eye Checkup Bills</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card p-2 text-center cursor-pointer" style="cursor:pointer" data-type="customers">
                                    <div style="font-size:24px">👥</div>
                                    <small class="fw-semibold">Customers</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Format</label>
                        <div class="d-flex gap-2" id="erpFormats">
                            <button class="btn btn-sm btn-primary" data-format="csv">CSV (Excel)</button>
                            <button class="btn btn-sm btn-outline-secondary" data-format="tally_xml">Tally XML</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date Range (optional)</label>
                        <div class="row g-2">
                            <div class="col-6"><input type="date" class="form-control form-control-sm" id="erpFrom"></div>
                            <div class="col-6"><input type="date" class="form-control form-control-sm" id="erpTo"></div>
                        </div>
                        <small class="text-muted">Leave blank to export all records</small>
                    </div>

                    <button class="btn btn-primary w-100" id="erpExportBtn">
                        📥 Download <span id="erpBtnLabel">Sales Orders as CSV (Excel)</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Export History</h5>
                    <button class="btn btn-sm btn-outline-secondary" id="erpRefresh">↻ Refresh</button>
                </div>
                <div class="card-body p-0">
                    <div id="erpLoading" class="text-center py-4"><div class="spinner-border text-primary"></div></div>
                    <div id="erpTableWrap" class="table-responsive" style="display:none">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Format</th>
                                    <th>Rows</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="erpBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var EXPORT_TYPES = [
        { key: 'orders', label: 'Sales Orders' },
        { key: 'frameBills', label: 'Frame Bills' },
        { key: 'eyeCheckup', label: 'Eye Checkup Bills' },
        { key: 'customers', label: 'Customers' }
    ];
    var FORMATS = [
        { key: 'csv', label: 'CSV (Excel)' },
        { key: 'tally_xml', label: 'Tally XML' }
    ];

    var form = { type: 'orders', format: 'csv', from: '', to: '' };
    var logs = [];

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function typeLabel(key) {
        var t = EXPORT_TYPES.find(function (x) { return x.key === key; });
        return t ? t.label : '';
    }
    function formatLabel(key) {
        var f = FORMATS.find(function (x) { return x.key === key; });
        return f ? f.label : '';
    }

    function renderControls() {
        document.querySelectorAll('#erpTypes [data-type]').forEach(function (el) {
            var active = el.getAttribute('data-type') === form.type;
            el.className = 'card p-2 text-center cursor-pointer' + (active ? ' border-primary bg-primary bg-opacity-10' : '');
        });
        document.querySelectorAll('#erpFormats [data-format]').forEach(function (el) {
            var active = el.getAttribute('data-format') === form.format;
            el.className = 'btn btn-sm ' + (active ? 'btn-primary' : 'btn-outline-secondary');
        });
        document.getElementById('erpBtnLabel').textContent = typeLabel(form.type) + ' as ' + formatLabel(form.format);
    }

    function renderLogs() {
        var body = document.getElementById('erpBody');
        if (logs.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No exports yet</td></tr>';
            return;
        }
        body.innerHTML = logs.map(function (l) {
            return '<tr>' +
                '<td><small>' + esc(l.export_type) + '</small></td>' +
                '<td><span class="badge bg-secondary">' + esc(l.format) + '</span></td>' +
                '<td>' + (l.row_count != null ? l.row_count : '') + '</td>' +
                '<td><span class="badge bg-' + (l.status === 'success' ? 'success' : 'danger') + '">' + esc(l.status) + '</span></td>' +
                '<td><small>' + new Date(l.created_at).toLocaleString('en-IN') + '</small></td>' +
                '</tr>';
        }).join('');
    }

    function loadLogs() {
        document.getElementById('erpLoading').style.display = '';
        document.getElementById('erpTableWrap').style.display = 'none';
        fetch('/api/admin/erp-export/logs')
            .then(function (r) { return r.json(); })
            .then(function (json) {
                logs = json.logs || [];
                document.getElementById('erpLoading').style.display = 'none';
                document.getElementById('erpTableWrap').style.display = '';
                renderLogs();
            });
    }

    function doExport() {
        var qs = new URLSearchParams({ type: form.type, format: form.format });
        if (form.from) qs.set('from', form.from);
        if (form.to) qs.set('to', form.to);
        window.open('/api/admin/erp-export?' + qs.toString(), '_blank');
        setTimeout(loadLogs, 1500);
    }

    document.querySelectorAll('#erpTypes [data-type]').forEach(function (el) {
        el.addEventListener('click', function () { form.type = el.getAttribute('data-type'); renderControls(); });
    });
    document.querySelectorAll('#erpFormats [data-format]').forEach(function (el) {
        el.addEventListener('click', function () { form.format = el.getAttribute('data-format'); renderControls(); });
    });
    document.getElementById('erpFrom').addEventListener('change', function (e) { form.from = e.target.value; });
    document.getElementById('erpTo').addEventListener('change', function (e) { form.to = e.target.value; });
    document.getElementById('erpExportBtn').addEventListener('click', doExport);
    document.getElementById('erpRefresh').addEventListener('click', loadLogs);

    renderControls();
    loadLogs();
})();
</script>
@endverbatim
@endpush
