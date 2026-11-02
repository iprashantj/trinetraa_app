@extends('layouts.admin')
@section('title', 'Customer Profile')
@section('content')
<div id="erpAlerts"></div>

<div id="cpRoot" data-id="{{ (int) $customerId }}"></div>

<div class="mb-3"><a href="/admin/customers" class="small text-decoration-none">← All customers</a></div>

<div class="erp-card mb-3" id="cpHead" style="padding:22px 24px">
    <div class="text-muted">Loading…</div>
</div>

<div class="cp-tabs" id="cpTabs" role="tablist">
    <button type="button" class="cp-tab active" data-tab="purchases">Purchases</button>
    <button type="button" class="cp-tab" data-tab="tests">Eye tests</button>
    <button type="button" class="cp-tab" data-tab="invoices">Invoices</button>
</div>

<div class="erp-card" style="border-top-left-radius:0">
    <div id="tab-purchases" class="cp-pane"></div>
    <div id="tab-tests" class="cp-pane" style="display:none"></div>
    <div id="tab-invoices" class="cp-pane" style="display:none"></div>
</div>

<style>
    .cp-tabs { display: flex; gap: 4px; margin-top: 4px; }
    .cp-tab { border: 0; background: #e8eaf0; color: var(--erp-muted); font-weight: 600; font-size: .85rem; padding: 10px 20px; border-radius: 10px 10px 0 0; cursor: pointer; }
    .cp-tab.active { background: #fff; color: var(--erp-primary); box-shadow: 0 -2px 0 var(--erp-primary) inset; }
    .cp-pane { padding: 20px 22px; }
    .cp-avatar { width: 64px; height: 64px; border-radius: 50%; background: var(--erp-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.3rem; flex-shrink: 0; }
    .cp-stat { text-align: right; }
    .cp-stat b { display: block; font-size: 1.4rem; color: var(--erp-heading); }
    .cp-rx th, .cp-rx td { text-align: center; padding: .45rem .6rem !important; }
    .cp-rx th:first-child, .cp-rx td:first-child { text-align: left; }
    .cp-test { border: 1px solid var(--erp-border); border-radius: 10px; padding: 16px; margin-bottom: 14px; }
    .cp-restricted { background: #f7f8fc; border: 1px dashed #cfd5e3; border-radius: 10px; padding: 24px; text-align: center; color: var(--erp-muted); }
    .cp-form .form-label { font-size: .75rem; margin-bottom: 2px; }
    .cp-form .rx-grid { display: grid; grid-template-columns: 70px repeat(5, 1fr); gap: 8px; align-items: center; }
    .cp-form .rx-grid .h { font-size: .7rem; font-weight: 700; text-transform: uppercase; color: var(--erp-muted); text-align: center; }
    @media (max-width: 700px) { .cp-form .rx-grid { grid-template-columns: 50px repeat(5, 1fr); gap: 4px; } }
</style>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var CID = Number(document.getElementById('cpRoot').getAttribute('data-id'));
    var P = null;          // profile payload
    var editingTest = null; // eye test being edited (or {} for new)
    var showForm = false;
    var KEYS = ['sph', 'cyl', 'axis', 'add', 'va'];

    function initials(n) {
        return String(n || '?').trim().split(/\s+/).slice(0, 2).map(function (w) { return w.charAt(0).toUpperCase(); }).join('') || '?';
    }

    function renderHead() {
        var c = P.customer;
        var consent = c.marketing_consent
            ? '<span class="badge bg-success">Marketing consent' + (c.consent_at ? ' · ' + ERP.fmtDate(c.consent_at) : '') + '</span>'
            : '<span class="badge bg-secondary">No marketing consent</span>';
        var wa = c.whatsapp_opt_in ? '<span class="badge bg-success">WhatsApp offers: opted in</span>' : '<span class="badge bg-secondary">WhatsApp: not opted in</span>';
        document.getElementById('cpHead').innerHTML =
            '<div class="d-flex flex-wrap align-items-center gap-3">' +
              '<div class="cp-avatar">' + ERP.esc(initials(c.name)) + '</div>' +
              '<div class="flex-grow-1">' +
                '<h2 class="h4 mb-1">' + ERP.esc(c.name) + '</h2>' +
                '<div class="text-muted small">' + ERP.esc(c.phone || '—') + (c.email ? ' · ' + ERP.esc(c.email) : '') + '</div>' +
                '<div class="text-muted small">Customer since ' + ERP.esc(ERP.fmtDate(c.created_at)) + '</div>' +
                '<div class="mt-2 d-flex flex-wrap gap-2">' + consent + wa + '</div>' +
              '</div>' +
              '<div class="cp-stat"><b>' + ERP.inr(P.total_spent) + '</b><span class="text-muted small">Total spent · ' + P.purchases.length + ' purchase(s)</span></div>' +
            '</div>';
    }

    // ── Purchases ────────────────────────────────────────────────────────────
    function renderPurchases() {
        var el = document.getElementById('tab-purchases');
        if (!P.purchases.length) { el.innerHTML = '<div class="text-muted py-3">No purchases linked to this customer yet.</div>'; return; }
        el.innerHTML = '<div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Bill</th><th>Date</th><th>Items</th><th class="text-end">Total</th><th>Payment</th><th></th></tr></thead><tbody>' +
            P.purchases.map(function (b) {
                var items = (b.items || []).map(function (i) { return ERP.esc(i.brand_name) + (i.model_number ? ' ' + ERP.esc(i.model_number) : '') + ' ×' + i.quantity; }).join('<br>');
                var st = b.payment_status || 'paid';
                var cls = st === 'paid' ? 'bg-success' : (st === 'refunded' ? 'bg-secondary' : 'bg-warning');
                return '<tr><td class="fw-semibold">' + ERP.esc(b.bill_number) + '</td><td>' + ERP.esc(ERP.fmtDate(b.bill_date)) + '</td>' +
                    '<td class="small">' + items + '</td><td class="text-end">' + ERP.inr(b.total) + '</td>' +
                    '<td><span class="badge ' + cls + '">' + ERP.esc(st) + '</span>' + (b.payment_method ? ' <span class="text-muted small">' + ERP.esc(b.payment_method) + '</span>' : '') + '</td>' +
                    '<td class="text-end"><a class="btn btn-sm btn-outline-primary" target="_blank" href="/admin/frame-bills/invoice-pdf?ids=' + b.id + '">PDF</a></td></tr>';
            }).join('') + '</tbody></table></div>';
    }

    // ── Invoices (eye checkup bills) ─────────────────────────────────────────
    function renderInvoices() {
        var el = document.getElementById('tab-invoices');
        if (!P.eye_bills.length) { el.innerHTML = '<div class="text-muted py-3">No eye-checkup invoices yet.</div>'; return; }
        el.innerHTML = '<div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Bill</th><th>Date</th><th class="text-end">Total</th><th class="text-end">Balance due</th><th></th></tr></thead><tbody>' +
            P.eye_bills.map(function (b) {
                return '<tr><td class="fw-semibold">' + ERP.esc(b.bill_number) + '</td><td>' + ERP.esc(ERP.fmtDate(b.bill_date)) + '</td>' +
                    '<td class="text-end">' + ERP.inr(b.total) + '</td><td class="text-end">' + ERP.inr(b.balance_due) + '</td>' +
                    '<td class="text-end"><a class="btn btn-sm btn-outline-primary" target="_blank" href="/admin/eye-checkup-bills/invoice-pdf?ids=' + b.id + '">PDF</a></td></tr>';
            }).join('') + '</tbody></table></div>';
    }

    // ── Eye tests ────────────────────────────────────────────────────────────
    function rxRow(label, t, eye) {
        return '<tr><td class="fw-semibold">' + label + '</td>' + KEYS.map(function (k) { return '<td>' + ERP.esc(t[eye + '_' + k] || '—') + '</td>'; }).join('') + '</tr>';
    }

    function testCard(t, idx) {
        return '<div class="cp-test">' +
            '<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">' +
              '<div><b>' + ERP.esc(ERP.fmtDate(t.test_date)) + '</b>' + (idx === 0 ? ' <span class="badge bg-success">Latest</span>' : '') +
              '<div class="text-muted small">' + (t.branch_name ? 'Eye test · ' + ERP.esc(t.branch_name) : 'Eye test') + (t.optometrist ? ' · ' + ERP.esc(t.optometrist) : '') + '</div></div>' +
              '<div class="d-flex flex-wrap gap-2 align-items-center">' +
                (t.has_report ? '<span class="badge bg-info">Report attached</span> <a class="btn btn-sm btn-outline-primary" target="_blank" href="/admin/eye-tests/' + t.id + '/report">View report</a>' : '') +
                '<button class="btn btn-sm btn-outline-secondary" data-edit-test="' + t.id + '">Edit</button>' +
                '<button class="btn btn-sm btn-outline-danger" data-del-test="' + t.id + '">Delete</button>' +
              '</div>' +
            '</div>' +
            '<table class="table table-sm cp-rx mb-1"><thead><tr><th>Eye</th><th>SPH</th><th>CYL</th><th>AXIS</th><th>ADD</th><th>VA</th></tr></thead><tbody>' +
              rxRow('Right', t, 'right') + rxRow('Left', t, 'left') + '</tbody></table>' +
            '<div class="small text-muted">' + (t.pd ? 'PD: ' + ERP.esc(t.pd) + ' · ' : '') + (t.source === 'upload' ? 'Report uploaded' : 'Typed in') + '</div>' +
            (t.notes ? '<div class="small mt-1">' + ERP.esc(t.notes) + '</div>' : '') +
        '</div>';
    }

    function testForm(t) {
        t = t || {};
        function cell(eye, k) {
            return '<input class="form-control form-control-sm text-center" name="' + eye + '_' + k + '" maxlength="12" value="' + ERP.esc(t[eye + '_' + k] || '') + '" placeholder="' + k.toUpperCase() + '">';
        }
        function row(label, eye) { return '<div class="fw-semibold small">' + label + '</div>' + KEYS.map(function (k) { return cell(eye, k); }).join(''); }
        return '<form id="testForm" class="cp-form erp-card" style="padding:18px;background:#f7f8fc;box-shadow:none;margin-bottom:16px">' +
            '<div class="fw-semibold mb-3">' + (t.id ? 'Edit eye test' : 'New eye test') + ' <span class="text-muted small fw-normal">— type the values in, upload a report, or both</span></div>' +
            '<div class="row g-3 mb-3">' +
              '<div class="col-md-3"><label class="form-label">Test date *</label><input type="date" class="form-control form-control-sm" name="test_date" required value="' + ERP.esc((t.test_date || ERP.today()).substring(0, 10)) + '"></div>' +
              '<div class="col-md-3"><label class="form-label">Optometrist</label><input class="form-control form-control-sm" name="optometrist" maxlength="255" value="' + ERP.esc(t.optometrist || '') + '"></div>' +
              '<div class="col-md-2"><label class="form-label">PD</label><input class="form-control form-control-sm" name="pd" maxlength="12" value="' + ERP.esc(t.pd || '') + '"></div>' +
              '<div class="col-md-4"><label class="form-label">Branch</label><select class="form-select form-select-sm" name="branch_id" id="tfBranch"></select></div>' +
            '</div>' +
            '<div class="rx-grid mb-3"><span></span><span class="h">SPH</span><span class="h">CYL</span><span class="h">AXIS</span><span class="h">ADD</span><span class="h">VA</span>' +
              row('Right', 'right') + row('Left', 'left') + '</div>' +
            '<div class="row g-3 mb-3">' +
              '<div class="col-md-8"><label class="form-label">Notes</label><textarea class="form-control form-control-sm" rows="2" name="notes" maxlength="2000">' + ERP.esc(t.notes || '') + '</textarea></div>' +
              '<div class="col-md-4"><label class="form-label">Report (PDF / JPG / PNG, max 5 MB)</label><input type="file" class="form-control form-control-sm" name="report" accept=".pdf,.jpg,.jpeg,.png">' +
              (t.has_report ? '<div class="form-text">A report is already attached; choosing a file replaces it.</div>' : '') + '</div>' +
            '</div>' +
            '<button class="btn btn-primary btn-sm" type="submit">' + (t.id ? 'Save changes' : 'Save test') + '</button> ' +
            '<button class="btn btn-outline-secondary btn-sm" type="button" id="testCancel">Cancel</button>' +
        '</form>';
    }

    function renderTests() {
        var el = document.getElementById('tab-tests');
        if (!P.prescriptions_visible) {
            el.innerHTML = '<div class="cp-restricted">🔒 <b>Restricted</b> — you don\'t have access to prescription records.<br><span class="small">Ask the owner to give you the “Eye-test / prescription records” permission.</span></div>';
            return;
        }
        var html = '<div class="d-flex justify-content-between align-items-center mb-3"><div class="text-muted small">' + P.eye_tests.length + ' record(s)</div>' +
            (showForm ? '' : '<button class="btn btn-primary btn-sm" id="newTestBtn">+ New test</button>') + '</div>';
        if (showForm) html += testForm(editingTest);
        html += P.eye_tests.length ? P.eye_tests.map(testCard).join('') : (showForm ? '' : '<div class="text-muted py-3">No eye tests recorded yet.</div>');
        el.innerHTML = html;

        var nb = document.getElementById('newTestBtn');
        if (nb) nb.onclick = function () { editingTest = {}; showForm = true; renderTests(); };
        var cancel = document.getElementById('testCancel');
        if (cancel) cancel.onclick = function () { showForm = false; editingTest = null; renderTests(); };
        var form = document.getElementById('testForm');
        if (form) {
            ERP.fillBranchSelect(document.getElementById('tfBranch'), { all: false, remember: false, value: editingTest && editingTest.branch_id ? editingTest.branch_id : ERP.currentBranch() });
            form.onsubmit = submitTest;
        }
        el.querySelectorAll('[data-edit-test]').forEach(function (b) {
            b.onclick = function () {
                editingTest = P.eye_tests.find(function (x) { return x.id == b.getAttribute('data-edit-test'); });
                showForm = true; renderTests(); window.scrollTo({ top: 0, behavior: 'smooth' });
            };
        });
        el.querySelectorAll('[data-del-test]').forEach(function (b) {
            b.onclick = function () {
                if (!confirm('Delete this eye test record (and its report)?')) return;
                ERP.api('/api/admin/eye-tests/' + b.getAttribute('data-del-test'), { method: 'DELETE' })
                    .then(function () { ERP.notify('Eye test deleted.'); return load(); }).catch(ERP.error);
            };
        });
    }

    function submitTest(e) {
        e.preventDefault();
        var form = e.target, fd = new FormData(form);
        var file = form.querySelector('[name=report]').files[0];
        if (file && file.size > 5 * 1024 * 1024) { ERP.notify('The report must be 5 MB or smaller.', 'danger'); return; }
        if (!file) fd.delete('report');
        var url = editingTest && editingTest.id ? '/api/admin/eye-tests/' + editingTest.id : '/api/admin/customers/' + CID + '/eye-tests';
        ERP.api(url, { method: 'POST', body: fd }).then(function () {
            ERP.notify('Eye test saved.');
            showForm = false; editingTest = null;
            return load();
        }).catch(ERP.error);
    }

    // ── Tabs + load ──────────────────────────────────────────────────────────
    function openTab(name) {
        document.querySelectorAll('.cp-tab').forEach(function (x) { x.classList.toggle('active', x.getAttribute('data-tab') === name); });
        ['purchases', 'tests', 'invoices'].forEach(function (t) { document.getElementById('tab-' + t).style.display = t === name ? '' : 'none'; });
    }
    document.getElementById('cpTabs').addEventListener('click', function (e) {
        var b = e.target.closest('.cp-tab'); if (!b) return;
        openTab(b.getAttribute('data-tab'));
        try { history.replaceState(null, '', '#' + b.getAttribute('data-tab')); } catch (x) {}
    });
    var initial = (location.hash || '').replace('#', '');
    if (['purchases', 'tests', 'invoices'].indexOf(initial) !== -1) openTab(initial);

    function load() {
        return ERP.api('/api/admin/customers/' + CID + '/profile').then(function (j) {
            P = j.data;
            P.purchases = P.purchases || []; P.eye_bills = P.eye_bills || []; P.eye_tests = P.eye_tests || [];
            renderHead(); renderPurchases(); renderTests(); renderInvoices();
        }).catch(function (e) {
            document.getElementById('cpHead').innerHTML = '<div class="text-danger">' + ERP.esc(e.message) + '</div>';
        });
    }
    load();
})();
</script>
@endverbatim
@endpush
