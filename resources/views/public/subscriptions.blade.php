@extends('layouts.public')
@section('title', 'My Lens Subscriptions')
@section('content')

<div id="subsRoot">
    {{-- Loading state (replaced by JS) --}}
    <div style="min-height:60vh;display:flex;align-items:center;justify-content:center">
        <div style="text-align:center"><div style="font-size:40px">🔄</div><p style="color:#9FB1C7">Loading…</p></div>
    </div>
</div>

@endsection

@push('scripts')
@verbatim
<script>
(function () {
  var INTERVALS = [
    { value: 30, label: "Monthly (every 30 days)" },
    { value: 60, label: "Every 2 months" },
    { value: 90, label: "Every 3 months" },
  ];

  var state = {
    subs: [],
    showForm: false,
    saving: false,
    msg: "",
    form: { lens_name: "", brand: "", power: "", interval_days: 30, address_line: "", notes: "" },
  };

  var root = document.getElementById("subsRoot");

  function esc(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
  }

  function daysUntil(date) {
    return Math.ceil((new Date(date) - new Date()) / (1000 * 60 * 60 * 24));
  }

  function load() {
    fetch("/api/public/subscriptions")
      .then(function (r) {
        if (r.status === 401) { renderNotLoggedIn(); return null; }
        return r.json();
      })
      .then(function (d) {
        if (!d) return;
        state.subs = d.subs || [];
        renderMain();
      });
  }

  function renderNotLoggedIn() {
    root.innerHTML =
      '<div style="min-height:60vh;display:flex;align-items:center;justify-content:center">' +
        '<div style="text-align:center;max-width:400px">' +
          '<div style="font-size:60px;margin-bottom:16px">🔄</div>' +
          '<h2 style="color:#F8FAFC">Lens Subscription</h2>' +
          '<p style="color:#9FB1C7;margin-bottom:24px">Sign in to manage your contact lens subscriptions</p>' +
          '<a href="/account/login?redirect=/subscriptions" class="pp-btn pp-btn-primary">Sign In</a>' +
        '</div>' +
      '</div>';
  }

  function renderMain() {
    var html = '<div style="max-width:800px;margin:0 auto;padding:40px 16px">';

    // Header
    html +=
      '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">' +
        '<div>' +
          '<h1 style="color:#F8FAFC;margin:0">🔄 My Lens Subscriptions</h1>' +
          '<p style="color:#9FB1C7;margin-top:4px">Never run out of contact lenses again</p>' +
        '</div>' +
        '<button data-new-sub style="background:#1a3a5c;color:#fff;border:none;border-radius:8px;padding:10px 20px;cursor:pointer;font-weight:600">+ New Subscription</button>' +
      '</div>';

    // Message
    if (state.msg) {
      html += '<div style="background:#e8f5e9;color:#2e7d32;padding:10px 16px;border-radius:8px;margin-bottom:16px">' + esc(state.msg) + '</div>';
    }

    // Empty state
    if (state.subs.length === 0 && !state.showForm) {
      html +=
        '<div style="text-align:center;background:rgba(255,255,255,0.06);border-radius:12px;padding:48px 24px">' +
          '<div style="font-size:60px">👁️</div>' +
          '<h3 style="color:#F8FAFC">No subscriptions yet</h3>' +
          '<p style="color:#9FB1C7;margin-bottom:20px">Set up automatic lens reminders so we notify you when it\'s time to reorder</p>' +
          '<button data-first-sub style="background:#1a3a5c;color:#fff;border:none;border-radius:8px;padding:12px 28px;cursor:pointer;font-weight:600">Set Up First Subscription</button>' +
        '</div>';
    }

    // Form
    if (state.showForm) {
      var f = state.form;
      var intervalOpts = INTERVALS.map(function (i) {
        return '<option value="' + i.value + '"' + (Number(f.interval_days) === i.value ? ' selected' : '') + '>' + esc(i.label) + '</option>';
      }).join("");

      html +=
        '<div style="background:rgba(255,255,255,0.06);border-radius:12px;padding:24px;margin-bottom:24px">' +
          '<h3 style="color:#F8FAFC;margin-bottom:16px">New Lens Subscription</h3>' +
          '<form id="subForm">' +
            '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">' +
              '<div>' +
                '<label style="display:block;margin-bottom:4px;font-weight:500">Lens Name *</label>' +
                '<input required name="lens_name" value="' + esc(f.lens_name) + '" style="width:100%;padding:8px 12px;border:1px solid rgba(255,255,255,0.2);border-radius:6px" placeholder="e.g. Acuvue Oasys">' +
              '</div>' +
              '<div>' +
                '<label style="display:block;margin-bottom:4px;font-weight:500">Brand</label>' +
                '<input name="brand" value="' + esc(f.brand) + '" style="width:100%;padding:8px 12px;border:1px solid rgba(255,255,255,0.2);border-radius:6px" placeholder="e.g. Johnson & Johnson">' +
              '</div>' +
              '<div>' +
                '<label style="display:block;margin-bottom:4px;font-weight:500">Power</label>' +
                '<input name="power" value="' + esc(f.power) + '" style="width:100%;padding:8px 12px;border:1px solid rgba(255,255,255,0.2);border-radius:6px" placeholder="e.g. -2.50">' +
              '</div>' +
              '<div>' +
                '<label style="display:block;margin-bottom:4px;font-weight:500">Reminder Interval *</label>' +
                '<select required name="interval_days" style="width:100%;padding:8px 12px;border:1px solid rgba(255,255,255,0.2);border-radius:6px">' + intervalOpts + '</select>' +
              '</div>' +
              '<div style="grid-column:span 2">' +
                '<label style="display:block;margin-bottom:4px;font-weight:500">Delivery Address</label>' +
                '<input name="address_line" value="' + esc(f.address_line) + '" style="width:100%;padding:8px 12px;border:1px solid rgba(255,255,255,0.2);border-radius:6px" placeholder="Your delivery address">' +
              '</div>' +
            '</div>' +
            '<div style="display:flex;gap:12px;margin-top:16px">' +
              '<button type="submit"' + (state.saving ? ' disabled' : '') + ' style="background:#1a3a5c;color:#fff;border:none;border-radius:8px;padding:10px 24px;cursor:pointer;font-weight:600">' + (state.saving ? "Saving…" : "Create Subscription") + '</button>' +
              '<button type="button" data-cancel style="background:rgba(255,255,255,0.12);color:#F8FAFC;border:none;border-radius:8px;padding:10px 24px;cursor:pointer">Cancel</button>' +
            '</div>' +
          '</form>' +
        '</div>';
    }

    // Subscription cards
    html += '<div style="display:flex;flex-direction:column;gap:16px">';
    state.subs.forEach(function (s) {
      var days = daysUntil(s.next_due_date);
      var statusColor = s.status === "active" ? "#2e7d32" : s.status === "paused" ? "#e65100" : "#c62828";
      var dueColor = days <= 0 ? "#c62828" : days <= 7 ? "#e65100" : "#333";
      var dueStr = "";
      try { dueStr = new Date(s.next_due_date).toLocaleDateString("en-IN"); } catch (e) {}
      var ordersCount = (s.orders || []).length;

      html +=
        '<div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.14);border-radius:12px;padding:20px;box-shadow:0 1px 6px rgba(0,0,0,0.35)">' +
          '<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px">' +
            '<div>' +
              '<div style="font-weight:700;color:#F8FAFC;font-size:18px">' + esc(s.lens_name) + '</div>' +
              (s.brand ? '<div style="color:#9FB1C7;font-size:14px">' + esc(s.brand) + '</div>' : '') +
              (s.power ? '<div style="color:#9FB1C7;font-size:14px">Power: ' + esc(s.power) + '</div>' : '') +
            '</div>' +
            '<span style="background:' + statusColor + '22;color:' + statusColor + ';padding:4px 12px;border-radius:20px;font-size:13px;font-weight:600">' + esc(s.status) + '</span>' +
          '</div>' +
          '<div style="margin-top:12px;display:flex;gap:24px;flex-wrap:wrap">' +
            '<div>' +
              '<div style="font-size:12px;color:rgba(255,255,255,0.45)">Next Due</div>' +
              '<div style="font-weight:600;color:' + dueColor + '">' + esc(dueStr) +
                '<span style="font-size:12px;margin-left:6px">(' + (days <= 0 ? "Overdue!" : (days + " days")) + ')</span>' +
              '</div>' +
            '</div>' +
            '<div>' +
              '<div style="font-size:12px;color:rgba(255,255,255,0.45)">Interval</div>' +
              '<div style="font-weight:600">Every ' + esc(s.interval_days) + ' days</div>' +
            '</div>' +
            '<div>' +
              '<div style="font-size:12px;color:rgba(255,255,255,0.45)">Orders</div>' +
              '<div style="font-weight:600">' + ordersCount + ' fulfilled</div>' +
            '</div>' +
          '</div>' +
        '</div>';
    });
    html += '</div>';

    html += '</div>';
    root.innerHTML = html;
    wire();
  }

  function wire() {
    var newBtn = root.querySelector("[data-new-sub]");
    if (newBtn) newBtn.addEventListener("click", function () { state.showForm = true; renderMain(); });

    var firstBtn = root.querySelector("[data-first-sub]");
    if (firstBtn) firstBtn.addEventListener("click", function () { state.showForm = true; renderMain(); });

    var cancelBtn = root.querySelector("[data-cancel]");
    if (cancelBtn) cancelBtn.addEventListener("click", function () { state.showForm = false; renderMain(); });

    var form = document.getElementById("subForm");
    if (form) {
      // keep form state on input so a re-render preserves values
      form.addEventListener("input", function (e) {
        var name = e.target.name;
        if (!name) return;
        state.form[name] = name === "interval_days" ? Number(e.target.value) : e.target.value;
      });
      form.addEventListener("submit", submit);
    }
  }

  function submit(e) {
    e.preventDefault();
    state.saving = true;
    renderMain();
    fetch("/api/public/subscriptions", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(state.form),
    })
      .then(function (res) {
        return res.json().then(function (data) { return { ok: res.ok, data: data }; });
      })
      .then(function (r) {
        state.saving = false;
        if (r.ok) {
          state.msg = "Subscription created!";
          state.showForm = false;
          state.form = { lens_name: "", brand: "", power: "", interval_days: 30, address_line: "", notes: "" };
          load();
        } else {
          state.msg = (r.data && r.data.message) || "Error";
          renderMain();
        }
      })
      .catch(function () {
        state.saving = false;
        state.msg = "Error";
        renderMain();
      });
  }

  document.addEventListener("DOMContentLoaded", load);
})();
</script>
@endverbatim
@endpush
