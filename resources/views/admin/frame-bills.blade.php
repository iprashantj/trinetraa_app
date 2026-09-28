@extends('layouts.admin')
@section('title', 'Frame Bills')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Frame Bills</h1></div>
<div id="fbError"></div>

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
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="fbSelectAll">
            <label class="form-check-label" for="fbSelectAll">Select all</label>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="fbPrintSelected" disabled>🖨️ Print Selected Invoices</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th style="width:2rem"></th><th>Bill #</th><th>Customer</th><th>Date</th><th class="text-end">Total ₹</th><th>Items</th><th>Actions</th></tr></thead>
                <tbody id="fbRows">
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
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No bills yet.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (row) {
            var customer = row.customer_name || (row.customer && row.customer.name) || '—';
            return '<tr>' +
                '<td><input type="checkbox" data-select="' + row.id + '"></td>' +
                '<td><code>' + escapeHtml(row.bill_number) + '</code></td>' +
                '<td>' + escapeHtml(customer) + '</td>' +
                '<td>' + new Date(row.bill_date).toLocaleDateString('en-IN') + '</td>' +
                '<td class="text-end">₹' + Number(row.total).toFixed(2) + '</td>' +
                '<td>' + (row.items ? row.items.length : 0) + '</td>' +
                '<td>' +
                    '<a class="btn btn-sm btn-outline-secondary me-1" target="_blank" href="/admin/frame-bills/invoice-pdf?ids=' + row.id + '">🖨️ Print</a>' +
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
        var r = await fetch('/api/admin/frame-bills');
        var d = await r.json();
        rows = d.data || [];
        renderRows();
    }

    async function remove(id) {
        if (!window.confirm('Delete?')) return;
        await fetch('/api/admin/frame-bills/' + id, { method: 'DELETE' });
        await load();
    }

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
    load();
})();
</script>
@endverbatim
@endpush
