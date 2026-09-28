@extends('layouts.admin')
@section('title', 'Appointments')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Appointments</h1></div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Phone</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="apptRows">
                    <tr><td colspan="8" class="text-center py-4 text-muted">No appointments yet.</td></tr>
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
    var STATUS_COLORS = { pending: 'bg-warning text-dark', confirmed: 'bg-primary', completed: 'bg-success', cancelled: 'bg-secondary' };
    var tbody = document.getElementById('apptRows');

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function fmtDate(d) { return d ? new Date(d).toLocaleDateString('en-IN') : ''; }

    function render(rows) {
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No appointments yet.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (row) {
            var statusCls = STATUS_COLORS[row.status] || 'bg-secondary';
            function opt(v, label) {
                return '<option value="' + v + '"' + (row.status === v ? ' selected' : '') + '>' + label + '</option>';
            }
            return '<tr>' +
                '<td>' + esc(row.id) + '</td>' +
                '<td>' + esc(row.name) + '</td>' +
                '<td>' + esc(row.phone) + '</td>' +
                '<td>' + esc(row.service) + '</td>' +
                '<td>' + fmtDate(row.appt_date) + '</td>' +
                '<td>' + esc(row.time_slot) + '</td>' +
                '<td><span class="badge ' + statusCls + '">' + esc(row.status) + '</span></td>' +
                '<td><select class="form-select form-select-sm" data-id="' + esc(row.id) + '" style="width:auto">' +
                    opt('pending', 'Pending') + opt('confirmed', 'Confirmed') + opt('completed', 'Completed') + opt('cancelled', 'Cancelled') +
                '</select></td>' +
                '</tr>';
        }).join('');

        tbody.querySelectorAll('select[data-id]').forEach(function (sel) {
            sel.addEventListener('change', function () {
                updateStatus(sel.getAttribute('data-id'), sel.value);
            });
        });
    }

    function load() {
        fetch('/api/admin/appointments').then(function (r) { return r.json(); }).then(function (d) {
            render(d.data || []);
        });
    }

    async function updateStatus(id, status) {
        await fetch('/api/admin/appointments/' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: status })
        });
        load();
    }

    load();
})();
</script>
@endverbatim
@endpush
