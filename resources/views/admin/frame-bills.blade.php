@extends('layouts.admin')
@section('title', 'Frame Bills')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Frame Bills</h1></div>
<div id="erpAlerts"></div>
<div id="fbError"></div>
<div class="mb-3"><a href="/admin/billing" class="btn btn-primary btn-sm">Open POS billing (scan &amp; deduct stock)</a></div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">New Frame Bill</div>
    <div class="card-body">
        <form id="fbForm" class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Customer Name</label><input class="form-control" id="fb_customer_name"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Customer Contact</label><input class="form-control" id="fb_customer_contact"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Bill Date</label><input type="date" class="form-control" id="fb_bill_date"></div>

            <div class="col-12"><div class="fw-semibold mb-2">Items</div>
                <div id="fbItems"></div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="fbAddItem">+ Add Item</button>
            </div>

            <div class="col-md-3"><label class="form-label fw-semibold">Discount Amount ₹</label><input type="number" step="0.01" class="form-control" id="fb_discount_amount" value="0"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">GST Rate %</label><input type="number" step="0.01" class="form-control" id="fb_gst_rate" value="0"></div>
            <div class="col-12"><label class="form-label fw-semibold">Notes</label><textarea class="form-control" rows="2" id="fb_notes"></textarea></div>
            <div class="col-12 d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-primary" id="fbSubmit">Create Frame Bill</button>
                <div class="text-end">
                    <div>Items subtotal: <strong id="fbItemsSubtotal">₹0.00</strong></div>
                    <div>Subtotal (after discount): <strong id="fbSubtotal">₹0.00</strong></div>
                    <div>Tax: <strong id="fbTax">₹0.00</strong></div>
                    <div class="fs-5">Total: <strong id="fbTotal">₹0.00</strong></div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <select class="form-select form-select-sm" id="fbBranch" style="width:auto"></select>
            <input class="form-control form-control-sm" id="fbSearch" placeholder="Search bill #, customer, phone" style="width:240px">
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="fbSelectAll">
            <label class="form-check-label" for="fbSelectAll">Select all</label>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="fbPrintSelected" disabled>🖨️ Print Selected Invoices</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th style="width:2rem"></th><th>Bill #</th><th>Customer</th><th>Branch</th><th>Date</th><th class="text-end">Total ₹</th><th>Payment</th><th>Items</th><th>Actions</th></tr></thead>
                <tbody id="fbRows">
                    <tr><td colspan="9" class="text-center py-4 text-muted">Loading…</td></tr>
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
    var rows = [], loading = false, searchTimer, openShare = null;
    var branchSel = document.getElementById('fbBranch');
    var PAY = { paid: 'success', pending: 'warning', partial: 'info', refunded: 'secondary' };
    var items = [{ brand_name: '', model_number: '', price: '', discount: 0, quantity: 1 }];
    var errorEl = document.getElementById('fbError');
    var form = document.getElementById('fbForm');

    document.getElementById('fb_bill_date').value = new Date().toISOString().split('T')[0];

    function showError(msg) {
        errorEl.innerHTML = msg ? '<div class="alert alert-danger py-2">' + msg + '</div>' : '';
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function recalcTotals() {
        var itemsSubtotal = items.reduce(function (sum, it) {
            return sum + ((Number(it.price) || 0) - (Number(it.discount) || 0)) * (Number(it.quantity) || 0);
        }, 0);
        var discountAmount = Number(document.getElementById('fb_discount_amount').value) || 0;
        var gstRate = Number(document.getElementById('fb_gst_rate').value) || 0;
        var subtotal = itemsSubtotal - discountAmount;
        var tax = subtotal * gstRate / 100;
        var total = subtotal + tax;
        document.getElementById('fbItemsSubtotal').textContent = '₹' + itemsSubtotal.toFixed(2);
        document.getElementById('fbSubtotal').textContent = '₹' + subtotal.toFixed(2);
        document.getElementById('fbTax').textContent = '₹' + tax.toFixed(2);
        document.getElementById('fbTotal').textContent = '₹' + total.toFixed(2);
    }

    function renderItems() {
        var wrap = document.getElementById('fbItems');
        wrap.innerHTML = items.map(function (item, i) {
            return '<div class="row g-2 mb-2 align-items-end">' +
                '<div class="col-md-3"><input class="form-control form-control-sm" placeholder="Brand *" data-k="brand_name" data-i="' + i + '" value="' + escapeHtml(item.brand_name) + '" required></div>' +
                '<div class="col-md-2"><input class="form-control form-control-sm" placeholder="Model" data-k="model_number" data-i="' + i + '" value="' + escapeHtml(item.model_number) + '"></div>' +
                '<div class="col-md-2"><input type="number" step="0.01" class="form-control form-control-sm" placeholder="Price ₹" data-k="price" data-i="' + i + '" value="' + (item.price === '' ? '' : item.price) + '" required></div>' +
                '<div class="col-md-2"><input type="number" step="0.01" class="form-control form-control-sm" placeholder="Discount ₹" data-k="discount" data-i="' + i + '" value="' + item.discount + '"></div>' +
                '<div class="col-md-1"><input type="number" class="form-control form-control-sm" placeholder="Qty" data-k="quantity" data-i="' + i + '" value="' + item.quantity + '" min="1"></div>' +
                '<div class="col-md-2">' + (items.length > 1 ? '<button type="button" class="btn btn-sm btn-outline-danger" data-remove="' + i + '">Remove</button>' : '') + '</div>' +
                '</div>';
        }).join('');

        wrap.querySelectorAll('input[data-k]').forEach(function (inp) {
            inp.addEventListener('input', function () {
                var i = Number(inp.getAttribute('data-i'));
                var k = inp.getAttribute('data-k');
                items[i][k] = k === 'quantity' ? Number(inp.value) : inp.value;
                recalcTotals();
            });
        });
        wrap.querySelectorAll('[data-remove]').forEach(function (b) {
            b.addEventListener('click', function () {
                var i = Number(b.getAttribute('data-remove'));
                items = items.filter(function (_, idx) { return idx !== i; });
                renderItems();
                recalcTotals();
            });
        });
    }

    function selectedIds() {
        return Array.prototype.slice.call(document.querySelectorAll('#fbRows input[data-select]:checked'))
            .map(function (c) { return c.getAttribute('data-select'); });
    }

    function updatePrintSelectedState() {
        document.getElementById('fbPrintSelected').disabled = selectedIds().length === 0;
    }

    function renderRows() {
        var tbody = document.getElementById('fbRows');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No bills yet.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (row) {
            var customer = row.customer_name || (row.customer && row.customer.name) || '—';
            return '<tr>' +
                '<td><input type="checkbox" data-select="' + row.id + '"></td>' +
                '<td><code>' + escapeHtml(row.invoice_no || row.bill_number) + '</code></td>' +
                '<td>' + escapeHtml(customer) + '</td>' +
                '<td>' + escapeHtml(row.branch_name || '—') + '</td>' +
                '<td>' + new Date(row.bill_date).toLocaleDateString('en-IN') + '</td>' +
                '<td class="text-end">₹' + Number(row.total).toFixed(2) + '</td>' +
                '<td><span class="badge bg-' + (PAY[row.payment_status] || 'secondary') + '">' + escapeHtml(row.payment_status || '—') + '</span>' +
                    (row.payment_method ? ' <span class="small text-muted text-uppercase">' + escapeHtml(row.payment_method) + '</span>' : '') + '</td>' +
                '<td>' + (row.items ? row.items.length : 0) + '</td>' +
                '<td class="text-nowrap">' +
                    '<a class="btn btn-sm btn-outline-secondary me-1" target="_blank" href="/admin/frame-bills/invoice-pdf?ids=' + row.id + '">🖨️ Print</a>' +
                    '<button class="btn btn-sm btn-outline-primary me-1" data-share="' + row.id + '">Share</button>' +
                    (row.payment_status !== 'paid' && row.payment_status !== 'refunded' ? '<button class="btn btn-sm btn-outline-success me-1" data-pay="' + row.id + '">Record payment</button>' : '') +
                    '<a class="btn btn-sm btn-outline-secondary me-1" href="/admin/returns?bill=' + row.id + '">Return / Exchange</a>' +
                    '<button class="btn btn-sm btn-outline-warning me-1" data-review="' + row.id + '" title="Send the Google review link to the customer">' + (row.review_requested_at ? 'Review asked ✓' : 'Request review') + '</button>' +
                    '<button class="btn btn-sm btn-outline-danger" data-del="' + row.id + '">Delete</button>' +
                '</td>' +
                '</tr>' +
                '<tr id="sharerow-' + row.id + '" style="display:none"><td></td><td colspan="8" class="bg-light"><div class="p-2 small" data-sharebody="' + row.id + '">Loading…</div></td></tr>';
        }).join('');
        tbody.querySelectorAll('[data-del]').forEach(function (b) {
            b.addEventListener('click', function () { remove(Number(b.getAttribute('data-del'))); });
        });
        tbody.querySelectorAll('[data-share]').forEach(function (b) {
            b.addEventListener('click', function () { toggleShare(Number(b.getAttribute('data-share'))); });
        });
        tbody.querySelectorAll('[data-review]').forEach(function (b) {
            b.addEventListener('click', function () { requestReview(Number(b.getAttribute('data-review')), b); });
        });
        tbody.querySelectorAll('[data-pay]').forEach(function (b) {
            b.addEventListener('click', function () { recordPayment(Number(b.getAttribute('data-pay'))); });
        });
        tbody.querySelectorAll('[data-select]').forEach(function (c) {
            c.addEventListener('change', updatePrintSelectedState);
        });
        updatePrintSelectedState();
    }

    async function load() {
        var qs = '?per_page=100';
        if (branchSel.value) qs += '&branch_id=' + branchSel.value;
        var q = document.getElementById('fbSearch').value.trim();
        if (q) qs += '&search=' + encodeURIComponent(q);
        var r = await fetch('/api/admin/frame-bills' + qs);
        var d = await r.json();
        rows = d.data || [];
        renderRows();
    }

    async function requestReview(id, btn) {
        btn.disabled = true;
        try {
            var j = await ERP.api('/api/admin/frame-bills/' + id + '/review-request', { method: 'POST' });
            var r = j.data;
            if (r.sent) { ERP.notify('Review request sent by ' + r.channel + '.', 'success'); btn.textContent = 'Review asked ✓'; }
            else if (r.whatsapp_url) { window.open(r.whatsapp_url, '_blank'); ERP.notify('Opening WhatsApp with the review message (no automatic provider is set up).', 'info'); }
            else ERP.notify('Add a valid customer mobile number to the bill to send a review request.', 'warning');
        } catch (e) { ERP.error(e); }
        btn.disabled = false;
    }

    async function remove(id) {
        if (!window.confirm('Delete?')) return;
        await fetch('/api/admin/frame-bills/' + id, { method: 'DELETE' });
        await load();
    }

    function toggleShare(id) {
        var tr = document.getElementById('sharerow-' + id), body = tr.querySelector('[data-sharebody]');
        if (tr.style.display !== 'none') { tr.style.display = 'none'; return; }
        if (openShare && openShare !== tr) openShare.style.display = 'none';
        openShare = tr; tr.style.display = '';
        body.textContent = 'Loading…';
        ERP.api('/api/admin/frame-bills/' + id + '/share').then(function (j) {
            var sh = j.data;
            body.innerHTML =
                (sh.whatsapp_url ? '<a class="btn btn-sm btn-outline-success me-1" target="_blank" href="' + ERP.esc(sh.whatsapp_url) + '">WhatsApp</a>' : '<span class="text-muted me-2">No valid mobile for WhatsApp/SMS.</span>') +
                (sh.sms_url ? '<a class="btn btn-sm btn-outline-secondary me-1" href="' + ERP.esc(sh.sms_url) + '">SMS</a>' : '') +
                '<a class="btn btn-sm btn-outline-primary me-1" target="_blank" href="/admin/frame-bills/invoice-pdf?ids=' + id + '">PDF</a>' +
                '<span class="input-group input-group-sm d-inline-flex w-auto align-middle"><input class="form-control" style="width:200px" placeholder="email" value="' + ERP.esc(sh.email || '') + '" data-mailto="' + id + '"><button class="btn btn-outline-secondary" data-mailbtn="' + id + '">Email</button></span>' +
                ' <a class="small ms-2" target="_blank" href="' + ERP.esc(sh.pdf_url) + '">Customer link</a>';
            body.querySelector('[data-mailbtn]').addEventListener('click', function () {
                ERP.api('/api/admin/frame-bills/' + id + '/email', { method: 'POST', body: { to: body.querySelector('[data-mailto]').value.trim() || null } })
                    .then(function (r) { ERP.notify(r.message); }).catch(ERP.error);
            });
        }).catch(function (e) { body.textContent = e.message; });
    }

    function recordPayment(id) {
        var m = window.prompt('Payment received by (cash / upi / card / online):', 'cash');
        if (!m) return;
        m = m.trim().toLowerCase();
        ERP.api('/api/admin/frame-bills/' + id + '/mark-paid', { method: 'POST', body: { payment_method: m } })
            .then(function () { ERP.notify('Payment recorded.'); load(); }).catch(ERP.error);
    }

    document.getElementById('fbSearch').addEventListener('input', function () { clearTimeout(searchTimer); searchTimer = setTimeout(load, 300); });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        try {
            loading = true; showError('');
            document.getElementById('fbSubmit').disabled = true;
            document.getElementById('fbSubmit').textContent = 'Creating…';
            var payload = {
                customer_name: document.getElementById('fb_customer_name').value,
                customer_contact: document.getElementById('fb_customer_contact').value,
                bill_date: document.getElementById('fb_bill_date').value,
                discount_amount: document.getElementById('fb_discount_amount').value,
                gst_rate: document.getElementById('fb_gst_rate').value,
                notes: document.getElementById('fb_notes').value,
                items: items,
            };
            var r = await fetch('/api/admin/frame-bills', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            var d = await r.json();
            if (!r.ok) { showError(d.message || 'Failed.'); return; }
            items = [{ brand_name: '', model_number: '', price: '', discount: 0, quantity: 1 }];
            document.getElementById('fb_customer_name').value = '';
            document.getElementById('fb_customer_contact').value = '';
            document.getElementById('fb_bill_date').value = new Date().toISOString().split('T')[0];
            document.getElementById('fb_discount_amount').value = 0;
            document.getElementById('fb_gst_rate').value = 0;
            document.getElementById('fb_notes').value = '';
            renderItems();
            recalcTotals();
            await load();
        } catch (err) { showError('Failed to create bill.'); } finally {
            loading = false;
            document.getElementById('fbSubmit').disabled = false;
            document.getElementById('fbSubmit').textContent = 'Create Frame Bill';
        }
    });

    document.getElementById('fbAddItem').addEventListener('click', function () {
        items.push({ brand_name: '', model_number: '', price: '', discount: 0, quantity: 1 });
        renderItems();
        recalcTotals();
    });

    document.getElementById('fb_discount_amount').addEventListener('input', recalcTotals);
    document.getElementById('fb_gst_rate').addEventListener('input', recalcTotals);

    document.getElementById('fbSelectAll').addEventListener('change', function (e) {
        document.querySelectorAll('#fbRows input[data-select]').forEach(function (c) { c.checked = e.target.checked; });
        updatePrintSelectedState();
    });
    document.getElementById('fbPrintSelected').addEventListener('click', function () {
        var ids = selectedIds();
        if (!ids.length) return;
        window.open('/admin/frame-bills/invoice-pdf?ids=' + ids.join(','), '_blank');
    });

    renderItems();
    recalcTotals();
    ERP.fillBranchSelect(branchSel, { all: 'All branches' }).then(function () {
        branchSel.addEventListener('change', load);
        load();
    }).catch(load);
})();
</script>
@endverbatim
@endpush
