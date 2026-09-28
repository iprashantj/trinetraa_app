@extends('layouts.admin')
@section('title', 'Reviews')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Reviews</h1>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Email</th><th>Rating</th><th>Review</th><th>Approved</th><th>Date</th><th>Actions</th></tr></thead>
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

    function esc(s) { return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;"); }
    function stars(n) {
        n = Number(n) || 0;
        var full = n > 0 ? n : 0;
        if (full > 5) full = 5;
        return "★".repeat(full) + "☆".repeat(5 - full);
    }

    var rowsBody = document.getElementById("rowsBody");

    function render() {
        if (rows.length === 0) {
            rowsBody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No reviews yet.</td></tr>';
            return;
        }
        rowsBody.innerHTML = rows.map(function (row) {
            var approved = row.is_approved
                ? '<span class="badge bg-success">Approved</span>'
                : '<span class="badge bg-warning text-dark">Pending</span>';
            var date = row.created_at ? new Date(row.created_at).toLocaleDateString("en-IN") : "";
            var approveBtn = !row.is_approved
                ? '<button class="btn btn-sm btn-outline-success" data-approve="' + row.id + '">Approve</button>'
                : "";
            return '<tr>' +
                '<td>' + row.id + '</td>' +
                '<td>' + esc(row.user_name) + '</td>' +
                '<td>' + esc(row.user_email) + '</td>' +
                '<td>' + stars(row.rating) + '</td>' +
                '<td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + esc(row.review_text) + '</td>' +
                '<td>' + approved + '</td>' +
                '<td>' + esc(date) + '</td>' +
                '<td><div class="d-flex gap-2">' + approveBtn +
                    '<button class="btn btn-sm btn-outline-danger" data-del="' + row.id + '">Delete</button>' +
                '</div></td>' +
            '</tr>';
        }).join("");
    }

    async function load() {
        var r = await fetch("/api/admin/reviews");
        var d = await r.json();
        rows = d.data || [];
        render();
    }

    rowsBody.addEventListener("click", function (e) {
        var ap = e.target.getAttribute("data-approve");
        var dl = e.target.getAttribute("data-del");
        if (ap) approve(ap);
        if (dl) remove(dl);
    });

    async function approve(id) {
        await fetch("/api/admin/reviews/" + id + "/approve", { method: "POST" });
        await load();
    }

    async function remove(id) {
        if (!window.confirm("Delete this review?")) return;
        await fetch("/api/admin/reviews/" + id, { method: "DELETE" });
        await load();
    }

    load();
})();
</script>
@endverbatim
@endpush
