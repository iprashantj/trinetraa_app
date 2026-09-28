@extends('layouts.public')
@section('title', 'Find My Frames')
@section('content')
<div id="recommendRoot"></div>
@endsection

@push('scripts')
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>
<script>
@verbatim
(function () {
  var QUIZ_QUESTIONS = [
    {
      id: "faceShape",
      question: "What is your face shape?",
      icon: "😊",
      options: [
        { value: "Oval", label: "Oval", desc: "Balanced, slightly wider cheekbones" },
        { value: "Round", label: "Round", desc: "Soft curves, similar width & length" },
        { value: "Square", label: "Square", desc: "Strong jawline, similar width & length" },
        { value: "Heart", label: "Heart", desc: "Wider forehead, narrower chin" },
        { value: "Oblong", label: "Oblong", desc: "Long and narrow face" }
      ]
    },
    {
      id: "stylePref",
      question: "What is your preferred style?",
      icon: "👗",
      options: [
        { value: "professional", label: "Professional", desc: "Clean, office-ready look" },
        { value: "casual", label: "Casual", desc: "Relaxed everyday wear" },
        { value: "sporty", label: "Sporty", desc: "Active and athletic" },
        { value: "fashion-forward", label: "Fashion Forward", desc: "Bold and trendy" },
        { value: "classic", label: "Classic", desc: "Timeless and elegant" }
      ]
    },
    {
      id: "usage",
      question: "Primary use case?",
      icon: "🎯",
      options: [
        { value: "office", label: "Office / Work", desc: "Daily professional use" },
        { value: "outdoor", label: "Outdoor / Sun", desc: "Protection outside" },
        { value: "driving", label: "Driving", desc: "Road clarity and safety" },
        { value: "everyday", label: "Everyday Casual", desc: "General all-day use" },
        { value: "reading", label: "Reading / Screen", desc: "Close-up work and screens" }
      ]
    },
    {
      id: "budget",
      question: "What's your budget?",
      icon: "💰",
      options: [
        { value: "under-500", label: "Under ₹500", desc: "Budget-friendly" },
        { value: "500-2000", label: "₹500–₹2,000", desc: "Mid-range" },
        { value: "2000-5000", label: "₹2,000–₹5,000", desc: "Premium" },
        { value: "above-5000", label: "Above ₹5,000", desc: "Luxury" }
      ]
    }
  ];

  var root = document.getElementById("recommendRoot");
  var step = 0;
  var answers = {};
  var result = null;
  var loading = false;

  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function num(v) { return Number(v) || 0; }

  function renderLoading() {
    root.innerHTML =
      '<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:16px">' +
        '<div style="width:50px;height:50px;border:4px solid #eee;border-top:4px solid #1a3a5c;border-radius:50%;animation:spin 0.8s linear infinite"></div>' +
        '<p style="color:#9FB1C7">Finding your perfect frames…</p>' +
      '</div>';
  }

  function renderResult() {
    var recommendation = result.recommendation || {};
    var eyewears = result.eyewears || [];
    var faceShape = recommendation.faceShape || "";
    var tags = recommendation.recommendedTags || [];

    var tagHtml = tags.map(function (tag) {
      return '<span style="background:#1a3a5c;color:#fff;padding:3px 12px;border-radius:20px;font-size:13px">' + esc(tag) + '</span>';
    }).join("");

    var framesHtml;
    if (eyewears.length === 0) {
      framesHtml = '<div style="text-align:center;padding:40px;background:rgba(255,255,255,0.06);border-radius:12px">' +
        '<p style="color:#9FB1C7">No frames match your filters right now. Check back after the store updates inventory!</p></div>';
    } else {
      var cards = eyewears.map(function (e) {
        var price = num(e.price);
        var discounted = e.discount_percentage > 0 ? (price * (1 - e.discount_percentage / 100)).toFixed(0) : null;
        var media = e.image
          ? '<img src="' + esc(e.image) + '" alt="' + esc(e.name) + '" style="max-height:100%;max-width:100%;object-fit:contain">'
          : '<span style="font-size:40px">👓</span>';
        var brand = e.brand ? '<div style="font-size:12px;color:rgba(255,255,255,0.45);margin-bottom:4px">' + esc(e.brand) + '</div>' : '';
        var strike = discounted ? '<span style="font-size:12px;color:rgba(255,255,255,0.45);text-decoration:line-through">₹' + price.toFixed(0) + '</span>' : '';
        return '<a href="/eyewears/' + esc(e.slug) + '" style="text-decoration:none">' +
          '<div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.14);border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.35);transition:transform 0.2s;cursor:pointer" ' +
            'onmouseenter="this.style.transform=\'translateY(-4px)\'" onmouseleave="this.style.transform=\'translateY(0)\'">' +
            '<div style="height:140px;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center">' + media + '</div>' +
            '<div style="padding:12px 14px">' +
              '<div style="font-weight:600;color:#F8FAFC;font-size:14px;margin-bottom:2px">' + esc(e.name) + '</div>' +
              brand +
              '<div style="display:flex;align-items:center;gap:6px">' +
                '<span style="font-weight:700;color:#F8FAFC">₹' + (discounted || price.toFixed(0)) + '</span>' +
                strike +
              '</div>' +
            '</div>' +
          '</div></a>';
      }).join("");
      framesHtml = '<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:20px">' + cards + '</div>';
    }

    root.innerHTML =
      '<div style="max-width:900px;margin:0 auto;padding:40px 16px">' +
        '<div style="text-align:center;margin-bottom:40px">' +
          '<div style="font-size:60px">✨</div>' +
          '<h1 style="color:#F8FAFC">Your Perfect Frames</h1>' +
          '<p style="color:#9FB1C7">Based on your ' + esc(faceShape) + ' face shape and preferences</p>' +
        '</div>' +
        '<div style="background:linear-gradient(135deg, #1a3a5c11, #1a3a5c22);border:2px solid #1a3a5c33;border-radius:16px;padding:24px 28px;margin-bottom:32px">' +
          '<h3 style="color:#F8FAFC;margin-bottom:8px">💡 Style Tip for ' + esc(faceShape) + ' Face</h3>' +
          '<p style="color:#444;margin:0;line-height:1.6">' + esc(recommendation.tip) + '</p>' +
          '<div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">' + tagHtml + '</div>' +
        '</div>' +
        '<h2 style="color:#F8FAFC;margin-bottom:24px">Recommended Frames</h2>' +
        framesHtml +
        '<div style="text-align:center;margin-top:32px">' +
          '<button id="retakeBtn" style="background:rgba(255,255,255,0.12);color:#F8FAFC;border:none;border-radius:8px;padding:12px 32px;cursor:pointer;font-weight:600;margin-right:12px">Retake Quiz</button>' +
          '<a href="/eyewears" style="background:#1a3a5c;color:#fff;text-decoration:none;border-radius:8px;padding:12px 32px;font-weight:600">Browse All</a>' +
        '</div>' +
      '</div>';

    var retake = document.getElementById("retakeBtn");
    if (retake) retake.addEventListener("click", reset);
  }

  function renderQuiz() {
    var q = QUIZ_QUESTIONS[step];

    var progress = QUIZ_QUESTIONS.map(function (_, i) {
      return '<div style="width:32px;height:4px;border-radius:2px;background:' + (i <= step ? "#1a3a5c" : "#e0e0e0") + ';transition:background 0.3s"></div>';
    }).join("");

    var options = q.options.map(function (opt) {
      var selected = answers[q.id] === opt.value;
      return '<button type="button" data-value="' + esc(opt.value) + '" ' +
        'style="background:' + (selected ? "#1a3a5c" : "#fff") + ';color:' + (selected ? "#fff" : "#333") + ';' +
        'border:2px solid;border-color:' + (selected ? "#1a3a5c" : "#e0e0e0") + ';border-radius:12px;padding:16px 20px;cursor:pointer;text-align:left;transition:all 0.2s">' +
        '<div style="font-weight:600">' + esc(opt.label) + '</div>' +
        '<div style="font-size:13px;opacity:0.75;margin-top:2px">' + esc(opt.desc) + '</div>' +
        '</button>';
    }).join("");

    var backBtn = step > 0
      ? '<button id="backBtn" style="margin-top:20px;background:none;border:none;color:#9FB1C7;cursor:pointer;font-size:14px">← Back</button>'
      : '';

    root.innerHTML =
      '<div style="max-width:600px;margin:0 auto;padding:40px 16px">' +
        '<div style="display:flex;justify-content:center;gap:8px;margin-bottom:32px">' + progress + '</div>' +
        '<div style="text-align:center;margin-bottom:32px">' +
          '<div style="font-size:56px">' + q.icon + '</div>' +
          '<h2 style="color:#F8FAFC;margin-top:12px">' + esc(q.question) + '</h2>' +
          '<p style="color:rgba(255,255,255,0.45);font-size:14px">Question ' + (step + 1) + ' of ' + QUIZ_QUESTIONS.length + '</p>' +
        '</div>' +
        '<div style="display:flex;flex-direction:column;gap:12px">' + options + '</div>' +
        backBtn +
      '</div>';

    root.querySelectorAll("[data-value]").forEach(function (btn) {
      btn.addEventListener("click", function () { select(btn.getAttribute("data-value")); });
    });
    var back = document.getElementById("backBtn");
    if (back) back.addEventListener("click", function () { step = step - 1; render(); });
  }

  function render() {
    if (loading) { renderLoading(); return; }
    if (result) { renderResult(); return; }
    renderQuiz();
  }

  function select(value) {
    var q = QUIZ_QUESTIONS[step];
    answers[q.id] = value;

    if (step < QUIZ_QUESTIONS.length - 1) {
      step = step + 1;
      render();
    } else {
      loading = true;
      render();
      fetch("/api/public/recommend", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(answers)
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          result = data;
          loading = false;
          render();
        })
        .catch(function () {
          loading = false;
          render();
        });
    }
  }

  function reset() {
    step = 0;
    answers = {};
    result = null;
    render();
  }

  document.addEventListener("DOMContentLoaded", function () { render(); });
})();
@endverbatim
</script>
@endpush
