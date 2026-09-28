@extends('layouts.public')
@section('title', 'Loyalty Rewards')
@section('content')

<div id="loyaltyRoot">
    {{-- Loading state (replaced by JS) --}}
    <div style="min-height:60vh;display:flex;align-items:center;justify-content:center">
        <div style="text-align:center">
            <div style="font-size:40px;margin-bottom:16px">🏆</div>
            <p style="color:#9FB1C7">Loading your loyalty account…</p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@verbatim
<script>
(function () {
  var TIERS = {
    Bronze: { min: 0, max: 1999, color: "#cd7f32", icon: "🥉", next: "Silver", nextAt: 2000 },
    Silver: { min: 2000, max: 4999, color: "#9e9e9e", icon: "🥈", next: "Gold", nextAt: 5000 },
    Gold: { min: 5000, max: Infinity, color: "#ffc107", icon: "🥇", next: null, nextAt: null },
  };

  function esc(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
  }

  function nfmt(n) { return Number(n || 0).toLocaleString(); }

  var root = document.getElementById("loyaltyRoot");

  function renderNotLoggedIn() {
    root.innerHTML =
      '<div style="min-height:60vh;display:flex;align-items:center;justify-content:center">' +
        '<div style="text-align:center;max-width:400px">' +
          '<div style="font-size:60px;margin-bottom:16px">🏆</div>' +
          '<h2 style="color:#F8FAFC">Loyalty Rewards</h2>' +
          '<p style="color:#9FB1C7;margin-bottom:24px">Sign in to view your points and rewards</p>' +
          '<a href="/account/login?redirect=/loyalty" class="pp-btn pp-btn-primary">Sign In</a>' +
        '</div>' +
      '</div>';
  }

  function renderNoAccount() {
    root.innerHTML =
      '<div style="min-height:60vh;display:flex;align-items:center;justify-content:center">' +
        '<div style="text-align:center;max-width:400px;padding:0 24px">' +
          '<div style="font-size:60px;margin-bottom:16px">🎁</div>' +
          '<h2 style="color:#F8FAFC">No loyalty account yet</h2>' +
          '<p style="color:#9FB1C7">Your loyalty account is created automatically after your first purchase. Visit our store to get started!</p>' +
        '</div>' +
      '</div>';
  }

  function renderAccount(account) {
    var tierInfo = TIERS[account.tier] || TIERS.Bronze;
    var totalEarned = Number(account.total_earned || 0);
    var progress = tierInfo.nextAt ? Math.min(100, (totalEarned / tierInfo.nextAt) * 100) : 100;

    var html = '<div style="max-width:800px;margin:0 auto;padding:40px 16px">';
    html += '<h1 style="color:#F8FAFC;margin-bottom:8px">🏆 Your Loyalty Rewards</h1>';
    html += '<p style="color:#9FB1C7;margin-bottom:32px">Earn points with every purchase and unlock exclusive rewards</p>';

    // Points card
    html +=
      '<div style="background:linear-gradient(135deg, ' + tierInfo.color + '22, ' + tierInfo.color + '44);border:2px solid ' + tierInfo.color + ';border-radius:16px;padding:32px 24px;margin-bottom:24px;text-align:center">' +
        '<div style="font-size:48px">' + tierInfo.icon + '</div>' +
        '<h2 style="color:' + tierInfo.color + ';font-size:40px;margin:8px 0 4px;font-weight:700">' + nfmt(account.points) + '</h2>' +
        '<p style="color:#444;margin:0">Available Points</p>' +
        '<div style="margin-top:16px">' +
          '<span style="background:' + tierInfo.color + ';color:#fff;padding:4px 16px;border-radius:20px;font-weight:600;font-size:14px">' + esc(account.tier) + ' Member</span>' +
        '</div>';
    if (tierInfo.next) {
      html +=
        '<div style="margin-top:20px">' +
          '<p style="font-size:13px;color:#9FB1C7;margin-bottom:6px">' + nfmt(totalEarned) + ' / ' + nfmt(tierInfo.nextAt) + ' points to ' + esc(tierInfo.next) + '</p>' +
          '<div style="background:#e0e0e0;border-radius:8px;height:8px;overflow:hidden">' +
            '<div style="background:' + tierInfo.color + ';height:100%;width:' + progress + '%;transition:width 0.5s"></div>' +
          '</div>' +
        '</div>';
    }
    html += '</div>';

    // How to earn
    var earns = [
      { icon: "🛍️", label: "Purchase", desc: "1 pt per ₹10 spent" },
      { icon: "⭐", label: "Review", desc: "50 pts per review" },
      { icon: "🎂", label: "Birthday", desc: "100 pts bonus" },
      { icon: "🔁", label: "Referral", desc: "200 pts per friend" },
    ];
    html += '<div style="background:rgba(255,255,255,0.06);border-radius:12px;padding:24px;margin-bottom:24px">';
    html += '<h3 style="color:#F8FAFC;margin-bottom:16px">How to Earn Points</h3>';
    html += '<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:16px">';
    earns.forEach(function (h) {
      html +=
        '<div style="background:rgba(255,255,255,0.06);border-radius:10px;padding:16px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,0.35)">' +
          '<div style="font-size:28px;margin-bottom:6px">' + h.icon + '</div>' +
          '<div style="font-weight:600;color:#F8FAFC">' + h.label + '</div>' +
          '<div style="font-size:13px;color:#9FB1C7">' + h.desc + '</div>' +
        '</div>';
    });
    html += '</div></div>';

    // Tier benefits
    html += '<div style="background:rgba(255,255,255,0.06);border-radius:12px;padding:24px;margin-bottom:24px">';
    html += '<h3 style="color:#F8FAFC;margin-bottom:16px">Tier Benefits</h3>';
    html += '<div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:12px">';
    Object.keys(TIERS).forEach(function (tier) {
      var info = TIERS[tier];
      var current = account.tier === tier;
      html +=
        '<div style="background:' + (current ? info.color + '22' : "#fff") + ';border:2px solid ' + (current ? info.color : "#eee") + ';border-radius:10px;padding:16px;text-align:center">' +
          '<div style="font-size:28px">' + info.icon + '</div>' +
          '<div style="font-weight:700;color:' + info.color + '">' + tier + '</div>' +
          '<div style="font-size:12px;color:#9FB1C7;margin-top:4px">' +
            (tier === "Gold" ? "5000+ pts" : (info.min + "–" + info.max + " pts")) +
          '</div>' +
          (current ? '<div style="margin-top:6px;font-size:11px;background:' + info.color + ';color:#fff;border-radius:8px;padding:2px 8px">Current</div>' : '') +
        '</div>';
    });
    html += '</div></div>';

    // Transaction history
    html += '<div style="background:rgba(255,255,255,0.06);border-radius:12px;padding:24px">';
    html += '<h3 style="color:#F8FAFC;margin-bottom:16px">Points History</h3>';
    var txns = account.transactions || [];
    if (txns.length === 0) {
      html += '<p style="color:rgba(255,255,255,0.45);text-align:center">No transactions yet</p>';
    } else {
      txns.forEach(function (t) {
        var dateStr = "";
        try {
          dateStr = new Date(t.created_at).toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" });
        } catch (e) { dateStr = ""; }
        html +=
          '<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #eee">' +
            '<div>' +
              '<div style="font-weight:500;color:#E9EEF6">' + esc(t.description || t.type) + '</div>' +
              '<div style="font-size:12px;color:rgba(255,255,255,0.45)">' + esc(dateStr) + '</div>' +
            '</div>' +
            '<span style="font-weight:700;color:' + (t.points > 0 ? "#2e7d32" : "#c62828") + ';font-size:16px">' +
              (t.points > 0 ? "+" : "") + t.points +
            '</span>' +
          '</div>';
      });
    }
    html += '</div>';

    html += '</div>';
    root.innerHTML = html;
  }

  document.addEventListener("DOMContentLoaded", function () {
    fetch("/api/public/loyalty")
      .then(function (r) {
        if (r.status === 401) { renderNotLoggedIn(); return null; }
        return r.json();
      })
      .then(function (data) {
        if (!data) return;
        if (!data.account) { renderNoAccount(); return; }
        renderAccount(data.account);
      })
      .catch(function () { renderNoAccount(); });
  });
})();
</script>
@endverbatim
@endpush
