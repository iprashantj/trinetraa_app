@extends('layouts.admin')
@section('title', 'Blog Posts')
@section('content')
<div id="listView">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Blog Posts</h1>
        <button class="btn btn-primary" id="newPostBtn">+ New Post</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Title</th><th>Slug</th><th>Status</th><th>Published</th><th>Actions</th></tr></thead>
                    <tbody id="rowsBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="formView" style="display:none">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0" id="formHeading">New Post</h1>
        <button class="btn btn-outline-secondary btn-sm" id="backBtn">← Back to list</button>
    </div>
    <div id="errBox"></div>
    <div class="card border-0 shadow-sm"><div class="card-body">
        <form id="blogForm" class="row g-3">
            <div class="col-md-8">
                <label class="form-label fw-semibold">Title *</label>
                <input class="form-control" id="f_title" required>
            </div>
            <div class="col-md-4"><label class="form-label fw-semibold">Slug *</label><input class="form-control" id="f_slug" required></div>
            <div class="col-12">
                <label class="form-label fw-semibold">Cover Image</label>
                <div class="d-flex gap-3 align-items-start">
                    <div id="blogImgPreview" style="width:80px;height:80px;border:2px dashed #dee2e6;border-radius:8px;overflow:hidden;flex-shrink:0;background:#f8f9fa;display:flex;align-items:center;justify-content:center;font-size:1.5rem">📝</div>
                    <div class="flex-grow-1">
                        <input class="form-control mb-2" id="f_image" placeholder="Paste image URL — or upload a file">
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <label class="btn btn-sm btn-outline-secondary mb-0" for="blogImgFile" style="cursor:pointer">📁 Upload Image</label>
                            <input type="file" id="blogImgFile" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif" style="display:none">
                            <span id="blogImgStatus" class="small text-muted"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12"><label class="form-label fw-semibold">Excerpt (short summary)</label><textarea class="form-control" rows="2" id="f_excerpt"></textarea></div>
            <div class="col-12">
                <label class="form-label fw-semibold">Content * <span class="text-muted fw-normal">(supports basic HTML)</span></label>
                <textarea class="form-control font-monospace" rows="16" id="f_content" required></textarea>
            </div>
            <div class="col-md-6"><label class="form-label fw-semibold">Meta Title</label><input class="form-control" id="f_meta_title"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Meta Description</label><input class="form-control" id="f_meta_description"></div>
            <div class="col-12 d-flex align-items-center gap-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="bPublished">
                    <label class="form-check-label fw-semibold" for="bPublished">Published</label>
                </div>
                <button type="submit" class="btn btn-primary" id="submitBtn">Create Post</button>
                <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
            </div>
        </form>
    </div></div>
</div>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var rows = [];
    var editing = null;
    var loading = false;
    var blogWidget;

    function slugify(str) { return str.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, ""); }
    function esc(s) { return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;"); }

    var listView = document.getElementById("listView");
    var formView = document.getElementById("formView");
    var rowsBody = document.getElementById("rowsBody");
    var errBox = document.getElementById("errBox");
    var formHeading = document.getElementById("formHeading");
    var submitBtn = document.getElementById("submitBtn");
    var form = document.getElementById("blogForm");

    function showError(msg) {
        errBox.innerHTML = msg ? '<div class="alert alert-danger py-2">' + esc(msg) + '</div>' : "";
    }

    var slugTouched = false;
    document.getElementById("f_slug").addEventListener("input", function () { slugTouched = true; });
    document.getElementById("f_title").addEventListener("input", function (e) {
        if (!editing && !slugTouched) document.getElementById("f_slug").value = slugify(e.target.value);
    });

    function fillForm(r) {
        document.getElementById("f_title").value = r ? (r.title || "") : "";
        document.getElementById("f_slug").value = r ? (r.slug || "") : "";
        document.getElementById("f_image").value = r ? (r.image || "") : "";
        if (blogWidget) blogWidget.updatePreview(r ? (r.image || "") : "");
        document.getElementById("f_excerpt").value = r ? (r.excerpt || "") : "";
        document.getElementById("f_content").value = r ? (r.content || "") : "";
        document.getElementById("f_meta_title").value = r ? (r.meta_title || "") : "";
        document.getElementById("f_meta_description").value = r ? (r.meta_description || "") : "";
        document.getElementById("bPublished").checked = r ? (r.is_published != null ? !!r.is_published : false) : false;
        slugTouched = false;
    }

    function showList() {
        editing = null;
        formView.style.display = "none";
        listView.style.display = "";
    }

    function showForm(r) {
        editing = r;
        formHeading.textContent = r ? "Edit Post" : "New Post";
        submitBtn.textContent = r ? "Update Post" : "Create Post";
        fillForm(r);
        showError("");
        listView.style.display = "none";
        formView.style.display = "";
        window.scrollTo({ top: 0, behavior: "smooth" });
    }

    function render() {
        if (rows.length === 0) {
            rowsBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No blog posts yet.</td></tr>';
            return;
        }
        rowsBody.innerHTML = rows.map(function (row) {
            var status = row.is_published
                ? '<span class="badge bg-success">Published</span>'
                : '<span class="badge bg-warning text-dark">Draft</span>';
            var published = row.published_at ? new Date(row.published_at).toLocaleDateString("en-IN") : "—";
            return '<tr>' +
                '<td>' + row.id + '</td>' +
                '<td class="fw-semibold">' + esc(row.title) + '</td>' +
                '<td><code>' + esc(row.slug) + '</code></td>' +
                '<td>' + status + '</td>' +
                '<td>' + esc(published) + '</td>' +
                '<td><div class="d-flex gap-2">' +
                    '<button class="btn btn-sm btn-outline-primary" data-edit="' + row.id + '">Edit</button>' +
                    '<button class="btn btn-sm btn-outline-danger" data-del="' + row.id + '">Delete</button>' +
                '</div></td>' +
            '</tr>';
        }).join("");
    }

    async function load() {
        var r = await fetch("/api/admin/blog?per_page=50");
        var d = await r.json();
        rows = d.data || [];
        render();
    }

    async function startEdit(id) {
        var r = await fetch("/api/admin/blog/" + id);
        var d = await r.json();
        showForm(d.data);
    }

    rowsBody.addEventListener("click", function (e) {
        var ed = e.target.getAttribute("data-edit");
        var dl = e.target.getAttribute("data-del");
        if (ed) startEdit(ed);
        if (dl) remove(dl);
    });

    document.getElementById("newPostBtn").addEventListener("click", function () { showForm(null); });
    document.getElementById("backBtn").addEventListener("click", function () { showList(); });
    document.getElementById("cancelBtn").addEventListener("click", function () { showList(); });

    form.addEventListener("submit", async function (e) {
        e.preventDefault();
        if (loading) return;
        var payload = {
            title: document.getElementById("f_title").value,
            slug: document.getElementById("f_slug").value,
            excerpt: document.getElementById("f_excerpt").value,
            content: document.getElementById("f_content").value,
            image: document.getElementById("f_image").value,
            is_published: document.getElementById("bPublished").checked,
            meta_title: document.getElementById("f_meta_title").value,
            meta_description: document.getElementById("f_meta_description").value,
        };
        try {
            loading = true; showError(""); submitBtn.disabled = true;
            var wasEditing = editing;
            submitBtn.textContent = "Saving…";
            var url = wasEditing ? ("/api/admin/blog/" + wasEditing.id) : "/api/admin/blog";
            var method = wasEditing ? "PUT" : "POST";
            var res = await fetch(url, { method: method, headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload) });
            if (!res.ok) { var dd = await res.json().catch(function () { return {}; }); throw new Error(dd.error || "Failed"); }
            showList(); await load();
        } catch (err) { showError(err.message); }
        finally { loading = false; submitBtn.disabled = false; submitBtn.textContent = editing ? "Update Post" : "Create Post"; }
    });

    async function remove(id) {
        if (!window.confirm("Delete this post?")) return;
        await fetch("/api/admin/blog/" + id, { method: "DELETE" });
        await load();
    }

    document.addEventListener("DOMContentLoaded", function () {
        blogWidget = window.initImageWidget({
            urlInputId:  "f_image",
            fileInputId: "blogImgFile",
            previewId:   "blogImgPreview",
            statusId:    "blogImgStatus",
            folder:      "blog",
            placeholder: "📝"
        });
        load();
    });
})();
</script>
@endverbatim
@endpush
