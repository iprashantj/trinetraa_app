@extends('layouts.admin')
@section('title', 'POS Billing')
@section('content')
<div id="erpAlerts"></div>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 mb-0">Billing</h1>
    <div class="d-flex gap-2 align-items-center">
        <label class="form-label mb-0 fw-semibold">Branch</label>
        <select class="form-select form-select-sm" id="bBranch" style="width:auto"></select>
    </div>
</div>

<div class="row g-4" id="bWrap">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">Items</div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col position-relative">
                        <input class="form-control" id="bSearch" placeholder="Scan QR / barcode, or search product…" autocomplete="off">
                        <div id="bSuggest" class="b-suggest" style="display:none"></div>
                    </div>
                    <div class="col-auto"><button type="button" class="btn btn-outline-secondary" id="bCam">📷 Scan</button></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Item</th><th style="width:110px">Price ₹</th><th style="width:100px">Disc ₹</th><th style="width:80px">Qty</th><th class="text-end">Amount</th><th style="width:40px"></th></tr></thead>
                        <tbody id="bLines"><tr><td colspan="6" class="text-center text-muted py-3">Scan or search to add items.</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">Customer</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label fw-semibold">Mobile</label><input class="form-control" id="bPhone" inputmode="tel" placeholder="Search by mobile"></div>
                    <div class="col-md-5"><label class="form-label fw-semibold">Name</label><input class="form-control" id="bName" placeholder="Walk-in customer"></div>
                    <div class="col-md-3"><label class="form-label fw-semibold">Date</label><input type="date" class="form-control" id="bDate"></div>
                    <div class="col-12" id="bCustHint"></div>
                    <div class="col-12" id="bSaveCust" style="display:none">
                        <div class="form-check"><input type="checkbox" class="form-check-input" id="bSave"><label class="form-check-label" for="bSave">Save as a new customer</label></div>
                        <div class="form-check"><input type="checkbox" class="form-check-input" id="bConsent"><label class="form-check-label" for="bConsent">Customer agrees to receive offers on WhatsApp / SMS (marketing consent)</label></div>
                    </div>
                    <div class="col-12"><input class="form-control" id="bNotes" placeholder="Notes (optional)"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">Invoice summary</div>
            <div class="card-body">
                <div id="bSummaryLines" class="mb-2 small"></div>
                <div class="row g-2 mb-3">
                    <div class="col-6"><label class="form-label small fw-semibold">Bill discount ₹</label><input type="number" min="0" step="0.01" class="form-control form-control-sm" id="bDisc" value="0"></div>
                    <div class="col-6"><label class="form-label small fw-semibold">GST %</label><input type="number" min="0" step="0.01" class="form-control form-control-sm" id="bGst" value="0"></div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-7"><label class="form-label small fw-semibold">Coupon code</label>
                        <div class="input-group input-group-sm"><input class="form-control text-uppercase" id="bCoupon" maxlength="40" autocomplete="off"><button type="button" class="btn btn-outline-secondary" id="bCouponBtn">Apply</button></div>
                        <div class="small" id="bCouponMsg"></div></div>
                    <div class="col-5"><label class="form-label small fw-semibold">Customer GSTIN</label><input class="form-control form-control-sm text-uppercase" id="bGstin" maxlength="15" placeholder="optional"></div>
                </div>
                <div class="form-check mb-2" id="bLoyaltyRow" style="display:none"><input class="form-check-input" type="checkbox" id="bLoyalty"><label class="form-check-label small" for="bLoyalty" id="bLoyaltyLbl"></label></div>
                <div class="text-success" id="bCouponLine" style="display:none;justify-content:space-between"><span>Coupon</span><strong id="bCouponAmt">−₹0.00</strong></div>
                <div class="text-success" id="bPointsLine" style="display:none;justify-content:space-between"><span>Loyalty points</span><strong id="bPointsAmt">−₹0.00</strong></div>
                <div class="d-flex justify-content-between"><span>Taxable value</span><strong id="bSub">₹0.00</strong></div>
                <div class="d-flex justify-content-between"><span>Tax</span><strong id="bTax">₹0.00</strong></div>
                <div class="d-flex justify-content-between fs-4 border-top pt-2 mt-2"><span>Payable</span><strong id="bTotal">₹0.00</strong></div>

                <div class="b-tabs mt-3" id="bTabs">
                    <button type="button" class="active" data-m="cash">Cash</button>
                    <button type="button" data-m="card">Card</button>
                    <button type="button" data-m="upi">UPI QR</button>
                </div>
                <div class="small text-muted my-2" id="bTabHint">Collect cash at the counter.</div>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="bReceived" checked><label class="form-check-label" for="bReceived">Payment received</label></div>
                <button type="button" class="btn btn-primary w-100" id="bCreate">Create bill</button>
            </div>
        </div>
    </div>
</div>

<div id="bDone" style="display:none">
    <div class="card border-0 shadow-sm" style="max-width:760px;margin:0 auto">
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                <div><div class="text-muted small">Invoice</div><h2 class="h4 mb-0" id="dNo"></h2><div class="text-muted" id="dMeta"></div></div>
                <div class="text-end"><div class="fs-3 fw-bold" id="dTotal"></div><span id="dStatus"></span></div>
            </div>
            <div class="row g-4">
                <div class="col-md-5 text-center">
                    <div id="dQr" class="d-inline-block"></div>
                    <div class="small mt-2" id="dQrMsg"></div>
                </div>
                <div class="col-md-7">
                    <div class="fw-semibold mb-2">Payment</div>
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <button class="btn btn-success btn-sm" data-pay="upi">Mark paid · UPI</button>
                        <button class="btn btn-outline-success btn-sm" data-pay="cash">Cash</button>
                        <button class="btn btn-outline-success btn-sm" data-pay="card">Card</button>
                        <button class="btn btn-outline-secondary btn-sm" id="dLink">Online card link</button>
                    </div>
                    <div id="dLinkOut" class="small mb-3"></div>
                    <div class="fw-semibold mb-2">Share invoice</div>
                    <div class="d-flex gap-2 flex-wrap mb-2">
                        <button class="btn btn-outline-success btn-sm" id="dWa">WhatsApp</button>
                        <button class="btn btn-outline-secondary btn-sm" id="dSms">SMS</button>
                        <button class="btn btn-outline-secondary btn-sm" id="dMail">Email</button>
                        <a class="btn btn-outline-primary btn-sm" id="dPdf" target="_blank">🖨️ Print / PDF</a>
                    </div>
                    <div id="dMailRow" class="input-group input-group-sm mb-2" style="display:none">
                        <input class="form-control" id="dMailTo" type="email" placeholder="customer@email.com"><button class="btn btn-primary" id="dMailSend">Send</button>
                    </div>
                </div>
            </div>
            <div class="text-end mt-3"><button class="btn btn-primary" id="dNew">New bill</button></div>
        </div>
    </div>
</div>
<style>
    .b-suggest { position:absolute; z-index:30; left:12px; right:12px; top:100%; background:#fff; border:1px solid #e8eaf0; border-radius:8px; box-shadow:0 8px 24px rgba(27,36,54,.12); max-height:280px; overflow:auto; }
    .b-suggest div { padding:.5rem .8rem; cursor:pointer; font-size:.85rem; display:flex; justify-content:space-between; gap:1rem; }
    .b-suggest div:hover { background:#f3f4f9; }
    .b-tabs { display:flex; border:1px solid #dcdfe8; border-radius:8px; overflow:hidden; }
    .b-tabs button { flex:1; border:0; background:#fff; padding:.5rem; font-weight:600; font-size:.82rem; color:#6b7a90; }
    .b-tabs button.active { background:var(--erp-primary,#3454d1); color:#fff; }
</style>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var branchSel = document.getElementById('bBranch');
    var lines = [], catalog = [], customers = [], custId = null, method = 'cash', bill = null, share = null;

    document.getElementById('bDate').value = ERP.today();

    function loadCatalog() {
        if (!branchSel.value) return;
        ERP.api('/api/admin/stock-items/lookup?branch_id=' + branchSel.value).then(function (j) { catalog = j.data || []; }).catch(ERP.error);
    }

    // ── lines ──
    function addItem(it) {
        var avail = it.branch_quantity != null ? it.branch_quantity : 0;
        if (avail < 1) { ERP.notify((it.name || 'Item') + ' is out of stock at this branch.', 'warning'); return; }
        var ex = lines.filter(function (l) { return l.stock_item_id === it.id; })[0];
        if (ex) { if (ex.quantity < ex.avail) ex.quantity++; else ERP.notify('Only ' + ex.avail + ' in stock.', 'warning'); }
        else lines.push({ stock_item_id: it.id, brand_name: it.brand || it.name, name: it.name, model_number: it.model_number || '', price: Number(it.sale_price) || 0, discount: 0, quantity: 1, avail: avail });
        renderLines();
    }
    function lineAmt(l) { return ((Number(l.price) || 0) - (Number(l.discount) || 0)) * (Number(l.quantity) || 0); }
    function renderLines() {
        var tb = document.getElementById('bLines');
        if (!lines.length) tb.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Scan or search to add items.</td></tr>';
        else tb.innerHTML = lines.map(function (l, i) {
            return '<tr><td>' + ERP.esc(l.name) + '<div class="small text-muted">' + ERP.esc(l.model_number) + ' · ' + l.avail + ' in stock</div></td>' +
                '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm" data-k="price" data-i="' + i + '" value="' + l.price + '"></td>' +
                '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm" data-k="discount" data-i="' + i + '" value="' + l.discount + '"></td>' +
                '<td><input type="number" min="1" max="' + l.avail + '" class="form-control form-control-sm" data-k="quantity" data-i="' + i + '" value="' + l.quantity + '"></td>' +
                '<td class="text-end" data-amt="' + i + '">' + ERP.inr(lineAmt(l)) + '</td>' +
                '<td><button class="btn btn-sm btn-outline-danger" data-rm="' + i + '">✕</button></td></tr>';
        }).join('');
        tb.querySelectorAll('input[data-k]').forEach(function (inp) {
            inp.addEventListener('input', function () {
                var i = +inp.getAttribute('data-i'), k = inp.getAttribute('data-k'), v = Number(inp.value) || 0;
                if (k === 'quantity') { v = Math.floor(v); if (v > lines[i].avail) { v = lines[i].avail; inp.value = v; } }
                lines[i][k] = v;
                var c = tb.querySelector('[data-amt="' + i + '"]'); if (c) c.textContent = ERP.inr(lineAmt(lines[i]));
                totals();
            });
        });
        tb.querySelectorAll('[data-rm]').forEach(function (b) { b.addEventListener('click', function () { lines.splice(+b.getAttribute('data-rm'), 1); renderLines(); }); });
        totals();
    }
    function totals() {
        var items = lines.reduce(function (s, l) { return s + lineAmt(l); }, 0);
        var disc = Number(document.getElementById('bDisc').value) || 0, gst = Number(document.getElementById('bGst').value) || 0;
        var cpn = Math.min(coupon.discount, Math.max(0, items - disc)), pts = loyalty.on ? Math.min(loyalty.value, Math.max(0, items - disc - cpn)) : 0;
        var sub = items - disc - cpn - pts, tax = sub * gst / 100;
        document.getElementById('bCouponLine').style.display = cpn > 0 ? 'flex' : 'none';
        document.getElementById('bCouponAmt').textContent = '−' + ERP.inr(cpn);
        document.getElementById('bPointsLine').style.display = pts > 0 ? 'flex' : 'none';
        document.getElementById('bPointsAmt').textContent = '−' + ERP.inr(pts);
        scheduleChecks(items - disc);
        document.getElementById('bSummaryLines').innerHTML = lines.map(function (l) {
            return '<div class="d-flex justify-content-between"><span>' + ERP.esc(l.name) + ' ×' + l.quantity + '</span><span>' + ERP.inr(lineAmt(l)) + '</span></div>';
        }).join('') || '<div class="text-muted">No items yet.</div>';
        document.getElementById('bSub').textContent = ERP.inr(sub);
        document.getElementById('bTax').textContent = ERP.inr(tax);
        document.getElementById('bTotal').textContent = ERP.inr(sub + tax);
        return { items: items, disc: disc, gst: gst, total: sub + tax };
    }
    // Coupon and loyalty are previews only; the server re-validates both when the bill is created.
    var coupon = { code: '', discount: 0 }, loyalty = { on: false, points: 0, value: 0, max_points: 0 }, checkTimer = null, lastBase = -1;
    function scheduleChecks(base) {
        if (base === lastBase) return;
        lastBase = base; clearTimeout(checkTimer);
        checkTimer = setTimeout(function () { refreshCoupon(); refreshLoyalty(); }, 350);
    }
    function refreshCoupon(announce) {
        var code = document.getElementById('bCoupon').value.trim(), msg = document.getElementById('bCouponMsg');
        if (!code) { coupon = { code: '', discount: 0 }; msg.textContent = ''; totals(); return; }
        var items = lines.reduce(function (s, l) { return s + lineAmt(l); }, 0) - (Number(document.getElementById('bDisc').value) || 0);
        ERP.api('/api/admin/coupons/check', { method: 'POST', body: { code: code, items_total: Math.max(0, items), brands: lines.map(function (l) { return l.brand_name; }) } }).then(function (j) {
            coupon = { code: j.data.code, discount: j.data.discount };
            msg.innerHTML = '<span class="text-success">Applied ' + ERP.esc(j.data.code) + ' — saves ' + ERP.inr(j.data.discount) + '</span>'; lastBase = -1; totals();
        }).catch(function (e) {
            coupon = { code: '', discount: 0 }; msg.innerHTML = '<span class="text-danger">' + ERP.esc(e.message) + '</span>'; totals();
        });
    }
    function refreshLoyalty() {
        var phone = document.getElementById('bPhone').value.trim(), row = document.getElementById('bLoyaltyRow');
        var items = lines.reduce(function (s, l) { return s + lineAmt(l); }, 0) - (Number(document.getElementById('bDisc').value) || 0) - coupon.discount;
        if (phone.replace(/\D/g, '').length < 10 || items <= 0) { row.style.display = 'none'; loyalty = { on: false, points: 0, value: 0, max_points: 0 }; return; }
        ERP.api('/api/admin/loyalty-check', { method: 'POST', body: { phone: phone, amount: items } }).then(function (j) {
            var d = j.data;
            if (!d.found || d.max_points < 1) { row.style.display = 'none'; loyalty = { on: false, points: 0, value: 0, max_points: 0 }; return; }
            row.style.display = '';
            loyalty.points = d.max_points; loyalty.value = d.max_discount; loyalty.max_points = d.max_points;
            document.getElementById('bLoyaltyLbl').textContent = 'Use ' + d.max_points + ' of ' + d.points + ' loyalty points (−' + ERP.inr(d.max_discount) + ')';
            totals();
        }).catch(function () { row.style.display = 'none'; });
    }
    document.getElementById('bCouponBtn').addEventListener('click', function () { lastBase = -1; refreshCoupon(true); });
    document.getElementById('bLoyalty').addEventListener('change', function () { loyalty.on = this.checked; totals(); });
    document.getElementById('bPhone').addEventListener('change', function () { lastBase = -1; totals(); });
    document.getElementById('bDisc').addEventListener('input', totals);
    document.getElementById('bGst').addEventListener('input', totals);

    // ── search / scan ──
    var search = document.getElementById('bSearch'), sug = document.getElementById('bSuggest');
    search.addEventListener('input', function () {
        var q = search.value.trim().toLowerCase();
        if (!q) { sug.style.display = 'none'; return; }
        var hits = catalog.filter(function (c) { return [c.name, c.brand, c.model_number, c.barcode].join(' ').toLowerCase().indexOf(q) > -1; }).slice(0, 8);
        sug.innerHTML = hits.length ? hits.map(function (c) {
            return '<div data-id="' + c.id + '"><span>' + ERP.esc((c.brand ? c.brand + ' · ' : '') + c.name + (c.model_number ? ' (' + c.model_number + ')' : '')) + '</span><span class="' + (c.branch_quantity > 0 ? 'text-muted' : 'text-danger') + '">' + ERP.inr(c.sale_price) + ' · ' + c.branch_quantity + ' in stock</span></div>';
        }).join('') : '<div class="text-muted">No matching product</div>';
        sug.style.display = '';
        sug.querySelectorAll('[data-id]').forEach(function (d) {
            d.addEventListener('click', function () {
                addItem(catalog.filter(function (c) { return String(c.id) === d.getAttribute('data-id'); })[0]);
                search.value = ''; sug.style.display = 'none'; search.focus();
            });
        });
    });
    document.addEventListener('click', function (e) { if (!sug.contains(e.target) && e.target !== search) sug.style.display = 'none'; });
    ERP.bindScan(search, document.getElementById('bCam'), function (code) {
        ERP.api('/api/admin/stock-items/scan?code=' + encodeURIComponent(code) + '&branch_id=' + branchSel.value).then(function (j) {
            var it = j.data; it.branch_quantity = (it.branch_stock && it.branch_stock[branchSel.value]) || 0;
            addItem(it); search.value = ''; sug.style.display = 'none';
        }).catch(ERP.error);
    });

    // ── customer ──
    function digits(s) { return String(s || '').replace(/\D+/g, ''); }
    ERP.api('/api/admin/customers/lookup').then(function (j) { customers = j.data || []; }).catch(function () {});
    function custCheck() {
        var d = digits(document.getElementById('bPhone').value).slice(-10), hint = document.getElementById('bCustHint'), save = document.getElementById('bSaveCust');
        custId = null; hint.innerHTML = ''; save.style.display = 'none';
        if (d.length < 10) return;
        var m = customers.filter(function (c) { return digits(c.phone).slice(-10) === d; })[0];
        if (m) { custId = m.id; document.getElementById('bName').value = m.name; hint.innerHTML = '<span class="badge bg-success">Existing customer</span> ' + ERP.esc(m.name) + ' — <a href="/admin/customers/' + m.id + '" target="_blank">view profile</a>'; }
        else { save.style.display = ''; hint.innerHTML = '<span class="badge bg-secondary">New customer</span>'; }
    }
    document.getElementById('bPhone').addEventListener('input', custCheck);

    // ── payment tabs ──
    var HINTS = { cash: 'Collect cash at the counter.', card: 'Take payment on the card machine.', upi: 'A UPI QR for the exact amount appears after you create the bill.' };
    document.querySelectorAll('#bTabs button').forEach(function (b) {
        b.addEventListener('click', function () {
            document.querySelectorAll('#bTabs button').forEach(function (x) { x.classList.remove('active'); });
            b.classList.add('active'); method = b.getAttribute('data-m');
            document.getElementById('bTabHint').textContent = HINTS[method];
            document.getElementById('bReceived').checked = method !== 'upi';
        });
    });

    // ── create ──
    document.getElementById('bCreate').addEventListener('click', async function () {
        var btn = this;
        if (!branchSel.value) { ERP.notify('Choose a branch.', 'warning'); return; }
        if (!lines.length) { ERP.notify('Add at least one item.', 'warning'); return; }
        var t = totals();
        btn.disabled = true; btn.textContent = 'Creating…';
        try {
            var phone = document.getElementById('bPhone').value.trim(), name = document.getElementById('bName').value.trim();
            if (!custId && document.getElementById('bSave').checked && name) {
                var c = await ERP.api('/api/admin/customers', { method: 'POST', body: { name: name, phone: phone, marketing_consent: document.getElementById('bConsent').checked, whatsapp_opt_in: document.getElementById('bConsent').checked } });
                custId = c.data.id;
            }
            var received = document.getElementById('bReceived').checked;
            var j = await ERP.api('/api/admin/frame-bills', { method: 'POST', body: {
                branch_id: +branchSel.value, customer_id: custId, customer_name: name || null, customer_contact: phone || null,
                bill_date: document.getElementById('bDate').value, discount_amount: t.disc, gst_rate: t.gst,
                coupon_code: coupon.code || null, loyalty_points: loyalty.on ? loyalty.points : 0,
                customer_gstin: document.getElementById('bGstin').value.trim() || null,
                payment_method: method, payment_status: received ? 'paid' : 'pending', notes: document.getElementById('bNotes').value || null,
                items: lines.map(function (l) { return { stock_item_id: l.stock_item_id, brand_name: l.brand_name, model_number: l.model_number, price: l.price, discount: l.discount, quantity: l.quantity }; })
            } });
            showDone(j.data);
        } catch (e) { ERP.error(e); }
        btn.disabled = false; btn.textContent = 'Create bill';
    });

    // ── success panel ──
    function statusBadge(s) { var m = { paid: 'success', pending: 'warning', partial: 'info', refunded: 'secondary' }; return '<span class="badge bg-' + (m[s] || 'secondary') + '">' + ERP.esc(s) + '</span>'; }
    function showDone(b) {
        bill = b; share = null;
        document.getElementById('bWrap').style.display = 'none';
        document.getElementById('bDone').style.display = '';
        document.getElementById('dNo').textContent = b.invoice_no || b.bill_number;
        document.getElementById('dMeta').textContent = (b.customer_name || 'Walk-in') + ' · ' + ERP.fmtDate(b.bill_date);
        document.getElementById('dTotal').textContent = ERP.inr(b.total);
        document.getElementById('dStatus').innerHTML = statusBadge(b.payment_status);
        document.getElementById('dPdf').href = '/admin/frame-bills/invoice-pdf?ids=' + b.id;
        document.getElementById('dLinkOut').textContent = '';
        document.getElementById('dMailRow').style.display = 'none';
        loadUpi();
        ERP.api('/api/admin/frame-bills/' + b.id + '/share').then(function (j) {
            share = j.data; if (share.email) document.getElementById('dMailTo').value = share.email;
        }).catch(ERP.error);
    }
    function loadUpi() {
        var q = document.getElementById('dQr'), msg = document.getElementById('dQrMsg');
        q.innerHTML = ''; msg.textContent = '';
        if (bill.payment_status === 'paid') { msg.innerHTML = '<span class="text-success fw-semibold">Paid ✓</span>'; return; }
        ERP.api('/api/admin/frame-bills/' + bill.id + '/upi').then(function (j) {
            ERP.qr(q, j.data.uri, 180);
            msg.innerHTML = 'Scan to pay <strong>' + ERP.inr(j.data.amount) + '</strong><br><span class="text-muted">to ' + ERP.esc(j.data.payee) + '</span>';
        }).catch(function (e) {
            msg.innerHTML = '<span class="text-muted">' + ERP.esc(e.message) + '</span>' + (e.status === 422 ? '<br><a href="/admin/settings">Open Settings</a>' : '');
        });
    }
    document.querySelectorAll('[data-pay]').forEach(function (b) {
        b.addEventListener('click', function () {
            ERP.api('/api/admin/frame-bills/' + bill.id + '/mark-paid', { method: 'POST', body: { payment_method: b.getAttribute('data-pay') } }).then(function (j) {
                bill = Object.assign(bill, j.data); document.getElementById('dStatus').innerHTML = statusBadge(bill.payment_status); loadUpi(); ERP.notify('Marked as paid.');
            }).catch(ERP.error);
        });
    });
    document.getElementById('dLink').addEventListener('click', function () {
        var out = document.getElementById('dLinkOut');
        ERP.api('/api/admin/frame-bills/' + bill.id + '/payment-link', { method: 'POST' }).then(function (j) {
            out.innerHTML = 'Payment link: <a href="' + ERP.esc(j.data.url) + '" target="_blank">' + ERP.esc(j.data.url) + '</a>';
        }).catch(function (e) { out.innerHTML = '<span class="text-muted">' + ERP.esc(e.message) + '</span>'; });
    });
    document.getElementById('dWa').addEventListener('click', function () {
        if (share && share.whatsapp_url) window.open(share.whatsapp_url, '_blank'); else ERP.notify('Add a valid customer mobile number to share on WhatsApp.', 'warning');
    });
    document.getElementById('dSms').addEventListener('click', function () {
        if (share && share.sms_url) window.location.href = share.sms_url; else ERP.notify('Add a valid customer mobile number to send an SMS.', 'warning');
    });
    document.getElementById('dMail').addEventListener('click', function () {
        var r = document.getElementById('dMailRow'); r.style.display = r.style.display === 'none' ? '' : 'none';
    });
    document.getElementById('dMailSend').addEventListener('click', function () {
        var to = document.getElementById('dMailTo').value.trim();
        ERP.api('/api/admin/frame-bills/' + bill.id + '/email', { method: 'POST', body: { to: to || null } }).then(function (j) { ERP.notify(j.message); }).catch(ERP.error);
    });
    document.getElementById('dNew').addEventListener('click', function () {
        lines = []; custId = null; bill = null; share = null;
        coupon = { code: '', discount: 0 }; loyalty = { on: false, points: 0, value: 0, max_points: 0 }; lastBase = -1;
        ['bCoupon', 'bGstin'].forEach(function (id) { document.getElementById(id).value = ''; });
        document.getElementById('bCouponMsg').textContent = ''; document.getElementById('bLoyalty').checked = false; document.getElementById('bLoyaltyRow').style.display = 'none';
        ['bPhone', 'bName', 'bNotes'].forEach(function (id) { document.getElementById(id).value = ''; });
        document.getElementById('bDisc').value = 0; document.getElementById('bGst').value = 0;
        document.getElementById('bSaveCust').style.display = 'none'; document.getElementById('bCustHint').innerHTML = '';
        document.getElementById('bDate').value = ERP.today();
        document.getElementById('bDone').style.display = 'none'; document.getElementById('bWrap').style.display = '';
        renderLines(); loadCatalog(); ERP.api('/api/admin/customers/lookup').then(function (j) { customers = j.data || []; }).catch(function () {});
        search.focus();
    });

    ERP.fillBranchSelect(branchSel, { all: false }).then(function () {
        loadCatalog(); branchSel.addEventListener('change', function () { lines = []; renderLines(); loadCatalog(); });
        search.focus();
    }).catch(ERP.error);
    renderLines();
})();
</script>
@endverbatim
@endpush
