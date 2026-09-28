@extends('layouts.public')
@section('title', 'Virtual Try-On — Try Frames on Your Face | Trinetraa Optician')
@section('meta_description', 'Try on glasses virtually using your camera at Trinetraa Optician. See how different frames look on your face before you buy. Free tool, no app needed.')
@section('canonical', 'https://trinetraaoptician.com/try-on')
@section('content')

<style>
    @keyframes tryon-spin { to { transform: rotate(360deg); } }
    @keyframes tryon-pulse { 0%,100%{box-shadow:0 0 0 0 rgba(99,102,241,0.5)} 50%{box-shadow:0 0 0 10px rgba(99,102,241,0);} }
    @keyframes tryon-fadein { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:none} }
    .tryon-shape-badge { animation: tryon-pulse 2s ease-in-out infinite; }
    .tryon-panel-in    { animation: tryon-fadein 0.4s ease; }
    .tryon-cat-chip::-webkit-scrollbar { height: 4px; }
    .tryon-cat-chip::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.14); border-radius: 2px; }
    .tryon-frame-btn { transition: all 0.18s ease; }
    .tryon-frame-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.35); }
    .tryon-frame-btn.selected { border-color: #6366f1 !important; box-shadow: 0 0 0 3px rgba(99,102,241,0.2); }
</style>

<div style="min-height:100vh;padding-top:80px">

    {{-- ── Page Header ─────────────────────────────────── --}}
    <div style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#0f172a 100%);color:#fff;padding:2rem 1.5rem 2.5rem;text-align:center">
        <div style="font-size:48px;margin-bottom:8px">🎭</div>
        <h1 style="margin:0;font-size:clamp(1.4rem,4vw,2.2rem);font-weight:800;letter-spacing:-0.02em">Virtual Try-On</h1>
        <p style="margin:0.5rem 0 0;color:rgba(255,255,255,0.65);font-size:0.95rem">
            AI-powered face detection · Real-time glasses overlay · 21 frame styles
        </p>
        <div id="tryonHeaderBadge" style="display:none"></div>
    </div>

    <div style="max-width:1400px;margin:0 auto;padding:2rem 1rem 4rem">

        {{-- ── Error ───────────────────────────────────────── --}}
        <div id="tryonError" style="background:rgba(220,38,38,0.15);color:#F87171;padding:12px 18px;border-radius:10px;margin-bottom:20px;border:1px solid rgba(220,38,38,0.4);font-weight:500;display:none"></div>

        {{-- ── Main Grid ───────────────────────────────────── --}}
        <div style="display:grid;grid-template-columns:minmax(0,1.5fr) 360px;gap:24px;align-items:start">

            {{-- ── Camera Panel ──────────────────────────────── --}}
            <div>
                <div style="position:relative;background:#0f172a;border-radius:20px;overflow:hidden;aspect-ratio:4/3;box-shadow:0 24px 60px rgba(0,0,0,0.25)">
                    <video
                        id="tryonVideo"
                        style="width:100%;height:100%;object-fit:cover;transform:scaleX(-1);display:none"
                        muted playsinline
                    ></video>
                    <canvas
                        id="tryonCanvas"
                        style="position:absolute;inset:0;width:100%;height:100%;transform:scaleX(-1);display:none"
                    ></canvas>

                    {{-- Idle state --}}
                    <div id="tryonIdle" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:20px;padding:32px">
                        <div style="width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,0.06);border:2px solid rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;font-size:44px">📷</div>
                        <div style="text-align:center">
                            <p style="color:#fff;font-weight:700;font-size:1.1rem;margin:0 0 6px">Enable Your Camera</p>
                            <p style="color:rgba(255,255,255,0.5);font-size:0.82rem;margin:0;line-height:1.5">
                                All processing runs locally in your browser.<br>No footage is uploaded or stored.
                            </p>
                        </div>
                        <button
                            id="tryonStartBtn"
                            style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:14px;padding:14px 40px;font-size:1rem;cursor:pointer;font-weight:700;box-shadow:0 8px 24px rgba(99,102,241,0.4)"
                        >
                            Start Try-On →
                        </button>
                    </div>

                    {{-- Loading state --}}
                    <div id="tryonLoading" style="position:absolute;inset:0;display:none;align-items:center;justify-content:center;flex-direction:column;gap:16px">
                        <div style="width:48px;height:48px;border:4px solid rgba(255,255,255,0.15);border-top:4px solid #6366f1;border-radius:50%;animation:tryon-spin 0.9s linear infinite"></div>
                        <p id="tryonLoadingText" style="color:#fff;font-weight:600">Starting camera…</p>
                        <p style="color:rgba(255,255,255,0.4);font-size:0.8rem">First load may take ~10 seconds</p>
                    </div>

                    {{-- Running overlays --}}
                    <div id="tryonRunning" style="display:none">
                        {{-- Top-left: face shape --}}
                        <div id="tryonShapeTag" class="tryon-panel-in" style="position:absolute;top:14px;left:14px;padding:6px 14px;border-radius:999px;font-weight:700;font-size:0.82rem;display:none"></div>
                        {{-- Top-right: confidence --}}
                        <div id="tryonConfTag" style="position:absolute;top:14px;right:14px;background:rgba(0,0,0,0.55);color:#fff;padding:5px 12px;border-radius:999px;font-size:0.75rem;font-weight:600;backdrop-filter:blur(6px);display:none"></div>
                        {{-- Bottom: face guide tip --}}
                        <div id="tryonGuideTip" style="position:absolute;bottom:56px;left:50%;transform:translateX(-50%)">
                            <span style="background:rgba(0,0,0,0.65);color:#fff;padding:7px 18px;border-radius:999px;font-size:0.8rem;backdrop-filter:blur(6px);white-space:nowrap">
                                👤 Centre your face in the oval
                            </span>
                        </div>
                        {{-- Bottom bar --}}
                        <div style="position:absolute;bottom:0;left:0;right:0;display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:linear-gradient(to top, rgba(0,0,0,0.7), transparent)">
                            <button id="tryonStopBtn" style="background:rgba(239,68,68,0.85);color:#fff;border:none;border-radius:10px;padding:8px 18px;cursor:pointer;font-weight:600;font-size:0.82rem;backdrop-filter:blur(4px)">
                                ⏹ Stop
                            </button>
                            <button id="tryonShotBtn" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);border-radius:10px;padding:8px 18px;cursor:pointer;font-weight:600;font-size:0.82rem;backdrop-filter:blur(4px)">
                                📸 Screenshot
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Screenshot preview --}}
                <div id="tryonShotPreview" class="tryon-panel-in" style="margin-top:16px;background:rgba(255,255,255,0.06);border-radius:14px;padding:14px;box-shadow:0 4px 20px rgba(0,0,0,0.35);border:1px solid rgba(255,255,255,0.14);display:none">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                        <span style="font-weight:700;color:#F8FAFC;font-size:0.9rem">📸 Your Screenshot</span>
                        <div style="display:flex;gap:8px">
                            <a id="tryonShotDownload" href="#" download="trinetraa-tryon.png" style="background:#0F766E;color:#fff;text-decoration:none;border-radius:8px;padding:6px 14px;font-size:0.8rem;font-weight:600">Download</a>
                            <button id="tryonShotClose" style="background:rgba(255,255,255,0.1);border:none;border-radius:8px;padding:6px 10px;cursor:pointer;font-size:0.8rem">✕</button>
                        </div>
                    </div>
                    <img id="tryonShotImg" src="" alt="Try-on screenshot" style="width:100%;border-radius:10px;display:block">
                </div>

                {{-- Face shape result card --}}
                <div id="tryonShapeCard" class="tryon-panel-in" style="margin-top:16px;border-radius:14px;padding:18px;display:none"></div>
            </div>

            {{-- ── Frame Selector Panel ──────────────────────── --}}
            <div style="background:rgba(255,255,255,0.06);border-radius:20px;box-shadow:0 4px 24px rgba(0,0,0,0.35);border:1px solid rgba(255,255,255,0.14);overflow:hidden">

                {{-- Panel header --}}
                <div style="padding:18px 18px 14px;border-bottom:1px solid rgba(255,255,255,0.14)">
                    <div style="font-weight:800;color:#F8FAFC;font-size:1rem">Choose a Frame</div>
                    <div id="tryonFrameCount" style="font-size:0.78rem;color:#8FA3BB;margin-top:2px">0 frames available</div>
                </div>

                {{-- Category filter chips --}}
                <div id="tryonChips" class="tryon-cat-chip" style="overflow-x:auto;padding:12px 14px 10px;display:flex;gap:7px;scrollbar-width:thin;border-bottom:1px solid rgba(255,255,255,0.14)"></div>

                {{-- Frame list --}}
                <div id="tryonFrameList" style="max-height:480px;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:8px"></div>

                {{-- Selected frame actions --}}
                <div id="tryonFrameActions" class="tryon-panel-in" style="padding:14px 16px;border-top:1px solid rgba(255,255,255,0.14);display:none;flex-direction:column;gap:8px">
                    <a id="tryonViewBtn" href="#" style="display:block;background:linear-gradient(135deg,#0F766E,#0f172a);color:#fff;text-decoration:none;border-radius:10px;padding:11px 16px;text-align:center;font-weight:700;font-size:0.875rem">
                        View Details &amp; Buy →
                    </a>
                    <a href="/cart" style="display:block;background:#f97316;color:#fff;text-decoration:none;border-radius:10px;padding:11px 16px;text-align:center;font-weight:700;font-size:0.875rem">
                        🛒 Add to Cart
                    </a>
                </div>
            </div>
        </div>

        {{-- ── 21 Frame Styles Guide ───────────────────────── --}}
        <div style="margin-top:48px">
            <div style="text-align:center;margin-bottom:28px">
                <h2 style="font-size:clamp(1.25rem,3vw,1.75rem);font-weight:800;color:#F8FAFC;margin:0 0 8px">21 Frame Styles — Find Your Match</h2>
                <p style="color:#8FA3BB;font-size:0.9rem;margin:0">Click any style to filter frames above</p>
            </div>
            <div id="tryonStyleGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px"></div>
        </div>

        {{-- ── Face Shape Guide ────────────────────────────── --}}
        <div style="margin-top:48px">
            <div style="text-align:center;margin-bottom:28px">
                <h2 style="font-size:clamp(1.25rem,3vw,1.75rem);font-weight:800;color:#F8FAFC;margin:0 0 8px">8 Face Shape Profiles</h2>
                <p style="color:#8FA3BB;font-size:0.9rem;margin:0">Our AI detects your face shape live and filters the best frames for you</p>
            </div>
            <div id="tryonShapeGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px"></div>
        </div>

        {{-- ── Disclaimer ──────────────────────────────────── --}}
        <div style="margin-top:32px;background:rgba(245,184,65,0.15);border:1px solid rgba(245,184,65,0.4);border-radius:12px;padding:14px 18px;font-size:0.82rem;color:#F5B841">
            <strong>Note:</strong> Virtual try-on is approximate. Frame sizes, fit, lens colour, and appearance may vary in person. Visit our Nashik store for a hands-on physical try-on experience.
        </div>

    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    /* ── 21 Frame Style Categories ─────────────────────────────── */
    var FRAME_STYLES = [
        { id: "all",         name: "All",          icon: "👓", desc: "Browse everything",                    suits: [] },
        { id: "aviator",     name: "Aviator",      icon: "✈️",  desc: "Teardrop lenses, thin metal",         suits: ["Oval","Heart","Diamond","Oblong"] },
        { id: "wayfarer",    name: "Wayfarer",     icon: "🕶️", desc: "Bold plastic, iconic square",          suits: ["Oval","Heart","Diamond","Round"] },
        { id: "round",       name: "Round",        icon: "⭕",  desc: "Circular lenses, classic look",        suits: ["Square","Oblong","Diamond","Rectangle"] },
        { id: "rectangle",   name: "Rectangle",    icon: "▬",  desc: "Sharp corners, wider than tall",       suits: ["Oval","Round","Heart","Triangle"] },
        { id: "cat_eye",     name: "Cat Eye",      icon: "😺", desc: "Upswept corners, glamorous",           suits: ["Oval","Square","Round","Triangle"] },
        { id: "square",      name: "Square",       icon: "⬛",  desc: "Equal sides, bold statement",          suits: ["Oval","Round","Oblong","Heart"] },
        { id: "oval_frame",  name: "Oval",         icon: "🔮", desc: "Soft curves, universally flattering",  suits: ["Square","Oblong","Rectangle","Diamond"] },
        { id: "rimless",     name: "Rimless",      icon: "💎", desc: "No frame rim, ultra-lightweight",      suits: ["Oval","Heart","Triangle","Diamond"] },
        { id: "semi_rimless",name: "Semi-Rimless", icon: "🔍", desc: "Top-bar only, minimal look",           suits: ["Oval","Square","Heart","Oblong"] },
        { id: "browline",    name: "Browline",     icon: "🎭", desc: "Bold brow bar, 50s classic",           suits: ["Oval","Heart","Square","Round"] },
        { id: "round_metal", name: "Round Metal",  icon: "🔩", desc: "Thin wire, round, timeless",           suits: ["Square","Oblong","Diamond","Rectangle"] },
        { id: "geometric",   name: "Geometric",    icon: "🔷", desc: "Angular multi-sided shapes",           suits: ["Oval","Round","Heart","Oblong"] },
        { id: "butterfly",   name: "Butterfly",    icon: "🦋", desc: "Wide, flared-out dramatic frame",      suits: ["Square","Oblong","Rectangle","Heart"] },
        { id: "shield",      name: "Shield",       icon: "🛡️", desc: "Single large lens, sporty feel",       suits: ["Oval","Square","Rectangle","Oblong"] },
        { id: "wrap",        name: "Wrap",         icon: "🌀", desc: "Curved around the face",               suits: ["Oval","Rectangle","Square","Oblong"] },
        { id: "d_frame",     name: "D-Frame",      icon: "🅓", desc: "Flat top, rounded bottom",             suits: ["Oval","Heart","Round","Diamond"] },
        { id: "hexagonal",   name: "Hexagonal",    icon: "⬡",  desc: "Six-sided bold geometric",             suits: ["Oval","Round","Heart","Diamond"] },
        { id: "octagonal",   name: "Octagonal",    icon: "🛑", desc: "Eight-sided, vintage-modern",          suits: ["Oval","Round","Square","Oblong"] },
        { id: "oversized",   name: "Oversized",    icon: "🌟", desc: "Large statement-making frames",        suits: ["Square","Rectangle","Diamond","Heart"] },
        { id: "sporty",      name: "Sporty",       icon: "🏃", desc: "Active lifestyle performance frames",  suits: ["Oval","Rectangle","Square","Oblong"] },
        { id: "vintage",     name: "Vintage",      icon: "🕰️", desc: "Retro-inspired heritage styles",      suits: ["Oval","Square","Heart","Round"] },
    ];

    /* ── 8 Face Shape Profiles ──────────────────────────────────── */
    var FACE_SHAPES = {
        Oval:      { icon: "🥚", color: "#6366f1", bg: "#eef2ff", desc: "Balanced proportions — suits almost any frame style", best: ["aviator","wayfarer","cat_eye","browline","oversized"] },
        Round:     { icon: "⭕", color: "#f59e0b", bg: "#fffbeb", desc: "Wide cheeks & soft jaw — angular frames add definition", best: ["rectangle","square","wayfarer","geometric","browline"] },
        Square:    { icon: "⬛", color: "#ef4444", bg: "#fef2f2", desc: "Strong jaw & wide forehead — curves soften features",   best: ["round","oval_frame","cat_eye","rimless","round_metal"] },
        Heart:     { icon: "💜", color: "#ec4899", bg: "#fdf4ff", desc: "Wide forehead, narrow chin — balance with wider bottom", best: ["aviator","rimless","rectangle","d_frame","oval_frame"] },
        Oblong:    { icon: "📏", color: "#10b981", bg: "#ecfdf5", desc: "Long & narrow face — add width with oversized styles",  best: ["oversized","butterfly","wayfarer","browline","wrap"] },
        Diamond:   { icon: "💠", color: "#0ea5e9", bg: "#f0f9ff", desc: "Narrow forehead & jaw, wide cheeks — balance at top",  best: ["cat_eye","browline","oval_frame","rimless","hexagonal"] },
        Triangle:  { icon: "🔺", color: "#f97316", bg: "#fff7ed", desc: "Narrow forehead, wide jaw — add emphasis at top",       best: ["cat_eye","browline","d_frame","hexagonal","oversized"] },
        Hexagon:   { icon: "⬡",  color: "#8b5cf6", bg: "#f5f3ff", desc: "Angular with soft edges — geometric styles echo shape", best: ["round","oval_frame","aviator","rimless","vintage"] },
    };
    var FACE_STYLE = function (shape) { return FACE_SHAPES[shape] || FACE_SHAPES.Oval; };

    /* ── State ─────────────────────────────────────────────────── */
    var status = "idle";          // idle | loading | running
    var faceShape = null;
    var frames = [];
    var selectedFrame = null;
    var detecting = false;
    var faceApiLoaded = false;
    var errorMsg = "";
    var activeStyle = "all";
    var loadingModels = false;
    var confidence = 0;

    var animId = null;
    var videoEl = document.getElementById("tryonVideo");
    var canvasEl = document.getElementById("tryonCanvas");

    /* ── Load face-api.js ──────────────────────────────────────── */
    var script = document.createElement("script");
    script.src = "https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js";
    script.onload = function () { faceApiLoaded = true; };
    script.onerror = function () { setError("Failed to load face detection library — check your connection."); };
    document.head.appendChild(script);

    fetchFrames();
    window.addEventListener("beforeunload", stopCamera);

    function setError(msg) {
        errorMsg = msg;
        var box = document.getElementById("tryonError");
        if (msg) { box.textContent = "⚠️ " + msg; box.style.display = "block"; }
        else { box.style.display = "none"; }
    }

    async function fetchFrames() {
        var res = await fetch("/api/public/eyewears?per_page=50");
        var data = await res.json();
        frames = data.data || [];
        renderFrameList();
        renderChips();
    }

    async function loadModels() {
        loadingModels = true;
        updateLoadingText();
        var faceapi = window.faceapi;
        var MODEL_URL = "https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model";
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            ]);
            loadingModels = false;
            return true;
        } catch (e) {
            loadingModels = false;
            return false;
        }
    }

    async function startCamera() {
        if (!faceApiLoaded) { setError("Face detection library still loading…"); return; }
        setStatus("loading");
        setError("");
        var ok = await loadModels();
        if (!ok) { setError("Could not load face detection models. Check your internet connection."); setStatus("idle"); return; }
        try {
            var stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user", width: 1280, height: 720 } });
            videoEl.srcObject = stream;
            videoEl.onloadedmetadata = function () {
                videoEl.play();
                setStatus("running");
                runDetectionLoop();
            };
        } catch (err) {
            setError(err.name === "NotAllowedError" ? "Camera access denied — please allow camera permission in your browser." : "Could not access camera.");
            setStatus("idle");
        }
    }

    function stopCamera() {
        if (animId) cancelAnimationFrame(animId);
        if (videoEl && videoEl.srcObject) {
            videoEl.srcObject.getTracks().forEach(function (t) { t.stop(); });
            videoEl.srcObject = null;
        }
        setStatus("idle");
        detecting = false;
        faceShape = null;
        renderShapeUI();
    }

    function updateLoadingText() {
        var el = document.getElementById("tryonLoadingText");
        if (el) el.textContent = loadingModels ? "Loading AI models…" : "Starting camera…";
    }

    function setStatus(s) {
        status = s;
        var running = s === "running";
        videoEl.style.display = running ? "block" : "none";
        canvasEl.style.display = running ? "block" : "none";
        document.getElementById("tryonIdle").style.display = s === "idle" ? "flex" : "none";
        document.getElementById("tryonLoading").style.display = s === "loading" ? "flex" : "none";
        document.getElementById("tryonRunning").style.display = running ? "block" : "none";
        if (s === "loading") updateLoadingText();
    }

    /* ── Face shape classification (8 types) ─────────────────── */
    function classifyFaceShape(landmarks) {
        var pts = landmarks.positions;
        var jawWidth  = Math.abs(pts[16].x - pts[0].x);
        var foreheadW = Math.abs(pts[19].x - pts[24].x) * 1.1;
        var cheekW    = Math.abs(pts[14].x - pts[2].x);
        var chinW     = Math.abs(pts[11].x - pts[5].x);
        var faceH     = Math.abs(pts[8].y  - pts[19].y);
        var ratio     = faceH / jawWidth;
        var fwRatio   = foreheadW / jawWidth;
        var cwRatio   = cheekW   / jawWidth;
        var chinRatio = chinW    / jawWidth;

        if (ratio > 1.6)                                       return "Oblong";
        if (ratio < 1.05 && cwRatio > 0.92)                    return "Round";
        if (Math.abs(fwRatio - 1) < 0.1 && ratio < 1.25)       return "Square";
        if (fwRatio > 1.2  && chinRatio < 0.75)                return "Heart";
        if (fwRatio < 0.82 && chinRatio < 0.78)                return "Diamond";
        if (fwRatio < 0.85 && jawWidth > cheekW)               return "Triangle";
        if (cwRatio > 1.02 && Math.abs(fwRatio - 0.9) < 0.12)  return "Hexagon";
        return "Oval";
    }

    /* ── Draw face guide oval ─────────────────────────────────── */
    function drawGuideOval(ctx, w, h) {
        ctx.save();
        ctx.strokeStyle = "rgba(255,255,255,0.25)";
        ctx.lineWidth = 2;
        ctx.setLineDash([8, 6]);
        ctx.beginPath();
        ctx.ellipse(w / 2, h * 0.46, w * 0.22, h * 0.36, 0, 0, Math.PI * 2);
        ctx.stroke();
        ctx.restore();
    }

    /* ── Draw glasses overlay ─────────────────────────────────── */
    function drawGlasses(ctx, landmarks, frame) {
        var pts      = landmarks.positions;
        var leftEye  = pts[36];
        var rightEye = pts[45];
        var angle    = Math.atan2(rightEye.y - leftEye.y, rightEye.x - leftEye.x);
        var eyeSpan  = Math.abs(rightEye.x - leftEye.x);
        var eyeW     = eyeSpan * 1.9;
        var eyeH     = eyeW * 0.38;
        var cx       = (leftEye.x + rightEye.x) / 2;
        var cy       = (leftEye.y + rightEye.y) / 2 - eyeH * 0.15;

        if (frame && frame.try_on_image) {
            var img    = new Image();
            img.src    = frame.try_on_image;
            var scale  = frame.try_on_scale   || 1;
            var offX   = frame.try_on_offset_x || 0;
            var offY   = frame.try_on_offset_y || 0;
            ctx.save();
            ctx.translate(cx, cy);
            ctx.rotate(angle);
            ctx.drawImage(img, -(eyeW * scale) / 2 + offX, -eyeH / 2 + offY, eyeW * scale, eyeH * scale);
            ctx.restore();
        } else {
            /* Stylised SVG-style canvas glasses */
            ctx.save();
            ctx.translate(cx, cy);
            ctx.rotate(angle);
            var hw = eyeW / 2;
            var hh = eyeH / 2;
            var lx = -hw * 0.52;
            var rx =  hw * 0.52;
            var rw =  hw * 0.46;

            ctx.strokeStyle = "#1a3a5c";
            ctx.lineWidth   = 3;
            ctx.shadowColor = "rgba(0,0,0,0.5)";
            ctx.shadowBlur  = 6;

            /* left lens */
            ctx.beginPath(); ctx.ellipse(lx, 0, rw, hh, 0, 0, Math.PI * 2); ctx.stroke();
            /* right lens */
            ctx.beginPath(); ctx.ellipse(rx, 0, rw, hh, 0, 0, Math.PI * 2); ctx.stroke();
            /* bridge */
            ctx.beginPath(); ctx.moveTo(lx + rw, 0); ctx.bezierCurveTo(lx + rw + 6, -hh * 0.5, rx - rw - 6, -hh * 0.5, rx - rw, 0); ctx.stroke();
            /* temples */
            ctx.beginPath(); ctx.moveTo(-(hw + rw * 0.5), 0); ctx.lineTo(-(hw + rw * 0.5) - 30, hh * 0.2); ctx.stroke();
            ctx.beginPath(); ctx.moveTo( (hw + rw * 0.5), 0); ctx.lineTo( (hw + rw * 0.5) + 30, hh * 0.2); ctx.stroke();
            ctx.restore();
        }
    }

    /* ── Main detection RAF loop ──────────────────────────────── */
    function runDetectionLoop() {
        var faceapi = window.faceapi;
        var video   = videoEl;
        var canvas  = canvasEl;
        if (!faceapi || !video || !canvas) return;

        var detect = async function () {
            if (!video.paused && !video.ended && video.readyState >= 3) {
                canvas.width  = video.videoWidth  || 640;
                canvas.height = video.videoHeight || 480;
                var ctx = canvas.getContext("2d");
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                /* always draw the face guide */
                drawGuideOval(ctx, canvas.width, canvas.height);

                var detection = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.45 }))
                    .withFaceLandmarks();

                if (detection) {
                    detecting = true;
                    confidence = Math.round(detection.detection.score * 100);
                    faceShape = classifyFaceShape(detection.landmarks);
                    drawGlasses(ctx, detection.landmarks, selectedFrame);
                } else {
                    detecting = false;
                    confidence = 0;
                }
                renderShapeUI();
            }
            animId = requestAnimationFrame(detect);
        };
        animId = requestAnimationFrame(detect);
    }

    /* ── Screenshot ───────────────────────────────────────────── */
    function takeScreenshot() {
        var video  = videoEl;
        var canvas = canvasEl;
        if (!video || !canvas) return;
        var snap = document.createElement("canvas");
        snap.width  = video.videoWidth;
        snap.height = video.videoHeight;
        var ctx = snap.getContext("2d");
        ctx.scale(-1, 1);
        ctx.drawImage(video, -snap.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(canvas, 0, 0);
        var url = snap.toDataURL("image/png");
        document.getElementById("tryonShotImg").src = url;
        document.getElementById("tryonShotDownload").href = url;
        document.getElementById("tryonShotPreview").style.display = "block";
    }

    /* ── Filtered frames ──────────────────────────────────────── */
    function filteredFrames() {
        return frames.filter(function (f) {
            if (activeStyle === "all") return true;
            if (!f.face_shape_tags) return true;
            return f.face_shape_tags.toLowerCase().indexOf(activeStyle.replace("_", " ").toLowerCase()) !== -1;
        });
    }

    function formatPrice(p) {
        return Number(p).toLocaleString("en-IN");
    }

    /* ── Render: category chips ───────────────────────────────── */
    function renderChips() {
        var wrap = document.getElementById("tryonChips");
        wrap.innerHTML = "";
        FRAME_STYLES.forEach(function (s) {
            var isActive = activeStyle === s.id;
            var isRecommended = faceShape && s.suits.indexOf(faceShape) !== -1 && s.id !== "all";
            var btn = document.createElement("button");
            btn.type = "button";
            btn.title = s.desc;
            btn.style.cssText = "flex-shrink:0;border-radius:999px;padding:5px 12px;font-size:0.75rem;font-weight:600;cursor:pointer;white-space:nowrap;transition:all 0.15s;"
                + "background:" + (isActive ? "#6366f1" : isRecommended ? "#eef2ff" : "#f8fafc") + ";"
                + "color:" + (isActive ? "#fff" : isRecommended ? "#6366f1" : "#475569") + ";"
                + "border:" + (isActive ? "1.5px solid #6366f1" : isRecommended ? "1.5px solid #a5b4fc" : "1.5px solid #e2e8f0") + ";";
            btn.textContent = s.icon + " " + s.name + (isRecommended ? " ✨" : "");
            btn.addEventListener("click", function () { setActiveStyle(s.id); });
            wrap.appendChild(btn);
        });
    }

    /* ── Render: frame list ───────────────────────────────────── */
    function renderFrameList() {
        var list = document.getElementById("tryonFrameList");
        var ff = filteredFrames();
        document.getElementById("tryonFrameCount").textContent = ff.length + " frames available";
        list.innerHTML = "";

        /* Default outline */
        var defBtn = document.createElement("button");
        defBtn.type = "button";
        defBtn.className = "tryon-frame-btn" + (selectedFrame === null ? " selected" : "");
        defBtn.style.cssText = "background:" + (selectedFrame === null ? "#eef2ff" : "#f8fafc") + ";border:1.5px solid;border-color:" + (selectedFrame === null ? "#6366f1" : "#e2e8f0") + ";border-radius:12px;padding:10px 14px;cursor:pointer;text-align:left;display:flex;gap:12px;align-items:center";
        defBtn.innerHTML = '<span style="font-size:28px;line-height:1">👓</span>'
            + '<div><div style="font-weight:700;font-size:0.85rem;color:#F8FAFC">Default Outline</div>'
            + '<div style="font-size:0.73rem;color:#8FA3BB">Simple wire-frame overlay</div></div>';
        defBtn.addEventListener("click", function () { setSelectedFrame(null); });
        list.appendChild(defBtn);

        if (ff.length === 0) {
            var empty = document.createElement("div");
            empty.style.cssText = "text-align:center;padding:24px 12px;color:#9ca3af;font-size:0.85rem";
            empty.textContent = "No frames match this style category yet.";
            list.appendChild(empty);
        }

        ff.forEach(function (f) {
            var isSelected = selectedFrame && selectedFrame.id === f.id;
            var btn = document.createElement("button");
            btn.type = "button";
            btn.className = "tryon-frame-btn" + (isSelected ? " selected" : "");
            btn.style.cssText = "background:" + (isSelected ? "#eef2ff" : "#f8fafc") + ";border:1.5px solid;border-color:" + (isSelected ? "#6366f1" : "#e2e8f0") + ";border-radius:12px;padding:10px 12px;cursor:pointer;text-align:left;display:flex;gap:12px;align-items:center";

            var thumb = '<div style="width:56px;height:38px;flex-shrink:0;background:rgba(255,255,255,0.06);border-radius:8px;overflow:hidden;display:flex;align-items:center;justify-content:center;border:1px solid #e5e7eb">'
                + (f.image
                    ? '<img src="' + f.image + '" alt="' + escapeAttr(f.name) + '" style="width:100%;height:100%;object-fit:contain">'
                    : '<span style="font-size:22px">👓</span>')
                + '</div>';

            var info = '<div style="flex:1;min-width:0">'
                + '<div style="font-weight:700;font-size:0.82rem;color:#F8FAFC;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + escapeHtml(f.name) + '</div>'
                + '<div style="font-size:0.72rem;color:#8FA3BB">₹' + formatPrice(f.price) + '</div>'
                + (f.brand ? '<div style="font-size:0.68rem;color:#9ca3af;margin-top:1px">' + escapeHtml(f.brand) + '</div>' : '')
                + '</div>';

            var check = isSelected ? '<span style="color:#6366f1;font-weight:800;font-size:1rem;flex-shrink:0">✓</span>' : '';

            btn.innerHTML = thumb + info + check;
            btn.addEventListener("click", function () { setSelectedFrame(f); });
            list.appendChild(btn);
        });

        /* Selected frame actions */
        var actions = document.getElementById("tryonFrameActions");
        if (selectedFrame) {
            actions.style.display = "flex";
            document.getElementById("tryonViewBtn").href = "/eyewears/" + selectedFrame.slug;
        } else {
            actions.style.display = "none";
        }
    }

    /* ── Render: 21 styles grid ───────────────────────────────── */
    function renderStyleGrid() {
        var grid = document.getElementById("tryonStyleGrid");
        grid.innerHTML = "";
        FRAME_STYLES.filter(function (s) { return s.id !== "all"; }).forEach(function (s) {
            var isActive = activeStyle === s.id;
            var isRecommended = faceShape && s.suits.indexOf(faceShape) !== -1;
            var btn = document.createElement("button");
            btn.type = "button";
            btn.style.cssText = "background:" + (isActive ? "#6366f1" : isRecommended ? "#eef2ff" : "#fff") + ";color:" + (isActive ? "#fff" : "#0f172a") + ";border:1.5px solid " + (isActive ? "#6366f1" : isRecommended ? "#a5b4fc" : "#e2e8f0") + ";border-radius:14px;padding:14px 12px;cursor:pointer;text-align:center;transition:all 0.18s;box-shadow:" + (isActive ? "0 4px 16px rgba(99,102,241,0.35)" : "0 1px 6px rgba(0,0,0,0.04)") + "";
            var html = '<div style="font-size:28px;margin-bottom:6px">' + s.icon + '</div>'
                + '<div style="font-weight:700;font-size:0.82rem">' + s.name + '</div>'
                + '<div style="font-size:0.68rem;color:' + (isActive ? "rgba(255,255,255,0.75)" : "#9ca3af") + ';margin-top:3px;line-height:1.3">' + s.desc + '</div>';
            if (isRecommended && !isActive) {
                html += '<div style="margin-top:6px;background:#c7d2fe;color:#4338ca;border-radius:999px;padding:2px 8px;font-size:0.65rem;font-weight:700">For You ✨</div>';
            }
            btn.innerHTML = html;
            btn.addEventListener("click", function () { setActiveStyle(isActive ? "all" : s.id); });
            grid.appendChild(btn);
        });
    }

    /* ── Render: 8 face shapes grid ───────────────────────────── */
    function renderShapeGrid() {
        var grid = document.getElementById("tryonShapeGrid");
        grid.innerHTML = "";
        Object.keys(FACE_SHAPES).forEach(function (name) {
            var s = FACE_SHAPES[name];
            var isDetected = faceShape === name;
            var card = document.createElement("div");
            card.style.cssText = "background:" + (isDetected ? s.bg : "#fff") + ";border:1.5px solid " + (isDetected ? s.color : "#e2e8f0") + ";border-radius:16px;padding:18px 16px;transition:all 0.2s;box-shadow:" + (isDetected ? "0 4px 20px " + s.color + "30" : "0 1px 6px rgba(0,0,0,0.04)") + "";
            var tags = s.best.slice(0, 3).map(function (sid) {
                var st = findStyle(sid);
                return st ? '<span style="background:' + s.color + '18;color:' + s.color + ';border-radius:999px;padding:2px 9px;font-size:0.68rem;font-weight:600">' + st.icon + ' ' + st.name + '</span>' : '';
            }).join("");
            card.innerHTML =
                '<div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">'
                + '<span style="font-size:28px">' + s.icon + '</span>'
                + '<div><div style="font-weight:800;color:' + (isDetected ? s.color : "#0f172a") + ';font-size:0.9rem">' + name + (isDetected ? " ← You" : "") + '</div></div>'
                + '</div>'
                + '<p style="font-size:0.78rem;color:#8FA3BB;margin:0 0 10px;line-height:1.5">' + s.desc + '</p>'
                + '<div style="display:flex;flex-wrap:wrap;gap:4px">' + tags + '</div>';
            grid.appendChild(card);
        });
    }

    /* ── Render: shape-dependent UI (badges, result card) ─────── */
    function renderShapeUI() {
        var shape = faceShape ? FACE_STYLE(faceShape) : null;

        /* Header badge */
        var headerBadge = document.getElementById("tryonHeaderBadge");
        if (faceShape && shape) {
            headerBadge.style.display = "inline-flex";
            headerBadge.className = "tryon-shape-badge tryon-panel-in";
            headerBadge.style.cssText = "display:inline-flex;align-items:center;gap:8px;margin-top:16px;background:" + shape.bg + ";color:" + shape.color + ";padding:8px 20px;border-radius:999px;font-weight:700;font-size:0.9rem;border:2px solid " + shape.color + "";
            headerBadge.innerHTML = '<span>' + shape.icon + '</span><span>' + faceShape + ' Face Shape Detected</span>'
                + (confidence > 0 ? '<span style="opacity:0.7;font-weight:500;font-size:0.8rem">(' + confidence + '%)</span>' : '');
        } else {
            headerBadge.style.display = "none";
        }

        /* In-frame overlay tags */
        var shapeTag = document.getElementById("tryonShapeTag");
        var confTag = document.getElementById("tryonConfTag");
        var guideTip = document.getElementById("tryonGuideTip");
        if (status === "running") {
            if (detecting && faceShape && shape) {
                shapeTag.style.display = "block";
                shapeTag.style.background = shape.bg;
                shapeTag.style.color = shape.color;
                shapeTag.style.border = "1.5px solid " + shape.color;
                shapeTag.textContent = shape.icon + " " + faceShape;
            } else {
                shapeTag.style.display = "none";
            }
            if (detecting && confidence > 0) {
                confTag.style.display = "block";
                confTag.textContent = "🎯 " + confidence + "%";
            } else {
                confTag.style.display = "none";
            }
            guideTip.style.display = !detecting ? "block" : "none";
        }

        /* Face shape result card */
        var card = document.getElementById("tryonShapeCard");
        if (faceShape && shape) {
            card.style.display = "block";
            card.style.background = shape.bg;
            card.style.border = "1.5px solid " + shape.color + "33";
            var stylesBtns = shape.best.slice(0, 5).map(function (sid) {
                var st = findStyle(sid);
                if (!st) return "";
                var on = activeStyle === sid;
                return '<button type="button" data-style="' + sid + '" style="background:' + (on ? shape.color : "rgba(0,0,0,0.35)") + ';color:' + (on ? "#fff" : shape.color) + ';border:none;border-radius:999px;padding:3px 10px;font-size:0.75rem;font-weight:600;cursor:pointer">' + st.icon + ' ' + st.name + '</button>';
            }).join("");
            card.innerHTML =
                '<div style="display:flex;align-items:center;gap:12px;margin-bottom:10px">'
                + '<span style="font-size:32px">' + shape.icon + '</span>'
                + '<div><div style="font-weight:800;color:' + shape.color + ';font-size:1rem">' + faceShape + ' Face Shape</div>'
                + '<div style="font-size:0.8rem;color:#9FB1C7">' + shape.desc + '</div></div>'
                + '</div>'
                + '<div style="display:flex;flex-wrap:wrap;gap:6px">'
                + '<span style="font-size:0.75rem;color:#9FB1C7;font-weight:600;margin-right:4px">Recommended styles:</span>'
                + stylesBtns + '</div>'
                + '<a href="/recommend?faceShape=' + faceShape + '" style="display:inline-block;margin-top:12px;color:' + shape.color + ';font-weight:600;font-size:0.82rem">→ Get full personalised recommendations</a>';
            card.querySelectorAll("button[data-style]").forEach(function (b) {
                b.addEventListener("click", function () { setActiveStyle(b.getAttribute("data-style")); });
            });
        } else {
            card.style.display = "none";
        }
    }

    /* ── State setters that re-render the relevant pieces ─────── */
    function setActiveStyle(id) {
        activeStyle = id;
        renderChips();
        renderFrameList();
        renderStyleGrid();
        renderShapeUI();
    }

    function setSelectedFrame(f) {
        selectedFrame = f;
        renderFrameList();
    }

    function findStyle(id) {
        for (var i = 0; i < FRAME_STYLES.length; i++) {
            if (FRAME_STYLES[i].id === id) return FRAME_STYLES[i];
        }
        return null;
    }

    function escapeHtml(s) {
        return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }
    function escapeAttr(s) {
        return escapeHtml(s).replace(/"/g, "&quot;");
    }

    /* ── Wire up controls ─────────────────────────────────────── */
    document.getElementById("tryonStartBtn").addEventListener("click", startCamera);
    document.getElementById("tryonStopBtn").addEventListener("click", stopCamera);
    document.getElementById("tryonShotBtn").addEventListener("click", takeScreenshot);
    document.getElementById("tryonShotClose").addEventListener("click", function () {
        document.getElementById("tryonShotPreview").style.display = "none";
    });

    /* ── Initial render ───────────────────────────────────────── */
    renderChips();
    renderFrameList();
    renderStyleGrid();
    renderShapeGrid();
    setStatus("idle");
})();
</script>
@endverbatim
@endpush
