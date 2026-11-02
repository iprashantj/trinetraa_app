@extends('layouts.admin')
@section('title', 'Team')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Team</h1>
        <p class="text-muted small mb-0">Owner and staff accounts that can sign in to this admin panel.</p>
    </div>
    <button class="btn btn-primary" id="addMemberBtn">+ Add Member</button>
</div>

<div id="teamAlert"></div>
<div id="erpAlerts"></div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-0">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="padding-left:1.25rem">Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Branch &amp; access</th>
                    <th>MFA</th>
                    <th>Status</th>
                    <th>Added</th>
                    <th style="text-align:right;padding-right:1.25rem">Actions</th>
                </tr>
            </thead>
            <tbody id="teamRows">
                <tr><td colspan="8" class="text-center text-muted py-4">Loading…</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Add / edit form card (hidden until opened) --}}
<div class="card border-0 shadow-sm mb-4" id="memberFormCard" style="display:none;max-width:640px">
    <div class="card-header bg-white fw-semibold" id="memberFormTitle">Add Team Member</div>
    <div class="card-body">
        <form id="memberForm">
            <input type="hidden" id="mfId">
            <div class="mb-3">
                <label class="form-label fw-semibold" for="mfName">Name</label>
                <input class="form-control" id="mfName" required maxlength="255">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" for="mfEmail">Email</label>
                <input type="email" class="form-control" id="mfEmail" required maxlength="255">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" for="mfPassword">Password <span class="text-muted fw-normal" id="mfPasswordHint">(min 8 characters)</span></label>
                <input type="password" class="form-control" id="mfPassword" minlength="8" maxlength="100" autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" for="mfRole">Role</label>
                <select class="form-control" id="mfRole">
                    <option value="staff">Staff — manage content, catalog & orders</option>
                    <option value="owner">Owner — full access incl. team & audit log</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" for="mfBranch">Branch</label>
                <select class="form-control" id="mfBranch"><option value="">All branches (not tied to one branch)</option></select>
                <div class="form-text">Branch staff only see and change their own branch's stock, bills and transfers.</div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Permissions</label>
                <div class="row g-1" id="mfPerms">
                    @foreach(\App\Support\Access::PERMISSIONS as $key => $label)
                        <div class="col-sm-6"><div class="form-check"><input class="form-check-input mf-perm" type="checkbox" value="{{ $key }}" id="perm_{{ $key }}"><label class="form-check-label" for="perm_{{ $key }}">{{ $label }}</label></div></div>
                    @endforeach
                </div>
                <div class="form-text">Leave all unticked for unrestricted access (existing behaviour). Owners always have everything.</div>
            </div>
            <div class="d-flex" style="gap:.5rem">
                <button type="submit" class="btn btn-primary" id="mfSaveBtn">Save</button>
                <button type="button" class="btn btn-outline-secondary" id="mfCancelBtn">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var rows = document.getElementById('teamRows');
    var alertEl = document.getElementById('teamAlert');
    var formCard = document.getElementById('memberFormCard');
    var formTitle = document.getElementById('memberFormTitle');
    var form = document.getElementById('memberForm');
    var members = [];
    var branches = {};
    var PERM_LABELS = {};
    document.querySelectorAll('.mf-perm').forEach(function (c) { PERM_LABELS[c.value] = c.nextElementSibling.textContent; });
    ERP.branches().then(function (list) {
        var sel = document.getElementById('mfBranch');
        list.forEach(function (b) { branches[b.id] = b.name; sel.insertAdjacentHTML('beforeend', '<option value="' + b.id + '">' + esc(b.name) + '</option>'); });
        render();
    }).catch(function () {});

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function flash(kind, msg) {
        alertEl.innerHTML = '<div class="alert alert-' + kind + ' py-2">' + esc(msg) + '</div>';
        setTimeout(function () { alertEl.innerHTML = ''; }, 5000);
    }

    function accessCell(m) {
        if (m.role === 'owner') return '<span class="text-muted">All branches · everything</span>';
        var br = m.branch_id ? (branches[m.branch_id] || ('Branch #' + m.branch_id)) : 'All branches';
        var perms = Array.isArray(m.permissions) && m.permissions.length
            ? m.permissions.map(function (p) { return '<span class="badge bg-primary me-1">' + esc(PERM_LABELS[p] || p) + '</span>'; }).join('')
            : '<span class="text-muted">Unrestricted</span>';
        return '<div class="fw-semibold">' + esc(br) + '</div><div>' + perms + '</div>';
    }

    function render() {
        if (!members.length) {
            rows.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No team members yet.</td></tr>';
            return;
        }
        rows.innerHTML = members.map(function (m) {
            var added = m.created_at ? new Date(m.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
            return '<tr>' +
                '<td style="padding-left:1.25rem" class="fw-semibold">' + esc(m.name) + '</td>' +
                '<td>' + esc(m.email) + '</td>' +
                '<td><span class="badge ' + (m.role === 'owner' ? 'bg-warning text-dark' : 'bg-secondary') + '">' + esc(m.role) + '</span></td>' +
                '<td class="small">' + accessCell(m) + '</td>' +
                '<td>' + (m.totp_enabled ? '<span class="badge bg-success">On</span>' : '<span class="badge bg-secondary">Off</span>') + '</td>' +
                '<td><span class="badge ' + (m.is_active ? 'bg-success' : 'bg-danger') + '">' + (m.is_active ? 'Active' : 'Deactivated') + '</span></td>' +
                '<td class="text-muted small">' + added + '</td>' +
                '<td style="text-align:right;padding-right:1.25rem;white-space:nowrap">' +
                    '<button class="btn btn-sm btn-outline-secondary" data-edit="' + m.id + '">Edit</button> ' +
                    (m.totp_enabled ? '<button class="btn btn-sm btn-outline-secondary" data-resetmfa="' + m.id + '">Reset MFA</button> ' : '') +
                    '<button class="btn btn-sm btn-outline-secondary" data-toggle="' + m.id + '">' + (m.is_active ? 'Deactivate' : 'Activate') + '</button> ' +
                    '<button class="btn btn-sm btn-outline-danger" data-del="' + m.id + '">Delete</button>' +
                '</td>' +
            '</tr>';
        }).join('');
        bind();
    }

    function load() {
        window.apiFetch('/api/admin/team').then(function (r) {
            if (r.status === 403) { rows.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Owner access required.</td></tr>'; return null; }
            return r.json();
        }).then(function (d) { if (d) { members = d.data || []; render(); } });
    }

    function openForm(member) {
        formCard.style.display = '';
        formTitle.textContent = member ? 'Edit Team Member' : 'Add Team Member';
        document.getElementById('mfId').value = member ? member.id : '';
        document.getElementById('mfName').value = member ? member.name : '';
        document.getElementById('mfEmail').value = member ? member.email : '';
        document.getElementById('mfPassword').value = '';
        document.getElementById('mfPassword').required = !member;
        document.getElementById('mfPasswordHint').textContent = member ? '(leave empty to keep current)' : '(min 8 characters)';
        document.getElementById('mfRole').value = member ? member.role : 'staff';
        document.getElementById('mfBranch').value = member && member.branch_id ? member.branch_id : '';
        var have = member && Array.isArray(member.permissions) ? member.permissions : [];
        document.querySelectorAll('.mf-perm').forEach(function (c) { c.checked = have.indexOf(c.value) !== -1; });
        formCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        document.getElementById('mfName').focus();
    }
    function closeForm() { formCard.style.display = 'none'; }

    function bind() {
        rows.querySelectorAll('[data-edit]').forEach(function (b) {
            b.addEventListener('click', function () {
                var m = members.find(function (x) { return x.id == b.getAttribute('data-edit'); });
                if (m) openForm(m);
            });
        });
        rows.querySelectorAll('[data-toggle]').forEach(function (b) {
            b.addEventListener('click', async function () {
                var m = members.find(function (x) { return x.id == b.getAttribute('data-toggle'); });
                if (!m) return;
                var res = await window.apiFetch('/api/admin/team/' + m.id, { method: 'PUT', body: JSON.stringify({ is_active: !m.is_active }) });
                var d = await res.json();
                if (!res.ok) { flash('danger', d.message || 'Failed.'); return; }
                flash('success', (m.is_active ? 'Deactivated ' : 'Activated ') + m.name + '.');
                load();
            });
        });
        rows.querySelectorAll('[data-resetmfa]').forEach(function (b) {
            b.addEventListener('click', async function () {
                var m = members.find(function (x) { return x.id == b.getAttribute('data-resetmfa'); });
                if (!m || !confirm('Reset multi-factor authentication for ' + m.name + '? They will need to set it up again.')) return;
                var res = await window.apiFetch('/api/admin/team/' + m.id, { method: 'PUT', body: JSON.stringify({ reset_mfa: true }) });
                var d = await res.json();
                if (!res.ok) { flash('danger', d.message || 'Failed.'); return; }
                flash('success', 'MFA reset for ' + m.name + '.');
                load();
            });
        });
        rows.querySelectorAll('[data-del]').forEach(function (b) {
            b.addEventListener('click', async function () {
                var m = members.find(function (x) { return x.id == b.getAttribute('data-del'); });
                if (!m || !confirm('Delete ' + m.name + ' (' + m.email + ')? This cannot be undone.')) return;
                var res = await window.apiFetch('/api/admin/team/' + m.id, { method: 'DELETE' });
                var d = await res.json();
                if (!res.ok) { flash('danger', d.message || 'Failed.'); return; }
                flash('success', 'Team member removed.');
                load();
            });
        });
    }

    document.getElementById('addMemberBtn').addEventListener('click', function () { openForm(null); });
    document.getElementById('mfCancelBtn').addEventListener('click', closeForm);

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        var id = document.getElementById('mfId').value;
        var payload = {
            name: document.getElementById('mfName').value,
            email: document.getElementById('mfEmail').value,
            role: document.getElementById('mfRole').value,
            branch_id: document.getElementById('mfBranch').value ? Number(document.getElementById('mfBranch').value) : null,
            permissions: Array.prototype.map.call(document.querySelectorAll('.mf-perm:checked'), function (c) { return c.value; }),
        };
        if (!payload.permissions.length) payload.permissions = null; // none ticked = unrestricted
        var pw = document.getElementById('mfPassword').value;
        if (pw) payload.password = pw;
        var res = await window.apiFetch(id ? '/api/admin/team/' + id : '/api/admin/team', {
            method: id ? 'PUT' : 'POST',
            body: JSON.stringify(payload),
        });
        var d = await res.json();
        if (!res.ok) {
            var msg = d.message || 'Failed to save.';
            if (d.errors) msg = Object.values(d.errors).join(' ');
            flash('danger', msg);
            return;
        }
        flash('success', id ? 'Member updated.' : 'Member added.');
        closeForm();
        load();
    });

    load();
})();
</script>
@endverbatim
@endpush
