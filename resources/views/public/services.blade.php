@extends('layouts.public')

@section('title', 'Eye Care Services — Free Eye Tests & Lens Fitting | Trinetraa Optician')
@section('meta_description', 'Book professional eye tests, lens fitting, frame repairs and contact lens consultations at Trinetraa Optician Nashik. Expert optometrists. Walk-ins welcome.')
@section('canonical', 'https://trinetraaoptician.com/services')

@section('content')
<style>
/* ── Reset & tokens ── */
.svc-page *, .svc-page *::before, .svc-page *::after {
  box-sizing: border-box;
}

.svc-page {
  --c-navy:    #F8FAFC;
  --c-sapphire:#14B8A6;
  --c-sky:     #2DD4BF;
  --c-ice:     transparent;
  --c-frost:   rgba(20,184,166,0.14);
  --c-ground:  transparent;
  --c-muted:   #9FB1C7;
  --c-border:  rgba(255,255,255,0.14);
  --c-white:   #FFFFFF;
  --c-tag-bg:  rgba(20,184,166,0.14);
  --c-tag-txt: #2DD4BF;
  --c-green:   #3dbf75;

  --font: 'DM Sans', 'Segoe UI', system-ui, -apple-system, sans-serif;
  --max-w: 1140px;
  --radius: 14px;
  --shadow: 0 2px 16px rgba(0,0,0,0.35), 0 1px 4px rgba(0,0,0,0.25);
  --shadow-hover: 0 8px 32px rgba(0,0,0,0.45), 0 2px 8px rgba(0,0,0,0.3);

  font-family: var(--font);
  background: var(--c-ground);
  color: var(--c-navy);
  -webkit-font-smoothing: antialiased;
  padding-top: 88px;
}

/* ── Container ── */
.svc-container {
  max-width: var(--max-w);
  margin: 0 auto;
  padding: 0 24px;
}

/* ── Section ── */
.svc-section {
  padding: 80px 0;
}

/* ── Eyebrow ── */
.svc-eyebrow {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #2DD4BF;
  margin-bottom: 12px;
}

/* ── Section header ── */
.svc-section-hd {
  text-align: center;
  margin-bottom: 48px;
}

.svc-section-hd__title {
  font-size: clamp(1.6rem, 3.5vw, 2.2rem);
  font-weight: 700;
  color: var(--c-navy);
  letter-spacing: -0.02em;
  text-wrap: balance;
  margin: 0 0 12px;
  line-height: 1.18;
}

.svc-section-hd__lead {
  font-size: 1rem;
  color: var(--c-muted);
  max-width: 52ch;
  margin: 0 auto;
  line-height: 1.65;
}

/* ── Divider ── */
.svc-divider {
  border: none;
  border-top: 1px solid var(--c-border);
  margin: 0;
}

/* ════════ HERO ════════ */
.svc-hero {
  background: var(--c-ice);
  border-bottom: 1px solid var(--c-border);
  overflow: hidden;
}

.svc-hero__inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 48px;
  min-height: 380px;
  padding: 64px 24px;
  max-width: var(--max-w);
  margin: 0 auto;
}

.svc-hero__text {
  flex: 1;
  min-width: 0;
}

.svc-hero__title {
  font-size: clamp(2.1rem, 5vw, 3.2rem);
  font-weight: 700;
  line-height: 1.12;
  color: var(--c-navy);
  text-wrap: balance;
  letter-spacing: -0.02em;
  margin: 0 0 20px;
}

.svc-hero__title em {
  font-style: normal;
  color: #2DD4BF;
}

.svc-hero__subtitle {
  font-size: 1.08rem;
  line-height: 1.65;
  color: var(--c-muted);
  max-width: 46ch;
  margin: 0 0 32px;
}

.svc-hero__actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  align-items: center;
}

.svc-hero__visual {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 700px) {
  .svc-hero__inner {
    flex-direction: column;
    text-align: center;
    min-height: unset;
    padding: 48px 24px 40px;
  }
  .svc-hero__subtitle { margin-left: auto; margin-right: auto; }
  .svc-hero__actions { justify-content: center; }
  .svc-hero__visual { display: none; }
}

/* ── Buttons ── */
.btn-primary {
  display: inline-block;
  background: var(--c-sapphire);
  color: var(--c-white);
  font-size: 0.92rem;
  font-weight: 600;
  padding: 13px 28px;
  border-radius: 8px;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: background 0.18s, transform 0.14s;
  font-family: var(--font);
}
.btn-primary:hover { background: #0D9488; transform: translateY(-1px); }
.btn-primary:focus-visible { outline: 3px solid var(--c-sky); outline-offset: 2px; }

.btn-ghost {
  display: inline-block;
  color: #2DD4BF;
  font-size: 0.92rem;
  font-weight: 600;
  padding: 13px 28px;
  border-radius: 8px;
  border: 1.5px solid #2DD4BF;
  text-decoration: none;
  background: transparent;
  cursor: pointer;
  transition: background 0.18s, color 0.18s;
  font-family: var(--font);
}
.btn-ghost:hover { background: var(--c-sapphire); color: var(--c-white); }
.btn-ghost:focus-visible { outline: 3px solid var(--c-sky); outline-offset: 2px; }

/* ════════ SERVICES GRID ════════ */
.svc-grid-section {
  background: var(--c-ground);
}

.svc-api-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.72rem;
  color: var(--c-sky);
  background: var(--c-frost);
  border: 1px solid var(--c-border);
  border-radius: 20px;
  padding: 3px 10px;
  margin-bottom: 16px;
  font-weight: 500;
}

.svc-api-badge__dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--c-green);
  flex-shrink: 0;
}

.svc-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

@media (max-width: 960px) { .svc-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .svc-grid { grid-template-columns: 1fr; } }

/* Service card */
.svc-card {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.14);
  border-radius: var(--radius);
  padding: 28px 24px 24px;
  display: flex;
  flex-direction: column;
  position: relative;
  box-shadow: var(--shadow);
  opacity: 0;
  transform: translateY(18px);
  transition: opacity 0.42s ease, transform 0.42s ease, box-shadow 0.22s, border-color 0.22s;
}

.svc-card--visible {
  opacity: 1;
  transform: none;
}

@media (prefers-reduced-motion: reduce) {
  .svc-card { opacity: 1; transform: none; transition: box-shadow 0.22s, border-color 0.22s; }
}

.svc-card:hover {
  box-shadow: var(--shadow-hover);
  border-color: rgba(45,212,191,0.4);
}

.svc-card__tag {
  position: absolute;
  top: 16px;
  right: 16px;
  background: var(--c-tag-bg);
  color: var(--c-tag-txt);
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 3px 9px;
  border-radius: 20px;
}

.svc-card__icon {
  font-size: 2rem;
  line-height: 1;
  margin-bottom: 14px;
}

.svc-card__title {
  font-size: 1.02rem;
  font-weight: 700;
  color: var(--c-navy);
  margin: 0 0 10px;
  line-height: 1.3;
}

.svc-card__desc {
  font-size: 0.88rem;
  color: var(--c-muted);
  line-height: 1.6;
  flex: 1;
  margin: 0 0 20px;
}

.svc-card__footer {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.svc-card__price {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.svc-card__price-amount {
  font-size: 1.3rem;
  font-weight: 700;
  color: var(--c-navy);
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.01em;
}

.svc-card__price-note {
  font-size: 0.76rem;
  color: var(--c-muted);
  line-height: 1.4;
}

.svc-card__price-duration {
  font-size: 0.76rem;
  color: var(--c-sky);
  font-weight: 600;
}

.svc-card__book-btn {
  display: block;
  text-align: center;
  background: var(--c-frost);
  color: #2DD4BF;
  font-size: 0.86rem;
  font-weight: 700;
  padding: 10px 18px;
  border-radius: 8px;
  text-decoration: none;
  border: 1.5px solid var(--c-border);
  transition: background 0.16s, color 0.16s, border-color 0.16s;
  font-family: var(--font);
}
.svc-card__book-btn:hover {
  background: var(--c-sapphire);
  color: var(--c-white);
  border-color: var(--c-sapphire);
}
.svc-card__book-btn:focus-visible { outline: 3px solid var(--c-sky); outline-offset: 2px; }

/* ════════ HOW IT WORKS ════════ */
.svc-how {
  background: var(--c-ice);
  border-top: 1px solid var(--c-border);
  border-bottom: 1px solid var(--c-border);
}

.svc-steps {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0;
  position: relative;
}

.svc-steps::before {
  content: '';
  position: absolute;
  top: 28px;
  left: calc(12.5% + 20px);
  right: calc(12.5% + 20px);
  height: 2px;
  background: linear-gradient(90deg, var(--c-sapphire) 0%, var(--c-sky) 100%);
  opacity: 0.3;
  pointer-events: none;
}

@media (max-width: 768px) {
  .svc-steps { grid-template-columns: 1fr 1fr; gap: 28px 16px; }
  .svc-steps::before { display: none; }
}
@media (max-width: 480px) {
  .svc-steps { grid-template-columns: 1fr; }
}

.svc-step {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 0 20px;
}

.svc-step__num {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--c-sapphire);
  color: var(--c-white);
  font-size: 1.1rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 18px;
  flex-shrink: 0;
  box-shadow: 0 4px 16px rgba(20,184,166,0.28);
  position: relative;
  z-index: 1;
}

.svc-step__title {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--c-navy);
  margin: 0 0 8px;
  line-height: 1.3;
}

.svc-step__desc {
  font-size: 0.84rem;
  color: var(--c-muted);
  line-height: 1.6;
  margin: 0;
}

/* ════════ FAQ ════════ */
.svc-faq {
  background: var(--c-ground);
}

.svc-faq__list {
  max-width: 720px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.faq-item {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.14);
  border-radius: 10px;
  overflow: hidden;
  transition: border-color 0.18s, box-shadow 0.18s;
}

.faq-item--open {
  border-color: var(--c-sapphire);
  box-shadow: 0 2px 16px rgba(20,184,166,0.1);
}

.faq-item__question {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 18px 20px;
  background: none;
  border: none;
  text-align: left;
  cursor: pointer;
  font-family: var(--font);
  font-size: 0.94rem;
  font-weight: 600;
  color: var(--c-navy);
  line-height: 1.4;
  transition: color 0.15s;
}

.faq-item__question:hover { color: #2DD4BF; }
.faq-item__question:focus-visible { outline: 3px solid var(--c-sky); outline-offset: -2px; }

.faq-item__chevron {
  font-size: 1.3rem;
  font-weight: 400;
  color: #2DD4BF;
  flex-shrink: 0;
  line-height: 1;
  width: 22px;
  text-align: center;
}

.faq-item__answer {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.28s ease;
}

.faq-item--open .faq-item__answer {
  max-height: 240px;
}

@media (prefers-reduced-motion: reduce) {
  .faq-item__answer { transition: none; }
}

.faq-item__answer p {
  margin: 0 20px 18px;
  padding-top: 14px;
  border-top: 1px solid var(--c-border);
  font-size: 0.88rem;
  color: var(--c-muted);
  line-height: 1.65;
}

/* ════════ CTA ════════ */
.svc-cta {
  background: linear-gradient(135deg, #0F766E 0%, #14B8A6 100%);
  padding: 80px 0;
}

.svc-cta__inner {
  text-align: center;
}

.svc-cta__eyebrow {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--c-sky);
  margin-bottom: 14px;
}

.svc-cta__title {
  font-size: clamp(1.7rem, 4vw, 2.6rem);
  font-weight: 700;
  color: var(--c-white);
  text-wrap: balance;
  letter-spacing: -0.02em;
  margin: 0 0 16px;
}

.svc-cta__subtitle {
  font-size: 1rem;
  color: rgba(255,255,255,0.72);
  max-width: 46ch;
  margin: 0 auto 36px;
  line-height: 1.6;
}

.btn-cta {
  display: inline-block;
  background: var(--c-white);
  color: #0F766E;
  font-size: 0.96rem;
  font-weight: 700;
  padding: 15px 36px;
  border-radius: 8px;
  text-decoration: none;
  font-family: var(--font);
  transition: background 0.18s, transform 0.14s, box-shadow 0.18s;
  box-shadow: 0 4px 20px rgba(0,0,0,0.18);
}
.btn-cta:hover { background: rgba(255,255,255,0.9); transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,0.22); }
.btn-cta:focus-visible { outline: 3px solid var(--c-sky); outline-offset: 2px; }

.svc-cta__note {
  margin-top: 16px;
  font-size: 0.8rem;
  color: rgba(255,255,255,0.5);
}
</style>

<div class="svc-page">

  {{-- ── Hero ── --}}
  <section class="svc-hero">
    <div class="svc-hero__inner">
      <div class="svc-hero__text">
        <span class="svc-eyebrow">Visionelle Eye Care</span>
        <h1 class="svc-hero__title">
          Expert eye care,<br>
          <em>tailored to you</em>
        </h1>
        <p class="svc-hero__subtitle">
          From comprehensive eye examinations to specialised pediatric care and
          retinal screening — our clinic brings precision diagnostics and
          personalised optical solutions under one roof.
        </p>
        <div class="svc-hero__actions">
          <a href="/appointment" class="btn-primary">Book a Free Eye Test</a>
          <a href="#services" class="btn-ghost">View Services</a>
        </div>
      </div>
      <div class="svc-hero__visual">
        <canvas id="svcApertureCanvas" aria-hidden="true" style="width:280px;height:280px;opacity:0.9"></canvas>
      </div>
    </div>
  </section>

  {{-- ── Services Grid ── --}}
  <section class="svc-section svc-grid-section" id="services">
    <div class="svc-container">
      <div class="svc-section-hd">
        <span class="svc-eyebrow">What we offer</span>
        <div id="svcApiBadgeWrap" style="display:none;justify-content:center;margin-bottom:8px">
          <span class="svc-api-badge">
            <span class="svc-api-badge__dot"></span>
            Live data from clinic
          </span>
        </div>
        <h2 class="svc-section-hd__title">Our Services</h2>
        <p class="svc-section-hd__lead">
          Professional eye care services delivered with modern diagnostic equipment
          and genuine attention to each patient's needs.
        </p>
      </div>
      <div class="svc-grid" id="svcGrid"></div>
    </div>
  </section>

  <hr class="svc-divider">

  {{-- ── How It Works ── --}}
  <section class="svc-section svc-how" id="how-it-works">
    <div class="svc-container">
      <div class="svc-section-hd">
        <span class="svc-eyebrow">Your visit</span>
        <h2 class="svc-section-hd__title">How it works</h2>
        <p class="svc-section-hd__lead">
          A straightforward process designed around your time and comfort.
        </p>
      </div>
      <div class="svc-steps">
        @php
          $steps = [
            ['number' => 1, 'title' => 'Book Your Appointment', 'description' => 'Choose a convenient time online or call us directly. Walk-ins are welcome, but appointments ensure shorter wait times.'],
            ['number' => 2, 'title' => 'Initial Consultation', 'description' => 'Our optometrist reviews your eye history, current symptoms, and any concerns before the examination begins.'],
            ['number' => 3, 'title' => 'Thorough Examination', 'description' => 'We conduct a full assessment — refraction, slit lamp, eye pressure, and retinal imaging as required.'],
            ['number' => 4, 'title' => 'Prescription & Follow-up', 'description' => 'Receive your prescription, frame or lens recommendations, and a clear plan for your ongoing eye health.'],
          ];
        @endphp
        @foreach($steps as $step)
          <div class="svc-step">
            <div class="svc-step__num">{{ $step['number'] }}</div>
            <h3 class="svc-step__title">{{ $step['title'] }}</h3>
            <p class="svc-step__desc">{{ $step['description'] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <hr class="svc-divider">

  {{-- ── FAQ ── --}}
  <section class="svc-section svc-faq" id="faq">
    <div class="svc-container">
      <div class="svc-section-hd">
        <span class="svc-eyebrow">Common questions</span>
        <h2 class="svc-section-hd__title">Frequently asked questions</h2>
        <p class="svc-section-hd__lead">
          Answers to the questions our patients ask most often.
        </p>
      </div>
      <div class="svc-faq__list" role="list">
        @php
          $faqs = [
            ['q' => 'How often should I get an eye exam?', 'a' => 'Every 1–2 years for adults in good ocular health. Children and seniors should have annual examinations, as early detection of changes makes a significant difference in outcomes.'],
            ['q' => 'What is included in a comprehensive eye test?', 'a' => 'Visual acuity testing, objective and subjective refraction, slit lamp biomicroscopy, intraocular pressure measurement, and a retinal health check. We use both auto-refractometer and fundus photography.'],
            ['q' => 'Do you accept walk-ins?', 'a' => 'Yes — walk-ins are always welcome at our clinic. Booking an appointment in advance is recommended to reduce your waiting time, especially on weekends.'],
            ['q' => 'What frame brands do you carry?', 'a' => 'We stock Ray-Ban, Oakley, Carrera, Titan, Fastrack, VDGE, Eye Plus, and over 15 additional brands across all price ranges, from everyday essentials to premium designer frames.'],
            ['q' => 'Can I get same-day glasses?', 'a' => 'Yes, for standard stock lenses we can often complete your glasses the same day. Custom progressive lenses and special coatings typically take 3–5 working days.'],
          ];
        @endphp
        @foreach($faqs as $item)
          <div class="faq-item">
            <button class="faq-item__question" type="button" aria-expanded="false">
              <span>{{ $item['q'] }}</span>
              <span class="faq-item__chevron" aria-hidden="true">+</span>
            </button>
            <div class="faq-item__answer" aria-hidden="true">
              <p>{{ $item['a'] }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ── CTA ── --}}
  <section class="svc-cta" id="book">
    <div class="svc-container">
      <div class="svc-cta__inner">
        <span class="svc-cta__eyebrow">Start here</span>
        <h2 class="svc-cta__title">Book your free eye test today</h2>
        <p class="svc-cta__subtitle">
          A comprehensive eye examination costs ₹200 — fully adjustable against
          your frame or lens purchase. Early detection makes a lasting difference.
        </p>
        <a href="/appointment" class="btn-cta">
          Book Free Eye Test
        </a>
        <p class="svc-cta__note">No referral needed · Walk-ins welcome</p>
      </div>
    </div>
  </section>

</div>
@endsection

@push('scripts')
<script>
@verbatim
(function () {
  // ─── Static fallback data ───
  var STATIC_SERVICES = [
    { id: 1, icon: "🔍", title: "Comprehensive Eye Examination", description: "Complete vision assessment using slit lamp, auto-refractometer, and fundus examination. A thorough check for refractive errors, eye pressure, and retinal health.", duration: "30 min", price: "₹200", priceNote: "adjustable against frame purchase", tag: "Most Popular" },
    { id: 2, icon: "👓", title: "Prescription Glasses", description: "Customised single vision, bifocal, and progressive lenses with anti-glare, blue light filter, and photochromic options to suit your lifestyle.", duration: null, price: null, priceNote: null, tag: null },
    { id: 3, icon: "👁️", title: "Contact Lens Fitting", description: "Trial fitting for daily, monthly, and toric contact lenses with proper care guidance and a personalised wearing schedule.", duration: null, price: null, priceNote: null, tag: null },
    { id: 4, icon: "🧒", title: "Pediatric Eye Care", description: "Specialised vision care for children including amblyopia (lazy eye) screening, visual development assessment, and orthoptic exercises.", duration: null, price: null, priceNote: null, tag: null },
    { id: 5, icon: "🔬", title: "Retinal Screening", description: "Digital retinal photography for early detection of diabetic retinopathy, glaucoma, and macular degeneration — vital for long-term eye health.", duration: null, price: null, priceNote: null, tag: null },
    { id: 6, icon: "🕶️", title: "Low Vision Aid", description: "Assessment and prescription of magnifiers and assistive devices for visually impaired patients, helping maximise remaining functional vision.", duration: null, price: null, priceNote: null, tag: null }
  ];

  var REDUCED = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  // ─── Service card rendering ───
  function buildCard(service, index) {
    var card = document.createElement("div");
    card.className = "svc-card";
    card.style.transitionDelay = (index * 60) + "ms";

    var price = service.price;
    var priceNote = service.priceNote != null ? service.priceNote : service.price_note;
    var duration = service.duration;
    var tag = service.tag;
    var icon = service.icon || "👓";

    var html = "";
    if (tag) html += '<span class="svc-card__tag">' + esc(tag) + '</span>';
    html += '<div class="svc-card__icon" aria-hidden="true">' + esc(icon) + '</div>';
    html += '<h3 class="svc-card__title">' + esc(service.title) + '</h3>';
    html += '<p class="svc-card__desc">' + esc(service.description) + '</p>';
    html += '<div class="svc-card__footer">';
    if (price) {
      html += '<div class="svc-card__price">';
      html += '<span class="svc-card__price-amount">' + esc(price) + '</span>';
      if (priceNote) html += '<span class="svc-card__price-note">' + esc(priceNote) + '</span>';
      if (duration) html += '<span class="svc-card__price-duration">' + esc(duration) + '</span>';
      html += '</div>';
    }
    html += '<a href="/appointment" class="svc-card__book-btn">Book Now</a>';
    html += '</div>';
    card.innerHTML = html;
    return card;
  }

  function renderServices(services) {
    var grid = document.getElementById("svcGrid");
    if (!grid) return;
    grid.innerHTML = "";
    var cards = [];
    services.forEach(function (service, i) {
      var card = buildCard(service, i);
      grid.appendChild(card);
      cards.push(card);
    });

    if (REDUCED) {
      cards.forEach(function (c) { c.classList.add("svc-card--visible"); });
      return;
    }

    // IntersectionObserver reveal, staggered by index
    if ("IntersectionObserver" in window) {
      cards.forEach(function (card, index) {
        var observer = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              setTimeout(function () { card.classList.add("svc-card--visible"); }, index * 80);
              observer.disconnect();
            }
          });
        }, { threshold: 0.1 });
        observer.observe(card);
      });
    } else {
      cards.forEach(function (c) { c.classList.add("svc-card--visible"); });
    }
  }

  // ─── FAQ accordion ───
  function initFaq() {
    var items = document.querySelectorAll(".svc-faq .faq-item");
    items.forEach(function (item) {
      var btn = item.querySelector(".faq-item__question");
      var chevron = item.querySelector(".faq-item__chevron");
      var answer = item.querySelector(".faq-item__answer");
      if (!btn) return;
      btn.addEventListener("click", function () {
        var open = item.classList.toggle("faq-item--open");
        btn.setAttribute("aria-expanded", open ? "true" : "false");
        if (chevron) chevron.textContent = open ? "−" : "+";
        if (answer) answer.setAttribute("aria-hidden", open ? "false" : "true");
      });
    });
  }

  // ─── Aperture canvas ───
  function initAperture() {
    var canvas = document.getElementById("svcApertureCanvas");
    if (!canvas) return;
    var ctx = canvas.getContext("2d");
    var t = 0;
    var W = 320, H = 320;
    canvas.width = W;
    canvas.height = H;
    var prefersReduced = REDUCED;
    var raf = null;

    function draw() {
      ctx.clearRect(0, 0, W, H);
      var cx = W / 2, cy = H / 2;
      var blades = 8, outerR = 120, innerR = 38;
      var rotation = t * 0.003;

      ctx.beginPath();
      ctx.arc(cx, cy, outerR + 8, 0, Math.PI * 2);
      ctx.strokeStyle = "rgba(20,184,166,0.18)";
      ctx.lineWidth = 2;
      ctx.stroke();

      for (var i = 0; i < blades; i++) {
        var angle = ((Math.PI * 2) / blades) * i + rotation;
        var open = 0.38 + 0.06 * Math.sin(t * 0.012);
        var spread = (Math.PI * 2) / blades;
        var a1 = angle - spread * open;
        var a2 = angle + spread * open;

        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, outerR, a1, a2);
        ctx.closePath();
        var grad = ctx.createLinearGradient(
          cx + Math.cos(angle) * innerR,
          cy + Math.sin(angle) * innerR,
          cx + Math.cos(angle) * outerR,
          cy + Math.sin(angle) * outerR
        );
        grad.addColorStop(0, "rgba(45,212,191,0.22)");
        grad.addColorStop(1, "rgba(20,184,166,0.08)");
        ctx.fillStyle = grad;
        ctx.fill();
        ctx.strokeStyle = "rgba(20,184,166,0.28)";
        ctx.lineWidth = 1;
        ctx.stroke();
      }

      var lensGrad = ctx.createRadialGradient(cx, cy, 0, cx, cy, innerR);
      lensGrad.addColorStop(0, "rgba(248,250,252,0.9)");
      lensGrad.addColorStop(0.6, "rgba(45,212,191,0.12)");
      lensGrad.addColorStop(1, "rgba(20,184,166,0.22)");
      ctx.beginPath();
      ctx.arc(cx, cy, innerR, 0, Math.PI * 2);
      ctx.fillStyle = lensGrad;
      ctx.fill();
      ctx.strokeStyle = "rgba(20,184,166,0.35)";
      ctx.lineWidth = 1.5;
      ctx.stroke();

      ctx.beginPath();
      ctx.arc(cx - 10, cy - 10, 7, 0, Math.PI * 2);
      ctx.fillStyle = "rgba(255,255,255,0.55)";
      ctx.fill();

      t++;
      if (!prefersReduced) {
        raf = requestAnimationFrame(draw);
      }
    }
    draw();
  }

  document.addEventListener("DOMContentLoaded", function () {
    initFaq();
    initAperture();
    renderServices(STATIC_SERVICES);

    fetch("/api/public/services")
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (json) {
        var data = json && json.data ? json.data : json;
        if (data && Array.isArray(data) && data.length > 0) {
          renderServices(data);
          var badge = document.getElementById("svcApiBadgeWrap");
          if (badge) badge.style.display = "flex";
        }
      })
      .catch(function () {});
  });
})();
@endverbatim
</script>
@endpush
