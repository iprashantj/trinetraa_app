@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Orders</h1></div>
<div id="ordError"></div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">New Order</div>
    <div class="card-body">
        <form id="ordForm" class="row g-3">
            <div class="col-md-3"><label class="form-label fw-semibold">Type *</label>
                <select class="form-select" id="ord_type">
                    <option value="sale">Sale</option>
                    <option value="purchase">Purchase</option>
                </select>
            </div>
            <div class="col-md-4"><label class="form-label fw-semibold">Customer</label>
                <select class="form-select" id="ord_customer_id">
                    <option value="">None</option>
                </select>
            </div>
            <div class="col-md-3"><label class="form-label fw-semibold">Order Date *</label><input type="date" class="form-control" id="ord_order_date"></div>
            <div class="col-md-2"><label class="form-label fw-semibold">Tax ₹</label><input type="number" step="0.01" class="form-control" id="ord_tax" value="0"></div>

            <div class="col-12"><div class="fw-semibold mb-2">Items</div>
                <div id="ordItems"></div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="ordAddItem">+ Add Item</button>
            </div>

            <div class="col-12"><label class="form-label fw-semibold">Notes</label><textarea class="form-control" rows="2" id="ord_notes"></textarea></div>
            <div class="col-12 d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-primary" id="ordSubmit">Create Order</button>
                <div class="text-end">
                    <div>Subtotal: <strong id="ordSubtotal">₹0.00</strong></div>
                    <div>Tax: <strong id="ordTaxDisp">₹0.00</strong></div>
                    <div class="fs-5">Total: <strong id="ordTotal">₹0.00</strong></div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Order #</th><th>Type</th><th>Customer</th><th>Date</th><th class="text-end">Total</th><th>Items</th><th>Actions</th></tr></thead>
                <tbody id="ordRows">
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
    var rows = [], stockItems = [], customers = [], loading = false;
    var items = [{ stock_item_id: '', quantity: 1, unit_price: '' }];
    var errorEl = document.getElementById('ordError');
    var form = document.getElementById('ordForm');

    document.getElementById('ord_order_date').value = new Date().toISOString().split('T')[0];

    function showError(msg) {
        errorEl.innerHTML = msg ? '<div class="alert alert-danger py-2">' + msg + '</div>' : '';
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function stockOptions(selected) {
        return '<option value="">Select item…</option>' + stockItems.map(function (s) {
            var label = s.name + (s.model_number ? ' (' + s.model_number + ')' : '') + ' — ₹' + Number(s.sale_price).toFixed(2) + ' / stock ' + s.current_stock;
            return '<option value="' + s.id + '"' + (String(s.id) === String(selected) ? ' selected' : '') + '>' + escapeHtml(label) + '</option>';
        }).join('');
    }

    function customerOptions() {
        document.getElementById('ord_customer_id').innerHTML = '<option value="">None</option>' + customers.map(function (c) {
            return '<option value="' + c.id + '">' + escapeHtml(c.name + (c.phone ? ' (' + c.phone + ')' : '')) + '</option>';
        }).join('');
    }

    function recalcTotals() {
        var subtotal = items.reduce(function (sum, it) {
            return sum + (Number(it.unit_price) || 0) * (Number(it.quantity) || 0);
        }, 0);
        var tax = Number(document.getElementById('ord_tax').value) || 0;
        var total = subtotal + tax;
        document.getElementById('ordSubtotal').textContent = '₹' + subtotal.toFixed(2);
        document.getElementById('ordTaxDisp').textContent = '₹' + tax.toFixed(2);
        document.getElementById('ordTotal').textContent = '₹' + total.toFixed(2);
    }

    function renderItems() {
        var wrap = document.getElementById('ordItems');
        wrap.innerHTML = items.map(function (item, i) {
            return '<div class="row g-2 mb-2 align-items-end" data-item="' + i + '">' +
                '<div class="col-md-5"><select class="form-select form-select-sm" data-k="stock_item_id" data-i="' + i + '">' + stockOptions(item.stock_item_id) + '</select></div>' +
                '<div class="col-md-2"><input type="number" step="0.01" class="form-control form-control-sm" placeholder="Unit Price ₹" data-k="unit_price" data-i="' + i + '" value="' + (item.unit_price === '' ? '' : item.unit_price) + '" required></div>' +
                '<div class="col-md-2"><input type="number" class="form-control form-control-sm" placeholder="Qty" data-k="quantity" data-i="' + i + '" value="' + item.quantity + '" min="1"></div>' +
                '<div class="col-md-2"><span class="text-muted small">₹' + ((Number(item.unit_price) || 0) * (Number(item.quantity) || 0)).toFixed(2) + '</span></div>' +
                '<div class="col-md-1">' + (items.length > 1 ? '<button type="button" class="btn btn-sm btn-outline-danger" data-remove="' + i + '">Remove</button>' : '') + '</div>' +
                '</div>';
        }).join('');

        wrap.querySelectorAll('select[data-k="stock_item_id"]').forEach(function (sel) {
            sel.addEventListener('change', function () {
                var i = Number(sel.getAttribute('data-i'));
                items[i].stock_item_id = sel.value;
                var picked = stockItems.find(function (s) { return String(s.id) === String(sel.value); });
                if (picked && (items[i].unit_price === '' || items[i].unit_price == null)) {
                    items[i].unit_price = Number(picked.sale_price);
                }
                renderItems();
                recalcTotals();
            });
        });
        wrap.querySelectorAll('input[data-k]').forEach(function (inp) {
            inp.addEventListener('input', function () {
                var i = Number(inp.getAttribute('data-i'));
                var k = inp.getAttribute('data-k');
                items[i][k] = k === 'quantity' ? Number(inp.value) : inp.value;
                renderItems();
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

    function renderRows() {
        var tbody = document.getElementById('ordRows');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No orders yet.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (row) {
            return '<tr>' +
                '<td><code>' + escapeHtml(row.order_number) + '</code></td>' +
                '<td>' + escapeHtml(row.type) + '</td>' +
                '<td>' + (row.customer && row.customer.name ? escapeHtml(row.customer.name) : '—') + '</td>' +
                '<td>' + new Date(row.order_date).toLocaleDateString('en-IN') + '</td>' +
                '<td class="text-end">₹' + Number(row.total).toFixed(2) + '</td>' +
                '<td>' + (row.items ? row.items.length : 0) + '</td>' +
                '<td><button class="btn btn-sm btn-outline-danger" data-del="' + row.id + '">Delete</button></td>' +
                '</tr>';
        }).join('');
        tbody.querySelectorAll('[data-del]').forEach(function (b) {
            b.addEventListener('click', function () { remove(Number(b.getAttribute('data-del'))); });
        });
    }

    async function load() {
        var r = await fetch('/api/admin/orders');
        var d = await r.json();
        rows = d.data || [];
        renderRows();
    }

    async function remove(id) {
        if (!window.confirm('Delete this order?')) return;
        await fetch('/api/admin/orders/' + id, { method: 'DELETE' });
        await load();
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        try {
            loading = true; showError('');
            document.getElementById('ordSubmit').disabled = true;
            document.getElementById('ordSubmit').textContent = 'Creating…';
            var payload = {
                type: document.getElementById('ord_type').value,
                customer_id: document.getElementById('ord_customer_id').value || null,
                order_date: document.getElementById('ord_order_date').value,
                tax: Number(document.getElementById('ord_tax').value) || 0,
                notes: document.getElementById('ord_notes').value,
                items: items.map(function (it) {
                    return {
                        stock_item_id: Number(it.stock_item_id),
                        quantity: Number(it.quantity),
                        unit_price: Number(it.unit_price),
                    };
                }),
            };
            var r = await fetch('/api/admin/orders', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            var d = await r.json();
            if (!r.ok) { showError(d.message || 'Failed.'); return; }
            items = [{ stock_item_id: '', quantity: 1, unit_price: '' }];
            document.getElementById('ord_type').value = 'sale';
            document.getElementById('ord_customer_id').value = '';
            document.getElementById('ord_order_date').value = new Date().toISOString().split('T')[0];
            document.getElementById('ord_tax').value = 0;
            document.getElementById('ord_notes').value = '';
            renderItems();
            recalcTotals();
            await load();
        } catch (err) { showError('Failed to create order.'); } finally {
            loading = false;
            document.getElementById('ordSubmit').disabled = false;
            document.getElementById('ordSubmit').textContent = 'Create Order';
        }
    });

    document.getElementById('ordAddItem').addEventListener('click', function () {
        items.push({ stock_item_id: '', quantity: 1, unit_price: '' });
        renderItems();
        recalcTotals();
    });

    document.getElementById('ord_tax').addEventListener('input', recalcTotals);

    renderItems();
    recalcTotals();
    load();
    fetch('/api/admin/stock-items/lookup').then(function (r) { return r.json(); }).then(function (d) { stockItems = d.data || []; renderItems(); });
    fetch('/api/admin/customers/lookup').then(function (r) { return r.json(); }).then(function (d) { customers = d.data || []; customerOptions(); });
})();
</script>
@endverbatim
@endpush
