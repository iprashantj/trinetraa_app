@extends('layouts.admin')
@section('title', 'Eye Checkup Bills')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Eye Checkup Bills</h1></div>
<div id="ecError"></div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">New Eye Checkup Bill</div>
    <div class="card-body">
        <form id="ecForm" class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Customer Name</label><input class="form-control" id="ec_customer_name"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Contact</label><input class="form-control" id="ec_customer_contact"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Bill Date</label><input type="date" class="form-control" id="ec_bill_date"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Left Eye Rx</label><input class="form-control" id="ec_left_eye" placeholder="e.g. -1.50 / -0.50 / 90"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Right Eye Rx</label><input class="form-control" id="ec_right_eye" placeholder="e.g. -1.50 / -0.50 / 90"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Addition</label><input class="form-control" id="ec_addition"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Frame ₹</label><input type="number" step="0.01" class="form-control" id="ec_frame_amount" value="0"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Glass ₹</label><input type="number" step="0.01" class="form-control" id="ec_glass_amount" value="0"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Advance ₹</label><input type="number" step="0.01" class="form-control" id="ec_advance_amount" value="0"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Other ₹</label><input type="number" step="0.01" class="form-control" id="ec_other_amount" value="0"></div>
            <div class="col-md-2 d-flex align-items-end"><div class="form-check mb-1"><input type="checkbox" class="form-check-input" id="withGst"><label class="form-check-label" for="withGst">GST?</label></div></div>
            <div class="col-md-3" id="ec_gst_wrap" style="display:none"><label class="form-label fw-semibold">GST Rate %</label><input type="number" step="0.01" class="form-control" id="ec_gst_rate" value="0"></div>
            <div class="col-12"><label class="form-label fw-semibold">Notes</label><textarea class="form-control" rows="2" id="ec_notes"></textarea></div>
            <div class="col-12 d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-primary" id="ecSubmit">Create Bill</button>
                <div class="text-end">
                    <div>Subtotal: <strong id="ecSubtotal">₹0.00</strong></div>
                    <div>Tax: <strong id="ecTax">₹0.00</strong></div>
                    <div class="fs-5">Total: <strong id="ecTotal">₹0.00</strong></div>
                    <div>Balance Due: <strong id="ecBalance">₹0.00</strong></div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="ecSelectAll">
            <label class="form-check-label" for="ecSelectAll">Select all</label>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="ecPrintSelected" disabled>🖨️ Print Selected Invoices</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th style="width:2rem"></th><th>Bill #</th><th>Customer</th><th>Date</th><th class="text-end">Total ₹</th><th class="text-end">Balance ₹</th><th>Actions</th></tr></thead>
                <tbody id="ecRows">
                    <tr><td colspan="7" class="text-center py-4 text-muted">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var rows = [], loading = false;
    var errorEl = document.getElementById('ecError');
    var form = document.getElementById('ecForm');

    document.getElementById('ec_bill_date').value = new Date().toISOString().split('T')[0];

    function showError(msg) {
        errorEl.innerHTML = msg ? '<div class="alert alert-danger py-2">' + msg + '</div>' : '';
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function recalcTotals() {
        var frame = Number(document.getElementById('ec_frame_amount').value) || 0;
        var glass = Number(document.getElementById('ec_glass_amount').value) || 0;
        var advance = Number(document.getElementById('ec_advance_amount').value) || 0;
        var other = Number(document.getElementById('ec_other_amount').value) || 0;
        var withGst = document.getElementById('withGst').checked;
        var gstRate = Number(document.getElementById('ec_gst_rate').value) || 0;

        var subtotal = frame + glass + other;
        var tax = withGst ? subtotal * gstRate / 100 : 0;
        var total = subtotal + tax;
        var balanceDue = total - advance;

        document.getElementById('ecSubtotal').textContent = '₹' + subtotal.toFixed(2);
        document.getElementById('ecTax').textContent = '₹' + tax.toFixed(2);
        document.getElementById('ecTotal').textContent = '₹' + total.toFixed(2);
        document.getElementById('ecBalance').textContent = '₹' + balanceDue.toFixed(2);
        document.getElementById('ecBalance').style.color = balanceDue > 0 ? '#dc2626' : '#16a34a';
    }

    function selectedIds() {
        return Array.prototype.slice.call(document.querySelectorAll('#ecRows input[data-select]:checked'))
            .map(function (c) { return c.getAttribute('data-select'); });
    }

    function updatePrintSelectedState() {
        document.getElementById('ecPrintSelected').disabled = selectedIds().length === 0;
    }

    function renderRows() {
        var tbody = document.getElementById('ecRows');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No bills yet.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (row) {
            var balance = Number(row.balance_due);
            return '<tr>' +
                '<td><input type="checkbox" data-select="' + row.id + '"></td>' +
                '<td><code>' + escapeHtml(row.bill_number) + '</code></td>' +
                '<td>' + (row.customer_name ? escapeHtml(row.customer_name) : '—') + '</td>' +
                '<td>' + new Date(row.bill_date).toLocaleDateString('en-IN') + '</td>' +
                '<td class="text-end">₹' + Number(row.total).toFixed(2) + '</td>' +
                '<td class="text-end" style="color:' + (balance > 0 ? '#dc2626' : '#16a34a') + '">₹' + balance.toFixed(2) + '</td>' +
                '<td>' +
                    '<a class="btn btn-sm btn-outline-secondary me-1" target="_blank" href="/admin/eye-checkup-bills/invoice-pdf?ids=' + row.id + '">🖨️ Print</a>' +
                    '<button class="btn btn-sm btn-outline-danger" data-del="' + row.id + '">Delete</button>' +
                '</td>' +
                '</tr>';
        }).join('');
        tbody.querySelectorAll('[data-del]').forEach(function (b) {
            b.addEventListener('click', function () { remove(Number(b.getAttribute('data-del'))); });
        });
        tbody.querySelectorAll('[data-select]').forEach(function (c) {
            c.addEventListener('change', updatePrintSelectedState);
        });
        updatePrintSelectedState();
    }

    async function load() {
        var r = await fetch('/api/admin/eye-checkup-bills');
        var d = await r.json();
        rows = d.data || [];
        renderRows();
    }

    async function remove(id) {
        if (!window.confirm('Delete?')) return;
        await fetch('/api/admin/eye-checkup-bills/' + id, { method: 'DELETE' });
        await load();
    }

    document.getElementById('withGst').addEventListener('change', function () {
        document.getElementById('ec_gst_wrap').style.display = this.checked ? '' : 'none';
        recalcTotals();
    });

    ['ec_frame_amount', 'ec_glass_amount', 'ec_advance_amount', 'ec_other_amount', 'ec_gst_rate'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', recalcTotals);
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        try {
            loading = true; showError('');
            document.getElementById('ecSubmit').disabled = true;
            document.getElementById('ecSubmit').textContent = 'Creating…';
            var payload = {
                customer_name: document.getElementById('ec_customer_name').value,
                customer_contact: document.getElementById('ec_customer_contact').value,
                bill_date: document.getElementById('ec_bill_date').value,
                left_eye: document.getElementById('ec_left_eye').value,
                right_eye: document.getElementById('ec_right_eye').value,
                addition: document.getElementById('ec_addition').value,
                frame_amount: document.getElementById('ec_frame_amount').value,
                glass_amount: document.getElementById('ec_glass_amount').value,
                advance_amount: document.getElementById('ec_advance_amount').value,
                other_amount: document.getElementById('ec_other_amount').value,
                with_gst: document.getElementById('withGst').checked,
                gst_rate: document.getElementById('ec_gst_rate').value,
                notes: document.getElementById('ec_notes').value,
            };
            var r = await fetch('/api/admin/eye-checkup-bills', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            var d = await r.json();
            if (!r.ok) { showError(d.message || 'Failed.'); return; }
            document.getElementById('ec_customer_name').value = '';
            document.getElementById('ec_customer_contact').value = '';
            document.getElementById('ec_bill_date').value = new Date().toISOString().split('T')[0];
            document.getElementById('ec_left_eye').value = '';
            document.getElementById('ec_right_eye').value = '';
            document.getElementById('ec_addition').value = '';
            document.getElementById('ec_frame_amount').value = 0;
            document.getElementById('ec_glass_amount').value = 0;
            document.getElementById('ec_advance_amount').value = 0;
            document.getElementById('ec_other_amount').value = 0;
            document.getElementById('withGst').checked = false;
            document.getElementById('ec_gst_wrap').style.display = 'none';
            document.getElementById('ec_gst_rate').value = 0;
            document.getElementById('ec_notes').value = '';
            recalcTotals();
            await load();
        } catch (err) { showError('Failed.'); } finally {
            loading = false;
            document.getElementById('ecSubmit').disabled = false;
            document.getElementById('ecSubmit').textContent = 'Create Bill';
        }
    });

    document.getElementById('ecSelectAll').addEventListener('change', function (e) {
        document.querySelectorAll('#ecRows input[data-select]').forEach(function (c) { c.checked = e.target.checked; });
        updatePrintSelectedState();
    });
    document.getElementById('ecPrintSelected').addEventListener('click', function () {
        var ids = selectedIds();
        if (!ids.length) return;
        window.open('/admin/eye-checkup-bills/invoice-pdf?ids=' + ids.join(','), '_blank');
    });

    recalcTotals();
    load();
})();
</script>
@endverbatim
@endpush
