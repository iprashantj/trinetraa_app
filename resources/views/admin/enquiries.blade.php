@extends('layouts.admin')
@section('title', 'Enquiries')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Contact Enquiries</h1>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr></thead>
                <tbody id="contactRows">
                    <tr><td colspan="5" class="text-center py-4 text-muted">No enquiries yet.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Product Enquiries</h1>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Product</th><th>Message</th><th>Date</th></tr></thead>
                <tbody id="productRows">
                    <tr><td colspan="7" class="text-center py-4 text-muted">No product enquiries yet.</td></tr>
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
    var contactBody = document.getElementById('contactRows');
    var productBody = document.getElementById('productRows');

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function fmtDate(d) {
        if (!d) return '';
        return new Date(d).toLocaleDateString('en-IN');
    }

    fetch('/api/admin/enquiries').then(function (r) { return r.json(); }).then(function (d) {
        var rows = d.data || [];
        if (!rows.length) return;
        contactBody.innerHTML = rows.map(function (row) {
            return '<tr>' +
                '<td>' + esc(row.id) + '</td>' +
                '<td>' + esc(row.name) + '</td>' +
                '<td>' + esc(row.email) + '</td>' +
                '<td style="max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + esc(row.message) + '</td>' +
                '<td>' + fmtDate(row.created_at) + '</td>' +
                '</tr>';
        }).join('');
    });

    fetch('/api/admin/enquiries/product').then(function (r) { return r.json(); }).then(function (d) {
        var rows = d.data || [];
        if (!rows.length) return;
        productBody.innerHTML = rows.map(function (row) {
            return '<tr>' +
                '<td>' + esc(row.id) + '</td>' +
                '<td>' + esc(row.name) + '</td>' +
                '<td>' + esc(row.email) + '</td>' +
                '<td>' + esc(row.phone || '—') + '</td>' +
                '<td>' + esc(row.eyewear_name) + '</td>' +
                '<td style="max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + esc(row.message) + '</td>' +
                '<td>' + fmtDate(row.created_at) + '</td>' +
                '</tr>';
        }).join('');
    });
})();
</script>
@endverbatim
@endpush
