@extends('layouts.admin')
@section('title', 'Eye Test Services')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Eye Test Services</h1></div>
<div id="errBox"></div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold" id="formTitle">New Service</div>
    <div class="card-body">
        <form id="serviceForm" class="row g-3">
            <div class="col-md-5"><label class="form-label fw-semibold">Title *</label><input class="form-control" name="title" id="f_title" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Slug *</label><input class="form-control" name="slug" id="f_slug" required></div>
            <div class="col-md-1"><label class="form-label fw-semibold">Icon</label><input class="form-control" name="icon" id="f_icon" placeholder="👁️"></div>
            <div class="col-md-1"><label class="form-label fw-semibold">Sort</label><input type="number" class="form-control" name="sort_order" id="f_sort_order" value="0"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Price</label><input class="form-control" name="price" id="f_price" placeholder="e.g. Free / ₹200"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Duration</label><input class="form-control" name="duration" id="f_duration" placeholder="e.g. 30 minutes"></div>
            <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea class="form-control" rows="3" name="description" id="f_description"></textarea></div>
            <div class="col-12 d-flex align-items-center gap-3">
                <div class="form-check"><input type="checkbox" class="form-check-input" id="sActive" checked><label class="form-check-label fw-semibold" for="sActive">Active</label></div>
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
                <thead class="table-light"><tr><th>ID</th><th>Icon</th><th>Title</th><th>Price</th><th>Duration</th><th>Active</th><th>Actions</th></tr></thead>
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

    function slugify(str) { return str.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, ""); }
    function esc(s) { return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;"); }

    var form = document.getElementById("serviceForm");
    var formTitle = document.getElementById("formTitle");
    var submitBtn = document.getElementById("submitBtn");
    var cancelBtn = document.getElementById("cancelBtn");
    var errBox = document.getElementById("errBox");
    var rowsBody = document.getElementById("rowsBody");

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
        document.getElementById("f_icon").value = r ? (r.icon || "") : "";
        document.getElementById("f_sort_order").value = r ? (r.sort_order != null ? r.sort_order : 0) : 0;
        document.getElementById("f_price").value = r ? (r.price || "") : "";
        document.getElementById("f_duration").value = r ? (r.duration || "") : "";
        document.getElementById("f_description").value = r ? (r.description || "") : "";
        document.getElementById("sActive").checked = r ? (r.is_active != null ? !!r.is_active : true) : true;
        slugTouched = false;
    }

    function setEditing(r) {
        editing = r;
        formTitle.textContent = r ? ("Edit: " + r.title) : "New Service";
        submitBtn.textContent = r ? "Update" : "Create";
        cancelBtn.style.display = r ? "" : "none";
        fillForm(r);
    }

    function render() {
        if (rows.length === 0) {
            rowsBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No services yet.</td></tr>';
            return;
        }
        rowsBody.innerHTML = rows.map(function (row) {
            return '<tr>' +
                '<td>' + row.id + '</td>' +
                '<td style="font-size:1.5rem">' + esc(row.icon || "—") + '</td>' +
                '<td class="fw-semibold">' + esc(row.title) + '</td>' +
                '<td>' + esc(row.price || "—") + '</td>' +
                '<td>' + esc(row.duration || "—") + '</td>' +
                '<td><span class="badge ' + (row.is_active ? "bg-success" : "bg-secondary") + '">' + (row.is_active ? "Active" : "Off") + '</span></td>' +
                '<td><div class="d-flex gap-2">' +
                    '<button class="btn btn-sm btn-outline-primary" data-edit="' + row.id + '">Edit</button>' +
                    '<button class="btn btn-sm btn-outline-danger" data-del="' + row.id + '">Delete</button>' +
                '</div></td>' +
            '</tr>';
        }).join("");
    }

    async function load() {
        var r = await fetch("/api/admin/services");
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
            slug: document.getElementById("f_slug").value,
            icon: document.getElementById("f_icon").value,
            sort_order: Number(document.getElementById("f_sort_order").value),
            price: document.getElementById("f_price").value,
            duration: document.getElementById("f_duration").value,
            description: document.getElementById("f_description").value,
            is_active: document.getElementById("sActive").checked,
        };
        try {
            loading = true; showError(""); submitBtn.disabled = true; submitBtn.textContent = "Saving…";
            var url = editing ? ("/api/admin/services/" + editing.id) : "/api/admin/services";
            var method = editing ? "PUT" : "POST";
            var res = await fetch(url, { method: method, headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload) });
            if (!res.ok) { var dd = await res.json().catch(function () { return {}; }); throw new Error(dd.error || "Failed"); }
            setEditing(null); await load();
        } catch (err) { showError(err.message); }
        finally { loading = false; submitBtn.disabled = false; submitBtn.textContent = editing ? "Update" : "Create"; }
    });

    async function remove(id) {
        if (!window.confirm("Delete this service?")) return;
        await fetch("/api/admin/services/" + id, { method: "DELETE" });
        await load();
    }

    setEditing(null);
    load();
})();
</script>
@endverbatim
@endpush
