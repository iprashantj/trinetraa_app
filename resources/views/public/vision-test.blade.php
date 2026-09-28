@extends('layouts.public')
@section('title', 'Vision Self-Assessment — Trinetraa Optician')
@section('content')

<style>
    @media print {
        .pp-nav, .pp-footer, .pp-back-to-top, .vision-print-actions { display: none !important; }
        body > *, .pp-root > *:not(.vision-print-area) { display: none !important; }
        .pp-root { display: block !important; }
        .vision-print-area { display: block !important; padding: 0 !important; margin: 0 auto !important; max-width: 100% !important; }
        .vision-print-header { display: flex !important; }
    }
</style>

{{-- ── STEP: intro ── --}}
<div id="vtIntro" data-step="intro" style="max-width:600px;margin:0 auto;padding:88px 16px 60px;text-align:center">
    <div style="font-size:72px;margin-bottom:24px">👁️</div>
    <h1 style="color:#F8FAFC;margin-bottom:12px">Vision Self-Assessment</h1>
    <p style="color:#9FB1C7;font-size:16px;line-height:1.6;margin-bottom:32px">
        Answer a few quick questions to understand your eye health. This is not a medical diagnosis — always consult an optician or ophthalmologist for professional advice.
    </p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:40px">
        <span style="background:rgba(20,184,166,0.14);color:#F8FAFC;padding:6px 16px;border-radius:20px;font-size:14px;font-weight:500">Quick — 2 minutes</span>
        <span style="background:rgba(20,184,166,0.14);color:#F8FAFC;padding:6px 16px;border-radius:20px;font-size:14px;font-weight:500">9 questions</span>
        <span style="background:rgba(20,184,166,0.14);color:#F8FAFC;padding:6px 16px;border-radius:20px;font-size:14px;font-weight:500">Instant results</span>
    </div>
    <button id="vtStartBtn" style="background:#0F766E;color:#fff;border:none;border-radius:12px;padding:16px 48px;font-size:18px;cursor:pointer;font-weight:600">
        Start Assessment →
    </button>
</div>

{{-- ── STEP: quiz ── --}}
<div id="vtQuiz" data-step="quiz" style="display:none;max-width:560px;margin:0 auto;padding:88px 16px 40px">
    <div style="background:rgba(255,255,255,0.14);border-radius:4px;height:6px;margin-bottom:32px">
        <div id="vtProgress" style="background:#0F766E;height:100%;width:0%;border-radius:4px;transition:width 0.3s"></div>
    </div>
    <div style="text-align:center;margin-bottom:32px">
        <div id="vtQIcon" style="font-size:52px;margin-bottom:16px"></div>
        <h2 id="vtQText" style="color:#F8FAFC;line-height:1.3"></h2>
        <p id="vtQCounter" style="color:rgba(255,255,255,0.45);font-size:13px"></p>
    </div>
    <div id="vtQOptions"></div>
    <button id="vtBackBtn" style="margin-top:20px;background:none;border:none;color:#9FB1C7;cursor:pointer;display:none;margin:20px auto 0">← Back</button>
</div>

{{-- ── STEP: contact ── --}}
<div id="vtContact" data-step="contact" style="display:none;max-width:480px;margin:0 auto;padding:88px 16px 40px">
    <h2 style="color:#F8FAFC;margin-bottom:8px">Almost done!</h2>
    <p style="color:#9FB1C7;margin-bottom:24px">Share your details to save your assessment (optional)</p>
    <form id="vtContactForm">
        <div style="margin-bottom:14px">
            <label style="display:block;margin-bottom:4px;font-weight:500;color:#F8FAFC">Name</label>
            <input type="text" id="vtName" style="width:100%;padding:10px 14px;border:1px solid rgba(255,255,255,0.14);border-radius:8px;font-size:15px">
        </div>
        <div style="margin-bottom:14px">
            <label style="display:block;margin-bottom:4px;font-weight:500;color:#F8FAFC">Email</label>
            <input type="email" id="vtEmail" style="width:100%;padding:10px 14px;border:1px solid rgba(255,255,255,0.14);border-radius:8px;font-size:15px">
        </div>
        <div style="margin-bottom:14px">
            <label style="display:block;margin-bottom:4px;font-weight:500;color:#F8FAFC">Phone</label>
            <input type="tel" id="vtPhone" style="width:100%;padding:10px 14px;border:1px solid rgba(255,255,255,0.14);border-radius:8px;font-size:15px">
        </div>
        <div style="display:flex;gap:12px;margin-top:20px">
            <button type="submit" id="vtSubmitBtn" style="background:#0F766E;color:#fff;border:none;border-radius:10px;padding:14px 32px;font-size:16px;cursor:pointer;font-weight:600;flex:1">
                See My Results →
            </button>
            <button type="button" id="vtSkipBtn" style="background:rgba(255,255,255,0.1);color:#9FB1C7;border:none;border-radius:10px;padding:14px 20px;cursor:pointer">
                Skip
            </button>
        </div>
    </form>
</div>

{{-- ── STEP: result ── --}}
<div id="vtResult" data-step="result" style="display:none">
    <div class="vision-print-area" style="max-width:600px;margin:0 auto;padding:88px 16px 40px">

        <div class="vision-print-header" style="display:none;align-items:center;gap:12px;margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #1a3a5c">
            <span style="font-size:32px">👁️</span>
            <div>
                <div style="font-weight:700;font-size:18px;color:#F8FAFC">Trinetraa Optician</div>
                <div style="font-size:12px;color:#9FB1C7">📍 Nashik, Maharashtra &nbsp;|&nbsp; 📞 0253-123456 &nbsp;|&nbsp; ✉️ info@trinetraa.com</div>
            </div>
        </div>

        <div style="text-align:center;margin-bottom:32px">
            <div id="vtUrgencyIcon" style="font-size:60px"></div>
            <h1 style="color:#F8FAFC">Your Vision Assessment</h1>
            <div id="vtUrgencyLabel" style="display:inline-block;padding:8px 24px;border-radius:24px;font-weight:700;font-size:16px;margin-top:8px"></div>
        </div>

        <div id="vtIssuesBox" style="background:rgba(230,81,0,0.15);border:1px solid rgba(230,81,0,0.4);border-radius:12px;padding:20px;margin-bottom:20px;display:none">
            <h3 style="color:#FB923C;margin-bottom:12px">⚠️ Potential Concerns</h3>
            <div id="vtIssues"></div>
        </div>

        <div id="vtRecsBox" style="background:rgba(46,125,50,0.15);border:1px solid rgba(46,125,50,0.4);border-radius:12px;padding:20px;margin-bottom:20px;display:none">
            <h3 style="color:#4ADE80;margin-bottom:12px">✅ Recommendations</h3>
            <div id="vtRecs"></div>
        </div>

        <div style="background:rgba(255,255,255,0.06);border-radius:12px;padding:20px;margin-bottom:24px;text-align:center">
            <p style="color:#9FB1C7;font-size:13px;margin-bottom:12px">
                ⚕️ <strong>Disclaimer:</strong> This is a self-assessment tool only. It is not a medical diagnosis. Please visit Trinetraa Optician for a professional eye examination.
            </p>
            <a href="/appointment" style="background:#0F766E;color:#fff;text-decoration:none;border-radius:8px;padding:12px 28px;font-weight:600;display:inline-block">
                Book an Eye Checkup
            </a>
        </div>

        <div class="vision-print-actions" style="display:flex;gap:12px;justify-content:center">
            <button id="vtRetakeBtn" style="background:rgba(255,255,255,0.1);color:#F8FAFC;border:none;border-radius:8px;padding:10px 24px;cursor:pointer">
                Retake
            </button>
            <button id="vtPrintBtn" style="background:#0F766E;color:#fff;border:none;border-radius:8px;padding:10px 24px;cursor:pointer">
                🖨️ Print Results
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var QUESTIONS = [
        { id: "blurry_distance", text: "Do objects in the distance appear blurry?", icon: "🏔️" },
        { id: "blurry_near", text: "Do you have difficulty reading small text up close?", icon: "📖" },
        { id: "headaches", text: "Do you experience headaches after reading or screen use?", icon: "🤕" },
        { id: "night_driving", text: "Do you have difficulty seeing clearly at night or while driving?", icon: "🌙" },
        { id: "screen_strain", text: "Do you experience eye strain after using screens (phone/computer)?", icon: "💻" },
        { id: "eye_pain", text: "Do you experience eye pain or discomfort?", icon: "😣" },
        { id: "sudden_change", text: "Have you noticed any sudden changes in your vision recently?", icon: "⚡" },
        { id: "family_history", text: "Do you have a family history of eye conditions (glaucoma, macular degeneration)?", icon: "🧬" },
        { id: "last_checkup", text: "When was your last eye examination?", icon: "🏥", type: "select", options: [
            { value: "within_6_months", label: "Within 6 months" },
            { value: "6_to_12_months", label: "6–12 months ago" },
            { value: "1_to_2_years", label: "1–2 years ago" },
            { value: "over_2_years", label: "Over 2 years ago" },
            { value: "never", label: "Never" },
        ]},
    ];

    var URGENCY_CONFIG = {
        routine: { color: "#4ADE80", bg: "rgba(46,125,50,0.15)", label: "Routine Check", icon: "✅" },
        soon: { color: "#FB923C", bg: "rgba(230,81,0,0.15)", label: "Schedule Soon", icon: "⚠️" },
        urgent: { color: "#F87171", bg: "rgba(198,40,40,0.15)", label: "See a Doctor Soon", icon: "🚨" },
    };

    var step = "intro";
    var answers = {};
    var contact = { name: "", email: "", phone: "" };
    var currentQ = 0;
    var result = null;
    var loading = false;

    var elIntro = document.getElementById("vtIntro");
    var elQuiz = document.getElementById("vtQuiz");
    var elContact = document.getElementById("vtContact");
    var elResult = document.getElementById("vtResult");

    function setStep(s) {
        step = s;
        elIntro.style.display = s === "intro" ? "block" : "none";
        elQuiz.style.display = s === "quiz" ? "block" : "none";
        elContact.style.display = s === "contact" ? "block" : "none";
        elResult.style.display = s === "result" ? "block" : "none";
        window.scrollTo({ top: 0, behavior: "smooth" });
        if (s === "quiz") renderQuiz();
        if (s === "result") renderResult();
    }

    // ── Quiz rendering ──
    function renderQuiz() {
        var q = QUESTIONS[currentQ];
        var progress = (currentQ / QUESTIONS.length) * 100;
        document.getElementById("vtProgress").style.width = progress + "%";
        document.getElementById("vtQIcon").textContent = q.icon;
        document.getElementById("vtQText").textContent = q.text;
        document.getElementById("vtQCounter").textContent = "Question " + (currentQ + 1) + " of " + QUESTIONS.length;

        var optWrap = document.getElementById("vtQOptions");
        optWrap.innerHTML = "";

        if (q.type === "select") {
            var col = document.createElement("div");
            col.style.cssText = "display:flex;flex-direction:column;gap:10px";
            q.options.forEach(function (opt) {
                var btn = document.createElement("button");
                btn.type = "button";
                btn.textContent = opt.label;
                btn.style.cssText = "background:rgba(255,255,255,0.06);border:2px solid rgba(255,255,255,0.14);border-radius:10px;padding:14px 20px;cursor:pointer;font-size:15px;font-weight:500;color:#F8FAFC;transition:all 0.2s";
                btn.addEventListener("mouseenter", function () { btn.style.borderColor = "#2DD4BF"; btn.style.background = "rgba(20,184,166,0.14)"; });
                btn.addEventListener("mouseleave", function () { btn.style.borderColor = "rgba(255,255,255,0.14)"; btn.style.background = "rgba(255,255,255,0.06)"; });
                btn.addEventListener("click", function () { answer(opt.value); });
                col.appendChild(btn);
            });
            optWrap.appendChild(col);
        } else {
            var row = document.createElement("div");
            row.style.cssText = "display:flex;gap:16px;justify-content:center";
            [{ v: "yes", label: "Yes", color: "#F87171", bg: "rgba(198,40,40,0.15)" }, { v: "no", label: "No", color: "#4ADE80", bg: "rgba(46,125,50,0.15)" }].forEach(function (b) {
                var btn = document.createElement("button");
                btn.type = "button";
                btn.textContent = b.label;
                btn.style.cssText = "background:" + b.bg + ";color:" + b.color + ";border:2px solid " + b.color + ";border-radius:12px;padding:18px 48px;font-size:20px;font-weight:700;cursor:pointer;flex:1;max-width:200px";
                btn.addEventListener("click", function () { answer(b.v); });
                row.appendChild(btn);
            });
            optWrap.appendChild(row);
        }

        var backBtn = document.getElementById("vtBackBtn");
        backBtn.style.display = currentQ > 0 ? "block" : "none";
    }

    function answer(value) {
        var q = QUESTIONS[currentQ];
        answers[q.id] = value;
        if (currentQ < QUESTIONS.length - 1) {
            currentQ += 1;
            renderQuiz();
        } else {
            setStep("contact");
        }
    }

    // ── Submit ──
    async function submit() {
        if (loading) return;
        loading = true;
        var submitBtn = document.getElementById("vtSubmitBtn");
        submitBtn.textContent = "Analyzing…";
        submitBtn.disabled = true;
        document.getElementById("vtSkipBtn").disabled = true;

        contact.name = document.getElementById("vtName").value;
        contact.email = document.getElementById("vtEmail").value;
        contact.phone = document.getElementById("vtPhone").value;

        try {
            var res = await fetch("/api/public/vision-assessment", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(Object.assign({}, contact, { answers: answers })),
            });
            var data = await res.json();
            result = data.analysis;
            setStep("result");
        } finally {
            loading = false;
            submitBtn.textContent = "See My Results →";
            submitBtn.disabled = false;
            document.getElementById("vtSkipBtn").disabled = false;
        }
    }

    // ── Result rendering ──
    function renderResult() {
        if (!result) return;
        var urgency = URGENCY_CONFIG[result.urgency] || URGENCY_CONFIG.routine;

        document.getElementById("vtUrgencyIcon").textContent = urgency.icon;
        var labelEl = document.getElementById("vtUrgencyLabel");
        labelEl.textContent = urgency.label;
        labelEl.style.background = urgency.bg;
        labelEl.style.color = urgency.color;

        var issues = result.issues || [];
        var issuesBox = document.getElementById("vtIssuesBox");
        var issuesWrap = document.getElementById("vtIssues");
        issuesWrap.innerHTML = "";
        if (issues.length > 0) {
            issuesBox.style.display = "block";
            issues.forEach(function (issue) {
                var d = document.createElement("div");
                d.style.cssText = "display:flex;gap:8px;margin-bottom:6px";
                var bullet = document.createElement("span");
                bullet.style.color = "#FB923C";
                bullet.textContent = "•";
                var txt = document.createElement("span");
                txt.style.color = "#B8C6D8";
                txt.textContent = issue;
                d.appendChild(bullet); d.appendChild(txt);
                issuesWrap.appendChild(d);
            });
        } else {
            issuesBox.style.display = "none";
        }

        var recs = result.recommendations || [];
        var recsBox = document.getElementById("vtRecsBox");
        var recsWrap = document.getElementById("vtRecs");
        recsWrap.innerHTML = "";
        if (recs.length > 0) {
            recsBox.style.display = "block";
            recs.forEach(function (rec) {
                var d = document.createElement("div");
                d.style.cssText = "display:flex;gap:8px;margin-bottom:6px";
                var arrow = document.createElement("span");
                arrow.style.color = "#4ADE80";
                arrow.textContent = "→";
                var txt = document.createElement("span");
                txt.style.color = "#B8C6D8";
                txt.textContent = rec;
                d.appendChild(arrow); d.appendChild(txt);
                recsWrap.appendChild(d);
            });
        } else {
            recsBox.style.display = "none";
        }
    }

    // ── Wire up controls ──
    document.getElementById("vtStartBtn").addEventListener("click", function () { setStep("quiz"); });
    document.getElementById("vtBackBtn").addEventListener("click", function () { if (currentQ > 0) { currentQ -= 1; renderQuiz(); } });
    document.getElementById("vtContactForm").addEventListener("submit", function (e) { e.preventDefault(); submit(); });
    document.getElementById("vtSkipBtn").addEventListener("click", function () { submit(); });
    document.getElementById("vtRetakeBtn").addEventListener("click", function () {
        currentQ = 0; answers = {}; result = null;
        setStep("intro");
    });
    document.getElementById("vtPrintBtn").addEventListener("click", function () { window.print(); });
})();
</script>
@endverbatim
@endpush
