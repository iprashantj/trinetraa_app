@extends('layouts.admin')
@section('title', 'Gallery')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Gallery</h1></div>
<div id="errBox"></div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold" id="formTitle">New Gallery Item</div>
    <div class="card-body">
        <form id="galleryForm" class="row g-3">
            <div class="col-md-3"><label class="form-label fw-semibold">Type</label>
                <select class="form-select" id="f_type">
                    <option value="image">Image</option><option value="video">Video</option>
                </select>
            </div>
            <div class="col-md-9"><label class="form-label fw-semibold">Title *</label><input class="form-control" id="f_title" required></div>
            <div class="col-12">
                <label class="form-label fw-semibold">Image</label>
                <div class="d-flex gap-3 align-items-start">
                    <div id="galImgPreview" style="width:80px;height:80px;border:2px dashed #dee2e6;border-radius:8px;overflow:hidden;flex-shrink:0;background:#f8f9fa;display:flex;align-items:center;justify-content:center;font-size:1.5rem">🖼️</div>
                    <div class="flex-grow-1">
                        <input class="form-control mb-2" id="f_image" placeholder="Paste image URL — or upload a file">
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <label class="btn btn-sm btn-outline-secondary mb-0" for="galImgFile" style="cursor:pointer">📁 Upload Image</label>
                            <input type="file" id="galImgFile" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif" style="display:none">
                            <span id="galImgStatus" class="small text-muted"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12"><label class="form-label fw-semibold">Embed URL (video)</label><input class="form-control" id="f_embed_url"></div>
            <div class="col-md-2"><label class="form-label fw-semibold">Sort</label><input type="number" class="form-control" id="f_sort_order" value="0"></div>
            <div class="col-md-2 d-flex align-items-end"><div class="form-check mb-2"><input type="checkbox" class="form-check-input" id="gActive" checked><label class="form-check-label fw-semibold" for="gActive">Active</label></div></div>
            <div class="col-12"><button type="submit" class="btn btn-primary" id="submitBtn">Create</button></div>
        </form>
        <button class="btn btn-sm btn-secondary mt-2" id="cancelBtn" style="display:none">Cancel</button>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Type</th><th>Title</th><th>Sort</th><th>Active</th><th>Actions</th></tr></thead>
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
    var galWidget;

    function esc(s) { return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;"); }

    var form = document.getElementById("galleryForm");
    var formTitle = document.getElementById("formTitle");
    var submitBtn = document.getElementById("submitBtn");
    var cancelBtn = document.getElementById("cancelBtn");
    var errBox = document.getElementById("errBox");
    var rowsBody = document.getElementById("rowsBody");

    function showError(msg) {
        errBox.innerHTML = msg ? '<div class="alert alert-danger py-2">' + esc(msg) + '</div>' : "";
    }

    function fillForm(r) {
        document.getElementById("f_type").value = r ? (r.type || "image") : "image";
        document.getElementById("f_title").value = r ? (r.title || "") : "";
        document.getElementById("f_image").value = r ? (r.image || "") : "";
        if (galWidget) galWidget.updatePreview(r ? (r.image || "") : "");
        document.getElementById("f_embed_url").value = r ? (r.embed_url || "") : "";
        document.getElementById("f_sort_order").value = r ? (r.sort_order != null ? r.sort_order : 0) : 0;
        document.getElementById("gActive").checked = r ? (r.is_active != null ? !!r.is_active : true) : true;
    }

    function setEditing(r) {
        editing = r;
        formTitle.textContent = r ? ("Edit Item #" + r.id) : "New Gallery Item";
        submitBtn.textContent = r ? "Update" : "Create";
        cancelBtn.style.display = r ? "" : "none";
        fillForm(r);
    }

    function render() {
        if (rows.length === 0) {
            rowsBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No items yet.</td></tr>';
            return;
        }
        rowsBody.innerHTML = rows.map(function (row) {
            return '<tr>' +
                '<td>' + row.id + '</td>' +
                '<td>' + esc(row.type) + '</td>' +
                '<td>' + esc(row.title) + '</td>' +
                '<td>' + esc(row.sort_order) + '</td>' +
                '<td><span class="badge ' + (row.is_active ? "bg-success" : "bg-secondary") + '">' + (row.is_active ? "Active" : "Inactive") + '</span></td>' +
                '<td><div class="d-flex gap-2">' +
                    '<button class="btn btn-sm btn-outline-primary" data-edit="' + row.id + '">Edit</button>' +
                    '<button class="btn btn-sm btn-outline-danger" data-del="' + row.id + '">Delete</button>' +
                '</div></td>' +
            '</tr>';
        }).join("");
    }

    async function load() {
        var r = await fetch("/api/admin/gallery");
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
            type: document.getElementById("f_type").value,
            title: document.getElementById("f_title").value,
            image: document.getElementById("f_image").value,
            embed_url: document.getElementById("f_embed_url").value,
            sort_order: Number(document.getElementById("f_sort_order").value),
            is_active: document.getElementById("gActive").checked,
        };
        try {
            loading = true; showError(""); submitBtn.disabled = true; submitBtn.textContent = "Saving…";
            var url = editing ? ("/api/admin/gallery/" + editing.id) : "/api/admin/gallery";
            var method = editing ? "PUT" : "POST";
            await window.apiFetch(url, { method: method, body: JSON.stringify(payload) });
            setEditing(null); await load();
        } catch (err) { showError("Failed to save."); }
        finally { loading = false; submitBtn.disabled = false; submitBtn.textContent = editing ? "Update" : "Create"; }
    });

    async function remove(id) {
        if (!window.confirm("Delete?")) return;
        await fetch("/api/admin/gallery/" + id, { method: "DELETE" });
        await load();
    }

    document.addEventListener("DOMContentLoaded", function () {
        galWidget = window.initImageWidget({
            urlInputId:  "f_image",
            fileInputId: "galImgFile",
            previewId:   "galImgPreview",
            statusId:    "galImgStatus",
            folder:      "gallery",
            placeholder: "🖼️"
        });
        setEditing(null);
        load();
    });
})();
</script>
@endverbatim
@endpush
