/* Shared helpers for the ERP admin screens. Loaded by layouts/admin.blade.php after qrcode.js.
   Usage: ERP.api('/api/admin/branches').then(function (j) { ... j.data ... })            */
(function () {
    'use strict';
    var meta = document.querySelector('meta[name="csrf-token"]');
    var csrf = meta ? meta.getAttribute('content') : '';
    var ERP = (window.ERP = window.ERP || {});

    ERP.esc = function (s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };
    ERP.inr = function (n) { return '₹' + (Number(n) || 0).toLocaleString('en-IN', { maximumFractionDigits: 2 }); };
    ERP.today = function () { var d = new Date(); return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2); };

    /** JSON fetch. Resolves with the parsed body; rejects with an Error whose .message is user-ready and .errors holds field errors. */
    ERP.api = function (url, opts) {
        opts = opts || {};
        var headers = { Accept: 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' };
        var body = opts.body;
        if (body && !(body instanceof FormData) && typeof body === 'object') {
            body = JSON.stringify(body);
            headers['Content-Type'] = 'application/json';
        }
        return fetch(url, { method: opts.method || (body ? 'POST' : 'GET'), headers: headers, body: body, credentials: 'same-origin' })
            .then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (j) {
                    if (res.status === 401) { window.location.href = '/admin/login'; }
                    if (!res.ok) {
                        var msg = j.message || 'Something went wrong.';
                        if (j.errors) { msg = Object.keys(j.errors).map(function (k) { return j.errors[k]; }).join(' '); }
                        var e = new Error(msg); e.errors = j.errors || {}; e.status = res.status; e.body = j; throw e;
                    }
                    return j;
                });
            });
    };

    /** Small dismissible banner at the top of #erpAlerts (create that div on the page), or an alert() fallback. */
    ERP.notify = function (msg, type) {
        var host = document.getElementById('erpAlerts');
        if (!host) { alert(msg); return; }
        var el = document.createElement('div');
        el.className = 'alert alert-' + (type || 'success') + ' d-flex justify-content-between align-items-center py-2';
        el.innerHTML = '<span>' + ERP.esc(msg) + '</span><button type="button" class="btn-close" aria-label="Close"></button>';
        el.querySelector('button').onclick = function () { el.remove(); };
        host.prepend(el);
        setTimeout(function () { if (el.parentNode) el.remove(); }, type === 'danger' ? 9000 : 4500);
    };
    ERP.error = function (e) { ERP.notify((e && e.message) || String(e), 'danger'); };

    // ── Branch selection (remembered across pages) ───────────────────────────
    var branchCache = null;
    ERP.branches = function () {
        if (branchCache) return Promise.resolve(branchCache);
        return ERP.api('/api/admin/branches').then(function (j) { branchCache = j.data || []; return branchCache; });
    };
    ERP.me = function () {
        return ERP.api('/api/auth/me').then(function (j) { return j.data; });
    };
    ERP.currentBranch = function () { try { return localStorage.getItem('erpBranch') || ''; } catch (e) { return ''; } };
    ERP.setBranch = function (id) { try { localStorage.setItem('erpBranch', id || ''); } catch (e) {} };

    /** Fill a <select> with branches. opts: {all:'All branches' | false, remember:true, value:id}. Branch-pinned staff only get their own. */
    ERP.fillBranchSelect = function (sel, opts) {
        opts = opts || {};
        return ERP.branches().then(function (list) {
            var active = list.filter(function (b) { return b.is_active !== false && b.is_active !== 0; });
            var html = '';
            var showAll = opts.all !== false && active.length > 1;
            if (showAll) html += '<option value="">' + ERP.esc(opts.all || 'All branches') + '</option>';
            html += active.map(function (b) { return '<option value="' + b.id + '">' + ERP.esc(b.name) + '</option>'; }).join('');
            sel.innerHTML = html;
            var want = opts.value != null ? String(opts.value) : (opts.remember === false ? '' : ERP.currentBranch());
            if (want && active.some(function (b) { return String(b.id) === want; })) sel.value = want;
            else if (!showAll && active[0]) sel.value = active[0].id;
            if (opts.remember !== false) sel.addEventListener('change', function () { ERP.setBranch(sel.value); });
            return active;
        });
    };

    // ── QR codes (self-hosted qrcode-generator) ──────────────────────────────
    /** Render text as an inline SVG QR code inside el. */
    ERP.qr = function (el, text, size) {
        if (!window.qrcode) { el.textContent = text; return; }
        var q = window.qrcode(0, 'M'); q.addData(String(text)); q.make();
        el.innerHTML = q.createSvgTag({ cellSize: Math.max(2, Math.floor((size || 160) / q.getModuleCount())), margin: 2, scalable: true });
        var svg = el.querySelector('svg');
        if (svg) { svg.style.width = (size || 160) + 'px'; svg.style.height = (size || 160) + 'px'; }
    };

    /** Printable QR labels for unit codes: ERP.printLabels([{code:'TRABC123', name:'Ray-Ban Aviator'}]) */
    ERP.printLabels = function (labels) {
        var w = window.open('', '_blank', 'width=800,height=600');
        if (!w) { ERP.notify('Allow pop-ups to print labels.', 'warning'); return; }
        var cells = labels.map(function (l) {
            var host = document.createElement('div'); ERP.qr(host, l.code, 110);
            return '<div class="l">' + host.innerHTML + '<b>' + ERP.esc(l.code) + '</b><span>' + ERP.esc(l.name || '') + '</span></div>';
        }).join('');
        w.document.write('<!doctype html><title>Labels</title><style>body{font-family:sans-serif;margin:8mm}.g{display:flex;flex-wrap:wrap;gap:4mm}.l{width:40mm;border:1px dashed #999;padding:2mm;text-align:center;page-break-inside:avoid}.l svg{width:30mm;height:30mm}.l b{display:block;font-size:9pt;letter-spacing:.05em}.l span{display:block;font-size:7pt;color:#444}</style><div class="g">' + cells + '</div><script>onload=function(){print()}<\/script>');
        w.document.close();
    };

    // ── Scanning: USB/Bluetooth scanners type into the focused input and press Enter (works as-is).
    //    ERP.scanWithCamera uses the browser BarcodeDetector (Chrome/Edge) where available. ──────────────
    ERP.canScan = function () { return 'BarcodeDetector' in window && navigator.mediaDevices && navigator.mediaDevices.getUserMedia; };
    /** Opens a camera overlay; calls onCode(text) once and closes. */
    ERP.scanWithCamera = function (onCode) {
        if (!ERP.canScan()) { ERP.notify('Camera scanning is not supported in this browser. Use a barcode scanner or type the code.', 'warning'); return; }
        var overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:2000;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;color:#fff';
        overlay.innerHTML = '<video playsinline muted style="max-width:92vw;max-height:70vh;border-radius:12px"></video><div>Point the camera at the QR code or barcode</div><button class="btn btn-light">Cancel</button>';
        document.body.appendChild(overlay);
        var video = overlay.querySelector('video'), stream, stopped = false;
        function stop() { stopped = true; if (stream) stream.getTracks().forEach(function (t) { t.stop(); }); overlay.remove(); }
        overlay.querySelector('button').onclick = stop;
        var det = new window.BarcodeDetector({ formats: ['qr_code', 'code_128', 'ean_13', 'ean_8', 'code_39', 'upc_a'] });
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(function (s) {
            stream = s; video.srcObject = s; return video.play();
        }).then(function loop() {
            if (stopped) return;
            det.detect(video).then(function (codes) {
                if (codes && codes.length) { var v = codes[0].rawValue; stop(); onCode(v); } else { requestAnimationFrame(loop); }
            }).catch(function () { requestAnimationFrame(loop); });
        }).catch(function (e) { stop(); ERP.notify('Could not open the camera: ' + e.message, 'danger'); });
    };

    /** Wire an <input> so Enter (scanner) or the camera button runs fn(code). */
    ERP.bindScan = function (input, cameraBtn, fn) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); var v = input.value.trim(); if (v) { fn(v); input.select(); } }
        });
        if (cameraBtn) cameraBtn.addEventListener('click', function () { ERP.scanWithCamera(function (v) { input.value = v; fn(v); }); });
    };

    /** Low-stock badge HTML */
    ERP.stockBadge = function (qty, low) {
        if (qty <= 0) return '<span class="badge bg-danger">Out of stock</span>';
        return low ? '<span class="badge bg-warning">Low stock</span>' : '<span class="badge bg-success">In stock</span>';
    };
    ERP.fmtDate = function (s) { if (!s) return ''; var d = new Date(String(s).replace(' ', 'T')); return isNaN(d) ? s : d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }); };
    ERP.fmtDateTime = function (s) { if (!s) return ''; var d = new Date(String(s).replace(' ', 'T')); return isNaN(d) ? s : d.toLocaleString('en-IN', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }); };
})();
