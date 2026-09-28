@extends('layouts.admin')
@section('title', 'Offers & Promotions')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Offers &amp; Promotions</h1></div>
<div id="errBox"></div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold" id="formTitle">New Offer</div>
    <div class="card-body">
        <form id="offerForm" class="row g-3">
            <div class="col-md-8"><label class="form-label fw-semibold">Title *</label><input class="form-control" id="f_title" required></div>
            <div class="col-md-2"><label class="form-label fw-semibold">Badge</label><input class="form-control" id="f_badge" placeholder="e.g. 20% OFF"></div>
            <div class="col-md-2"><label class="form-label fw-semibold">Sort</label><input type="number" class="form-control" id="f_sort_order" value="0"></div>
            <div class="col-12">
                <label class="form-label fw-semibold">Offer Image</label>
                <div class="d-flex gap-3 align-items-start">
                    <div id="offerImgPreview" style="width:80px;height:80px;border:2px dashed #dee2e6;border-radius:8px;overflow:hidden;flex-shrink:0;background:#f8f9fa;display:flex;align-items:center;justify-content:center;font-size:1.5rem">🎁</div>
                    <div class="flex-grow-1">
                        <input class="form-control mb-2" id="f_image" placeholder="Paste image URL — or upload a file">
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <label class="btn btn-sm btn-outline-secondary mb-0" for="offerImgFile" style="cursor:pointer">📁 Upload Image</label>
                            <input type="file" id="offerImgFile" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif" style="display:none">
                            <span id="offerImgStatus" class="small text-muted"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea class="form-control" rows="3" id="f_description"></textarea></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Valid From</label><input type="date" class="form-control" id="f_valid_from"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Valid To</label><input type="date" class="form-control" id="f_valid_to"></div>
            <div class="col-12 d-flex align-items-center gap-3">
                <div class="form-check"><input type="checkbox" class="form-check-input" id="oActive" checked><label class="form-check-label fw-semibold" for="oActive">Active</label></div>
                <button type="submit" class="btn btn-primary" id="submitBtn">Create</button>
                <button type="button" class="btn btn-secondary" id="cancelBtn" style="display:none">Cancel</button>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Title</th><th>Badge</th><th>Valid To</th><th>Active</th><th>Actions</th></tr></thead>
                <tbody id="rowsBody"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var rows = [];
    var editing = null;
    var loading = false;
    var offerWidget;

    function esc(s) { return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;"); }

    var form = document.getElementById("offerForm");
    var formTitle = document.getElementById("formTitle");
    var submitBtn = document.getElementById("submitBtn");
    var cancelBtn = document.getElementById("cancelBtn");
    var errBox = document.getElementById("errBox");
    var rowsBody = document.getElementById("rowsBody");

    function showError(msg) {
        errBox.innerHTML = msg ? '<div class="alert alert-danger py-2">' + esc(msg) + '</div>' : "";
    }

    function fillForm(r) {
        document.getElementById("f_title").value = r ? (r.title || "") : "";
        document.getElementById("f_badge").value = r ? (r.badge || "") : "";
        document.getElementById("f_sort_order").value = r ? (r.sort_order != null ? r.sort_order : 0) : 0;
        document.getElementById("f_image").value = r ? (r.image || "") : "";
        if (offerWidget) offerWidget.updatePreview(r ? (r.image || "") : "");
        document.getElementById("f_description").value = r ? (r.description || "") : "";
        document.getElementById("f_valid_from").value = r && r.valid_from ? String(r.valid_from).slice(0, 10) : "";
        document.getElementById("f_valid_to").value = r && r.valid_to ? String(r.valid_to).slice(0, 10) : "";
        document.getElementById("oActive").checked = r ? (r.is_active != null ? !!r.is_active : true) : true;
    }

    function setEditing(r) {
        editing = r;
        formTitle.textContent = r ? ("Edit: " + r.title) : "New Offer";
        submitBtn.textContent = r ? "Update" : "Create";
        cancelBtn.style.display = r ? "" : "none";
        fillForm(r);
    }

    function render() {
        if (rows.length === 0) {
            rowsBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No offers yet.</td></tr>';
            return;
        }
        rowsBody.innerHTML = rows.map(function (row) {
            var validTo = row.valid_to ? new Date(row.valid_to).toLocaleDateString("en-IN") : "No expiry";
            var badge = row.badge ? '<span class="badge bg-warning text-dark">' + esc(row.badge) + '</span>' : "—";
            return '<tr>' +
                '<td>' + row.id + '</td>' +
                '<td class="fw-semibold">' + esc(row.title) + '</td>' +
                '<td>' + badge + '</td>' +
                '<td>' + esc(validTo) + '</td>' +
                '<td><span class="badge ' + (row.is_active ? "bg-success" : "bg-secondary") + '">' + (row.is_active ? "Active" : "Off") + '</span></td>' +
                '<td><div class="d-flex gap-2">' +
                    '<button class="btn btn-sm btn-outline-primary" data-edit="' + row.id + '">Edit</button>' +
                    '<button class="btn btn-sm btn-outline-danger" data-del="' + row.id + '">Delete</button>' +
                '</div></td>' +
            '</tr>';
        }).join("");
    }

    async function load() {
        var r = await fetch("/api/admin/offers");
        var d = await r.json();
        rows = d.data || [];
        render();
    }

    rowsBody.addEventListener("click", function (e) {
        var ed = e.target.getAttribute("data-edit");
        var dl = e.target.getAttribute("data-del");
        if (ed) { setEditing(rows.find(function (x) { return String(x.id) === ed; })); window.scrollTo({ top: 0, behavior: "smooth" }); }
        if (dl) remove(dl);
    });

    cancelBtn.addEventListener("click", function () { setEditing(null); });

    form.addEventListener("submit", async function (e) {
        e.preventDefault();
        if (loading) return;
        var payload = {
            title: document.getElementById("f_title").value,
            description: document.getElementById("f_description").value,
            image: document.getElementById("f_image").value,
            badge: document.getElementById("f_badge").value,
            valid_from: document.getElementById("f_valid_from").value,
            valid_to: document.getElementById("f_valid_to").value,
            sort_order: Number(document.getElementById("f_sort_order").value),
            is_active: document.getElementById("oActive").checked,
        };
        try {
            loading = true; showError(""); submitBtn.disabled = true; submitBtn.textContent = "Saving…";
            var url = editing ? ("/api/admin/offers/" + editing.id) : "/api/admin/offers";
            var method = editing ? "PUT" : "POST";
            var res = await window.apiFetch(url, { method: method, body: JSON.stringify(payload) });
            if (!res.ok) { var dd = await res.json().catch(function () { return {}; }); throw new Error(dd.error || "Failed"); }
            setEditing(null); await load();
        } catch (err) { showError(err.message); }
        finally { loading = false; submitBtn.disabled = false; submitBtn.textContent = editing ? "Update" : "Create"; }
    });

    async function remove(id) {
        if (!window.confirm("Delete this offer?")) return;
        await fetch("/api/admin/offers/" + id, { method: "DELETE" });
        await load();
    }

    document.addEventListener("DOMContentLoaded", function () {
        offerWidget = window.initImageWidget({
            urlInputId:  "f_image",
            fileInputId: "offerImgFile",
            previewId:   "offerImgPreview",
            statusId:    "offerImgStatus",
            folder:      "offers",
            placeholder: "🎁"
        });
        setEditing(null);
        load();
    });
})();
</script>
@endverbatim
@endpush
