@extends('layouts.admin')
@section('title', 'CRM — Contacts')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">👥 CRM — Contacts</h2>
        <button class="btn btn-primary" id="crmAddBtn">+ Add Contact</button>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <input class="form-control" id="crmSearch" placeholder="Search name, email, phone…">
        </div>
        <div class="col-md-3">
            <select class="form-select" id="crmStatusFilter">
                <option value="">All Statuses</option>
                <option value="lead">Lead</option>
                <option value="prospect">Prospect</option>
                <option value="customer">Customer</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-center">
            <small class="text-muted"><span id="crmTotal">0</span> contacts</small>
        </div>
    </div>

    <div class="row g-3">
        <!-- Contact List -->
        <div class="col-12" id="crmListCol">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div id="crmLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
                    <div id="crmList" class="list-group list-group-flush" style="display:none"></div>
                </div>
            </div>
        </div>

        <!-- Contact Detail -->
        <div class="col-md-7" id="crmDetailCol" style="display:none">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" id="crmDetailName"></h5>
                    <button type="button" class="btn-close" id="crmDetailClose"></button>
                </div>
                <div class="card-body" id="crmDetailBody">
                    <div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Contact Modal -->
    <div id="crmAddModal" class="modal" style="background:rgba(0,0,0,0.5);display:none">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Contact</h5>
                    <button type="button" class="btn-close" id="crmAddClose"></button>
                </div>
                <form id="crmAddForm">
                    <div class="modal-body">
                        <div class="mb-2"><input class="form-control" required placeholder="Name *" id="ncName"></div>
                        <div class="mb-2"><input class="form-control" type="email" placeholder="Email" id="ncEmail"></div>
                        <div class="mb-2"><input class="form-control" placeholder="Phone" id="ncPhone"></div>
                        <div class="mb-2"><input class="form-control" placeholder="Source (e.g. Walk-in, Website)" id="ncSource"></div>
                        <div class="mb-2">
                            <select class="form-select" id="ncStatus">
                                <option value="lead">Lead</option>
                                <option value="prospect">Prospect</option>
                                <option value="customer">Customer</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <textarea class="form-control" rows="2" placeholder="Notes" id="ncNotes"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="crmAddCancel">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var STATUS_CONFIG = {
        lead: { color: 'primary', label: 'Lead' },
        prospect: { color: 'info', label: 'Prospect' },
        customer: { color: 'success', label: 'Customer' },
        inactive: { color: 'secondary', label: 'Inactive' }
    };
    var ACTIVITY_TYPES = ['call', 'email', 'visit', 'whatsapp', 'note'];

    var state = { contacts: [], total: 0 };
    var selected = null;
    var detail = null;
    var searchTimer = null;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function activityIcon(type) {
        return ({ call: '📞', email: '📧', visit: '🏪', whatsapp: '💬', note: '📝' })[type] || '📌';
    }

    function statusOptions(selectedVal) {
        return Object.keys(STATUS_CONFIG).map(function (k) {
            return '<option value="' + k + '"' + (k === selectedVal ? ' selected' : '') + '>' + STATUS_CONFIG[k].label + '</option>';
        }).join('');
    }

    function renderList() {
        document.getElementById('crmTotal').textContent = state.total;
        var list = document.getElementById('crmList');
        if (state.contacts.length === 0) {
            list.innerHTML = '<div class="text-center text-muted py-4">No contacts found</div>';
            return;
        }
        list.innerHTML = state.contacts.map(function (c) {
            var cfg = STATUS_CONFIG[c.status] || {};
            var sourceLine = c.source ? '<small class="d-block text-muted">via ' + esc(c.source) + '</small>' : '';
            var active = selected && selected.id === c.id ? ' active' : '';
            return '<button class="list-group-item list-group-item-action' + active + '" data-contact="' + c.id + '">' +
                '<div class="d-flex justify-content-between align-items-start">' +
                '<div><div class="fw-semibold">' + esc(c.name) + '</div><small>' + esc(c.email || c.phone) + '</small>' + sourceLine + '</div>' +
                '<span class="badge bg-' + (cfg.color || 'secondary') + '">' + esc(cfg.label || c.status) + '</span>' +
                '</div></button>';
        }).join('');
        list.querySelectorAll('[data-contact]').forEach(function (b) {
            b.addEventListener('click', function () {
                var id = Number(b.getAttribute('data-contact'));
                var c = state.contacts.find(function (x) { return x.id === id; });
                if (c) openDetail(c);
            });
        });
    }

    function renderDetail() {
        var body = document.getElementById('crmDetailBody');
        if (!detail) {
            body.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
            return;
        }
        var notesHtml = detail.notes ? '<div class="alert alert-light mb-3 small">' + esc(detail.notes) + '</div>' : '';
        var typeOpts = ACTIVITY_TYPES.map(function (t) {
            return '<option value="' + t + '">' + activityIcon(t) + ' ' + t + '</option>';
        }).join('');
        var activities = (detail.activities || []);
        var timeline = activities.length === 0 ? '<p class="text-muted small">No activities yet</p>' :
            activities.map(function (a) {
                return '<div class="d-flex gap-2 mb-2"><span style="font-size:18px">' + activityIcon(a.type) + '</span>' +
                    '<div><div class="small fw-semibold">' + esc(a.type) + '</div>' +
                    '<div class="small">' + esc(a.description) + '</div>' +
                    '<div class="small text-muted">' + new Date(a.done_at).toLocaleString('en-IN') + '</div></div></div>';
            }).join('');

        body.innerHTML =
            '<div class="row g-2 mb-3">' +
                '<div class="col-6"><small class="text-muted d-block">Email</small><span>' + esc(detail.email || '—') + '</span></div>' +
                '<div class="col-6"><small class="text-muted d-block">Phone</small><span>' + esc(detail.phone || '—') + '</span></div>' +
                '<div class="col-6"><small class="text-muted d-block">Source</small><span>' + esc(detail.source || '—') + '</span></div>' +
                '<div class="col-6"><small class="text-muted d-block">Status</small>' +
                    '<select class="form-select form-select-sm" id="detailStatus">' + statusOptions(detail.status) + '</select></div>' +
            '</div>' +
            notesHtml +
            '<h6 class="mb-2">Add Activity</h6>' +
            '<div class="d-flex gap-2 mb-2">' +
                '<select class="form-select form-select-sm w-auto" id="actType">' + typeOpts + '</select>' +
                '<input class="form-control form-control-sm" id="actDesc" placeholder="Description…">' +
                '<button class="btn btn-sm btn-primary" id="actAdd">Add</button>' +
            '</div>' +
            '<h6 class="mb-2">Activity Timeline</h6>' +
            '<div style="max-height:240px;overflow-y:auto">' + timeline + '</div>' +
            '<div class="mt-3 pt-3 border-top">' +
                '<button class="btn btn-sm btn-outline-danger" id="detailDelete">Delete Contact</button>' +
            '</div>';

        document.getElementById('detailStatus').addEventListener('change', function (e) {
            updateStatus(detail.id, e.target.value);
            detail.status = e.target.value;
        });
        document.getElementById('actAdd').addEventListener('click', addActivity);
        document.getElementById('actDesc').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); addActivity(); }
        });
        document.getElementById('detailDelete').addEventListener('click', function () { deleteContact(detail.id); });
    }

    function load() {
        document.getElementById('crmLoading').style.display = '';
        document.getElementById('crmList').style.display = 'none';
        var qs = new URLSearchParams();
        var q = document.getElementById('crmSearch').value;
        var status = document.getElementById('crmStatusFilter').value;
        if (q) qs.set('q', q);
        if (status) qs.set('status', status);
        fetch('/api/admin/crm/contacts?' + qs.toString())
            .then(function (r) { return r.json(); })
            .then(function (json) {
                state = { contacts: json.contacts || [], total: json.total || 0 };
                document.getElementById('crmLoading').style.display = 'none';
                document.getElementById('crmList').style.display = '';
                renderList();
            });
    }

    function setLayout() {
        if (selected) {
            document.getElementById('crmListCol').className = 'col-md-5';
            document.getElementById('crmDetailCol').style.display = '';
        } else {
            document.getElementById('crmListCol').className = 'col-12';
            document.getElementById('crmDetailCol').style.display = 'none';
        }
    }

    function openDetail(contact) {
        selected = contact;
        detail = null;
        document.getElementById('crmDetailName').textContent = contact.name;
        setLayout();
        renderList();
        renderDetail();
        fetch('/api/admin/crm/contacts/' + contact.id)
            .then(function (r) { return r.json(); })
            .then(function (json) { detail = json.contact; renderDetail(); });
    }

    function closeDetail() {
        selected = null;
        detail = null;
        setLayout();
        renderList();
    }

    function addActivity() {
        var type = document.getElementById('actType').value;
        var description = document.getElementById('actDesc').value;
        if (!description) return;
        fetch('/api/admin/crm/contacts/' + selected.id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ addActivity: true, type: type, description: description })
        }).then(function () { openDetail(selected); });
    }

    function updateStatus(id, status) {
        fetch('/api/admin/crm/contacts/' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: status })
        }).then(function () { load(); });
    }

    function createContact(e) {
        e.preventDefault();
        var payload = {
            name: document.getElementById('ncName').value,
            email: document.getElementById('ncEmail').value,
            phone: document.getElementById('ncPhone').value,
            source: document.getElementById('ncSource').value,
            status: document.getElementById('ncStatus').value,
            notes: document.getElementById('ncNotes').value
        };
        fetch('/api/admin/crm/contacts', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (res) {
            if (res.ok) { closeAddModal(); load(); }
        });
    }

    function deleteContact(id) {
        if (!confirm('Delete this contact?')) return;
        fetch('/api/admin/crm/contacts/' + id, { method: 'DELETE' }).then(function () {
            closeDetail();
            load();
        });
    }

    function openAddModal() {
        document.getElementById('crmAddForm').reset();
        document.getElementById('crmAddModal').style.display = 'block';
    }
    function closeAddModal() {
        document.getElementById('crmAddModal').style.display = 'none';
    }

    document.getElementById('crmAddBtn').addEventListener('click', openAddModal);
    document.getElementById('crmAddClose').addEventListener('click', closeAddModal);
    document.getElementById('crmAddCancel').addEventListener('click', closeAddModal);
    document.getElementById('crmAddForm').addEventListener('submit', createContact);
    document.getElementById('crmDetailClose').addEventListener('click', closeDetail);
    document.getElementById('crmSearch').addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(load, 300);
    });
    document.getElementById('crmStatusFilter').addEventListener('change', load);

    load();
})();
</script>
@endverbatim
@endpush
