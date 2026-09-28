@extends('layouts.public')

@section('title', 'Trinetraa Optician Nashik — Premium Eyewear & Eye Care')
@section('meta_description', 'Trinetraa Optician Nashik — 1000+ branded frames, free eye tests, prescription glasses, sunglasses & contact lenses. Trusted optician since 2015. Visit us today.')
@section('og_image', 'https://trinetraaoptician.com/icons/icon-512.png')
@section('canonical', 'https://trinetraaoptician.com/')

@php
    $STATIC_BRANDS = [
        'Ray-Ban', 'Lenskart', 'Titan Eyeplus', 'Fastrack', 'Vogue Eyewear',
        'Carrera', 'Oakley', 'Tommy Hilfiger', 'Police', 'Fossil',
        'Vincent Chase', 'John Jacobs', 'Prada', 'Gucci', 'Versace',
        'Persol', 'Polaroid', 'Scott', 'Maui Jim', 'Silhouette',
    ];

    $SERVICES = [
        [
            'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="5"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="2" y1="12" x2="5" y2="12"/><line x1="19" y1="12" x2="22" y2="12"/></svg>',
            'title' => 'Eye Examination',
            'desc' => 'Comprehensive vision testing using digital phoropters and slit-lamp biomicroscopy for precise diagnosis.',
        ],
        [
            'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
            'title' => 'Prescription Glasses',
            'desc' => 'Single vision, bifocal, and progressive lenses crafted with leading optical labs for crystal-clear vision.',
        ],
        [
            'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12a4 4 0 0 1 8 0"/><circle cx="12" cy="12" r="1"/></svg>',
            'title' => 'Contact Lenses',
            'desc' => 'Daily, monthly, and toric contact lenses with expert fitting and aftercare guidance from our optometrists.',
        ],
        [
            'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            'title' => 'Pediatric Eye Care',
            'desc' => 'Specialized vision screenings for children to detect and correct developmental vision problems early.',
        ],
        [
            'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
            'title' => 'Low Vision Aid',
            'desc' => 'Magnifiers, telescopic aids, and specialized devices to maximize remaining vision for low vision patients.',
        ],
        [
            'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
            'title' => 'Retinal Screening',
            'desc' => 'Digital fundus photography and retinal imaging for early detection of diabetic retinopathy and macular conditions.',
        ],
    ];

    $PROCESS_STEPS = [
        ['n' => '01', 'title' => 'Book Appointment', 'desc' => "Schedule online or call us. We'll confirm your slot within the hour."],
        ['n' => '02', 'title' => 'Eye Examination', 'desc' => 'Comprehensive digital testing with our certified optometrists.'],
        ['n' => '03', 'title' => 'Get Prescription', 'desc' => 'Receive a detailed prescription tailored to your exact vision needs.'],
        ['n' => '04', 'title' => 'Choose Frames', 'desc' => 'Browse 20+ premium brands and walk out with your perfect pair.'],
    ];

    $ABOUT_BULLETS = [
        'Advanced digital phoropters and retinal imaging equipment',
        'Certified optometrists with 10+ years of clinical experience',
        'Same-day spectacle delivery for standard prescriptions',
    ];

    $STATS = [
        ['num' => '10+', 'label' => 'Years Experience'],
        ['num' => '500+', 'label' => 'Happy Patients'],
        ['num' => '20+', 'label' => 'Frame Brands'],
        ['num' => '8+', 'label' => 'Awards Won'],
    ];
@endphp

@section('content')
<style>
    .reveal {
      opacity: 0;
      transform: translateY(24px);
      transition: opacity 0.6s ease, transform 0.6s ease;
    }
    .reveal.revealed {
      opacity: 1;
      transform: none;
    }
    .reveal { transition-delay: var(--delay, 0s); }

    /* Brand marquee */
    @keyframes marquee {
      from { transform: translateX(0); }
      to   { transform: translateX(-50%); }
    }
    .trin-marquee-track {
      display: flex;
      gap: 0;
      animation: marquee 32s linear infinite;
      width: max-content;
    }
    .trin-marquee-track:hover { animation-play-state: paused; }

    /* Stat bar counter */
    .trin-stat-number {
      font-family: var(--pp-font-heading);
      font-size: clamp(2rem, 4vw, 2.8rem);
      font-weight: 800;
      color: #2DD4BF;
      letter-spacing: -0.04em;
      line-height: 1;
    }

    /* Service card hover */
    .trin-service-card {
      position: relative;
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.14);
      border-radius: 16px;
      padding: 2rem 1.75rem;
      transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
      cursor: default;
      overflow: hidden;
    }
    .trin-service-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 36px rgba(20,184,166,0.12);
      border-color: rgba(45,212,191,0.5);
    }
    .trin-service-icon {
      width: 52px; height: 52px;
      border-radius: 12px;
      background: rgba(20,184,166,0.14);
      color: #2DD4BF;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 1.25rem;
    }

    /* Process step */
    .trin-step-num {
      font-family: var(--pp-font-heading);
      font-size: 3rem;
      font-weight: 900;
      color: rgba(45,212,191,0.28);
      line-height: 1;
      margin-bottom: 0.5rem;
      letter-spacing: -0.05em;
    }

    /* About mosaic */
    .trin-mosaic {
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-template-rows: auto auto;
      gap: 1rem;
    }
    .trin-mosaic-main {
      grid-column: 1 / 3;
      border-radius: 16px;
      overflow: hidden;
      height: 220px;
    }
    .trin-mosaic-secondary {
      border-radius: 12px;
      overflow: hidden;
      height: 160px;
    }
    .trin-mosaic-badge {
      background: linear-gradient(135deg, #0F766E 0%, #0D9488 100%);
      color: #fff;
      border-radius: 12px;
      height: 160px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 1.25rem;
    }
    .trin-mosaic-badge strong {
      font-size: 2rem;
      font-weight: 900;
      letter-spacing: -0.04em;
      line-height: 1;
    }
    .trin-mosaic-badge span {
      font-size: 0.78rem;
      opacity: 0.85;
      margin-top: 0.35rem;
      line-height: 1.4;
    }

    /* Stats bar */
    .trin-stats-bar {
      background: rgba(255,255,255,0.04);
      padding: 3rem 0;
    }
    .trin-stats-grid {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 2rem;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 0;
    }
    .trin-stat-item {
      text-align: center;
      padding: 1.5rem 1rem;
      border-right: 1px solid rgba(255,255,255,0.08);
    }
    .trin-stat-item:last-child { border-right: none; }
    .trin-stat-label {
      font-size: 0.8rem;
      color: rgba(255,255,255,0.5);
      margin-top: 0.35rem;
      letter-spacing: 0.04em;
    }

    /* Review card override for blue accent */
    .trin-review-card {
      position: relative;
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.14);
      border-radius: 16px;
      padding: 1.75rem;
      transition: transform 0.28s ease, box-shadow 0.28s ease;
      overflow: hidden;
    }
    .trin-review-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 30px rgba(20,184,166,0.1);
    }
    .trin-review-stars { color: #F5B841; font-size: 0.95rem; display: flex; gap: 2px; margin-bottom: 0.75rem; }
    .trin-review-text { font-size: 0.9rem; color: #9FB1C7; line-height: 1.75; font-style: italic; margin-bottom: 1.25rem; }
    .trin-review-author { display: flex; align-items: center; gap: 0.75rem; }
    .trin-review-avatar {
      width: 40px; height: 40px; border-radius: 50%;
      background: linear-gradient(135deg, #0F766E 0%, #0D9488 100%); color: #fff;
      font-weight: 700; font-size: 0.9rem;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }

    /* Testimonial mini card in hero */
    .trin-hero-testimonial {
      background: rgba(255,255,255,0.06);
      border-radius: 14px;
      padding: 0.85rem 1rem;
      box-shadow: 0 8px 28px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      gap: 0.65rem;
      max-width: 260px;
    }
    .trin-hero-hours {
      background: rgba(20,184,166,0.14);
      border: 1px solid rgba(255,255,255,0.14);
      border-radius: 12px;
      padding: 0.75rem 1rem;
      margin-top: 0.75rem;
      display: flex;
      align-items: center;
      gap: 0.6rem;
      max-width: 260px;
    }

    /* Bullet check */
    .trin-check-item {
      display: flex;
      align-items: flex-start;
      gap: 0.75rem;
      margin-bottom: 0.85rem;
    }
    .trin-check-icon {
      width: 22px; height: 22px;
      border-radius: 50%;
      background: rgba(20,184,166,0.14);
      color: #2DD4BF;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      font-size: 0.75rem;
      font-weight: 800;
      margin-top: 1px;
    }

    /* CTA section */
    .trin-cta-section {
      background: linear-gradient(135deg, rgba(15,118,110,0.35) 0%, rgba(11,18,32,0.15) 100%), rgba(255,255,255,0.03);
      padding: 5rem 0;
      text-align: center;
    }

    @media (max-width: 900px) {
      .trin-stats-grid { grid-template-columns: repeat(2, 1fr); }
      .trin-stat-item:nth-child(2) { border-right: none; }
      .trin-stat-item { border-bottom: 1px solid rgba(255,255,255,0.08); }
      .trin-stat-item:last-child { border-bottom: none; }
    }
    @media (max-width: 640px) {
      .trin-stats-grid { grid-template-columns: repeat(2, 1fr); }
      .trin-mosaic-main { height: 160px; }
      .trin-mosaic-secondary, .trin-mosaic-badge { height: 120px; }
    }

    @media(max-width:768px){.hero-grid{grid-template-columns:1fr!important}.hero-grid>div:first-child{order:2}.hero-grid>div:last-child{order:1}}
    @media (max-width: 768px) {
      .about-grid { grid-template-columns: 1fr !important; gap: 2.5rem !important; }
    }
    @media (max-width: 900px) { .services-grid { grid-template-columns: repeat(2, 1fr) !important; } }
    @media (max-width: 560px) { .services-grid { grid-template-columns: 1fr !important; } }
    @media (max-width: 768px) {
      .process-grid { grid-template-columns: repeat(2, 1fr) !important; }
      .process-connector { display: none; }
    }
    @media (max-width: 480px) {
      .process-grid { grid-template-columns: 1fr !important; }
    }
</style>

{{-- ── 1. HERO ─────────────────────────────────────── --}}
<section style="background:transparent;padding-top:calc(64px + 4rem);padding-bottom:4rem;overflow:hidden">
    <div class="pp-container">
        <div class="hero-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:3.5rem;align-items:center">
            {{-- Left --}}
            <div style="max-width:540px">
                <div class="reveal" style="--delay:0s;margin-bottom:1.5rem">
                    <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.3rem 0.9rem;background:rgba(20,184,166,0.12);color:#5EEAD4;border:1px solid rgba(20,184,166,0.32);border-radius:999px;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em">Optical Clinic &amp; Eye Center</span>
                </div>
                <h1 class="reveal" style="--delay:0.08s;font-family:var(--pp-font-heading);font-size:clamp(2.1rem, 4vw, 3.2rem);font-weight:800;color:#F8FAFC;line-height:1.1;letter-spacing:-0.03em;margin-bottom:1.25rem;text-wrap:balance">
                    See the World Clearly<br>
                    <span style="color:#2DD4BF">with Trinetraa</span>
                </h1>
                <p class="reveal" style="--delay:0.16s;font-size:1rem;color:#9FB1C7;line-height:1.75;margin-bottom:2rem;max-width:420px">
                    Nashik's trusted optical clinic offering precision eye care, premium eyewear, and personalized vision solutions since 2015.
                </p>
                <div class="reveal" style="--delay:0.24s;display:flex;gap:0.75rem;flex-wrap:wrap;margin-bottom:2.5rem">
                    <a href="/appointment" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.8rem 1.75rem;background:#14B8A6;color:#fff;border-radius:999px;font-family:var(--pp-font-heading);font-size:0.875rem;font-weight:600;text-decoration:none;transition:background 0.2s, transform 0.2s, box-shadow 0.2s;box-shadow:0 4px 16px rgba(20,184,166,0.35)"
                        onmouseenter="this.style.background='#0D9488';this.style.transform='translateY(-2px)'"
                        onmouseleave="this.style.background='#2DD4BF';this.style.transform='none'">
                        Book Appointment
                    </a>
                    <a href="/eyewears" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.8rem 1.75rem;background:transparent;color:#F8FAFC;border:1.5px solid rgba(255,255,255,0.35);border-radius:999px;font-family:var(--pp-font-heading);font-size:0.875rem;font-weight:600;text-decoration:none;transition:border-color 0.2s, background 0.2s, color 0.2s"
                        onmouseenter="this.style.borderColor='#2DD4BF';this.style.color='#2DD4BF'"
                        onmouseleave="this.style.borderColor='rgba(255,255,255,0.35)';this.style.color='#0F172A'">
                        Browse Eyewear
                    </a>
                </div>

                {{-- Mini testimonial + hours widgets --}}
                <div class="trin-hero-testimonial reveal" style="--delay:0.32s">
                    <div style="width:38px;height:38px;border-radius:50%;background:#14B8A6;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;flex-shrink:0">R</div>
                    <div>
                        <div style="display:flex;gap:1px;color:#F59E0B;font-size:0.75rem;margin-bottom:2px">
                            <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                        </div>
                        <div style="font-size:0.78rem;font-weight:600;color:#F8FAFC;line-height:1.3">
                            "Best optical clinic in Nashik!"
                        </div>
                        <div style="font-size:0.68rem;color:#94A3B8;margin-top:1px">Rahul M., Nashik Road</div>
                    </div>
                </div>
                <div class="trin-hero-hours reveal" style="--delay:0.38s">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2DD4BF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <div style="font-size:0.78rem;color:#B8C6D8">
                        <strong style="display:block;font-weight:700;color:#F8FAFC">Working Hours</strong>
                        Mon–Sat: 9:30am–8:30pm &nbsp;|&nbsp; Sun: 10am–6pm
                    </div>
                </div>
            </div>

            {{-- Right — hero image card --}}
            <div class="reveal" style="--delay:0.2s;position:relative">
                <div style="border-radius:24px;overflow:hidden;aspect-ratio:7/8;box-shadow:0 32px 80px rgba(20,184,166,0.18), 0 8px 24px rgba(0,0,0,0.08);position:relative">
                    <img
                        src="https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=700&h=800&fit=crop&auto=format&q=80"
                        alt="Woman wearing stylish glasses"
                        style="width:100%;height:100%;object-fit:cover;object-position:center top;display:block"
                        loading="eager">
                    {{-- Floating badge --}}
                    <div style="position:absolute;bottom:1.5rem;right:1.25rem;background:rgba(13,20,35,0.85);-webkit-backdrop-filter:blur(16px);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.18);border-radius:12px;padding:0.75rem 1rem;box-shadow:0 8px 28px rgba(0,0,0,0.35);display:flex;align-items:center;gap:0.6rem">
                        <div style="width:36px;height:36px;border-radius:9px;background:rgba(20,184,166,0.16);display:flex;align-items:center;justify-content:center">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2DD4BF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                        </div>
                        <div>
                            <div style="font-size:0.75rem;font-weight:800;color:#F8FAFC;line-height:1.2">500+ Happy Patients</div>
                            <div style="font-size:0.67rem;color:#94A3B8;margin-top:1px">Trusted since 2015</div>
                        </div>
                    </div>
                </div>
                {{-- Decorative circle --}}
                <div style="position:absolute;top:-2rem;right:-2rem;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle, rgba(20,184,166,0.12) 0%, transparent 70%);z-index:-1;pointer-events:none"></div>
            </div>
        </div>
    </div>
</section>

{{-- ── 2. BRAND MARQUEE ───────────────────────────── --}}
<div style="background:rgba(255,255,255,0.04);border-top:1px solid rgba(255,255,255,0.12);border-bottom:1px solid rgba(255,255,255,0.12);padding:1.25rem 0;overflow:hidden">
    <div class="trin-marquee-track" id="brandMarquee">
        @foreach(array_merge($STATIC_BRANDS, $STATIC_BRANDS) as $name)
            <span style="padding:0 2.5rem;font-size:0.8rem;font-weight:700;color:rgba(255,255,255,0.45);white-space:nowrap;letter-spacing:0.08em;text-transform:uppercase;border-right:1px solid rgba(255,255,255,0.12)">{{ $name }}</span>
        @endforeach
    </div>
</div>

{{-- ── 3. ABOUT ────────────────────────────────────── --}}
<section style="padding:6rem 0">
    <div class="pp-container">
        <div class="about-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center">
            {{-- Left — image mosaic --}}
            <div class="trin-mosaic reveal" style="--delay:0s">
                <div class="trin-mosaic-main">
                    <img
                        src="https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=600&h=280&fit=crop&auto=format&q=80"
                        alt="Eye examination in progress"
                        style="width:100%;height:100%;object-fit:cover;display:block"
                        loading="lazy">
                </div>
                <div class="trin-mosaic-secondary">
                    <img
                        src="https://images.unsplash.com/photo-1651008376811-b90baee60c1f?w=300&h=220&fit=crop&auto=format&q=80"
                        alt="Doctor examining patient"
                        style="width:100%;height:100%;object-fit:cover;display:block"
                        loading="lazy">
                </div>
                <div class="trin-mosaic-badge">
                    <strong>10+</strong>
                    <span>Years of Trusted Eye Care in Nashik</span>
                </div>
            </div>

            {{-- Right — copy --}}
            <div>
                <div class="reveal" style="--delay:0.1s;margin-bottom:1.25rem">
                    <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.3rem 0.9rem;background:rgba(20,184,166,0.12);color:#5EEAD4;border:1px solid rgba(20,184,166,0.32);border-radius:999px;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em">About Us</span>
                </div>
                <h2 class="reveal" style="--delay:0.18s;font-family:var(--pp-font-heading);font-size:clamp(1.75rem, 3vw, 2.4rem);font-weight:800;color:#F8FAFC;letter-spacing:-0.03em;line-height:1.15;margin-bottom:1.25rem;text-wrap:balance">
                    Nashik's Premier Optical Care Destination
                </h2>
                <p class="reveal" style="--delay:0.24s;font-size:0.95rem;color:#9FB1C7;line-height:1.8;margin-bottom:1.75rem">
                    Trinetraa Optician combines modern diagnostic technology with compassionate service. Our certified optometrists use advanced equipment to deliver precise vision correction tailored to each patient at Narayan Bapu Chowk, Nashik Road.
                </p>
                <div class="reveal" style="--delay:0.3s;margin-bottom:2rem">
                    @foreach($ABOUT_BULLETS as $b)
                        <div class="trin-check-item">
                            <div class="trin-check-icon">✓</div>
                            <span style="font-size:0.92rem;color:#B8C6D8;line-height:1.6">{{ $b }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="reveal" style="--delay:0.38s;display:flex;gap:0.75rem;flex-wrap:wrap">
                    <a href="/about" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.75rem 1.5rem;background:#14B8A6;color:#fff;border-radius:999px;font-family:var(--pp-font-heading);font-size:0.875rem;font-weight:600;text-decoration:none;transition:background 0.2s"
                        onmouseenter="this.style.background='#0D9488'"
                        onmouseleave="this.style.background='#2DD4BF'">
                        Our Story
                    </a>
                    <a href="/appointment" style="display:inline-flex;align-items:center;padding:0.75rem 1.5rem;background:transparent;color:#2DD4BF;border:1.5px solid rgba(45,212,191,0.4);border-radius:999px;font-family:var(--pp-font-heading);font-size:0.875rem;font-weight:600;text-decoration:none;transition:border-color 0.2s, background 0.2s"
                        onmouseenter="this.style.background='rgba(20,184,166,0.12)';this.style.borderColor='rgba(45,212,191,0.6)'"
                        onmouseleave="this.style.background='transparent';this.style.borderColor='rgba(45,212,191,0.4)'">
                        Book Appointment
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── 4. STATS BAR ────────────────────────────────── --}}
<div class="trin-stats-bar">
    <div class="trin-stats-grid">
        @foreach($STATS as $i => $s)
            <div class="trin-stat-item reveal" style="--delay:{{ $i * 0.1 }}s">
                <div class="trin-stat-number">{{ $s['num'] }}</div>
                <div class="trin-stat-label">{{ $s['label'] }}</div>
            </div>
        @endforeach
    </div>
</div>

{{-- ── 5. SERVICES ─────────────────────────────────── --}}
<section style="padding:6rem 0">
    <div class="pp-container">
        <div style="text-align:center;margin-bottom:3.5rem">
            <div class="reveal" style="--delay:0s;margin-bottom:0.75rem">
                <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.3rem 0.9rem;background:rgba(20,184,166,0.12);color:#5EEAD4;border:1px solid rgba(20,184,166,0.32);border-radius:999px;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em">Our Services</span>
            </div>
            <h2 class="reveal" style="--delay:0.1s;font-family:var(--pp-font-heading);font-size:clamp(1.75rem, 3vw, 2.4rem);font-weight:800;color:#F8FAFC;letter-spacing:-0.03em;margin:0 auto 0.75rem;max-width:540px;text-wrap:balance">
                Complete Vision Care Under One Roof
            </h2>
            <p class="reveal" style="--delay:0.18s;font-size:0.95rem;color:#9FB1C7;max-width:480px;margin:0 auto;line-height:1.75">
                From routine eye exams to specialized care — everything your family's vision needs.
            </p>
        </div>

        <div class="services-grid" style="display:grid;grid-template-columns:repeat(3, 1fr);gap:1.25rem">
            @foreach($SERVICES as $i => $svc)
                <div class="trin-service-card reveal" style="--delay:{{ 0.05 + $i * 0.08 }}s">
                    <div class="trin-service-icon">{!! $svc['icon'] !!}</div>
                    <div style="font-family:var(--pp-font-heading);font-size:1rem;font-weight:700;color:#F8FAFC;margin-bottom:0.5rem">
                        {{ $svc['title'] }}
                    </div>
                    <p style="font-size:0.85rem;color:#8FA3BB;line-height:1.7;margin:0 0 1.25rem">
                        {{ $svc['desc'] }}
                    </p>
                    <a href="/services" style="font-size:0.8rem;font-weight:600;color:#2DD4BF;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem"
                        onmouseenter="this.style.gap='0.5rem'"
                        onmouseleave="this.style.gap='0.3rem'">
                        Learn More
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── 6. PROCESS ──────────────────────────────────── --}}
<section style="padding:6rem 0">
    <div class="pp-container">
        <div style="text-align:center;margin-bottom:3.5rem">
            <div class="reveal" style="--delay:0s;margin-bottom:0.75rem">
                <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.3rem 0.9rem;background:rgba(20,184,166,0.12);color:#5EEAD4;border:1px solid rgba(20,184,166,0.32);border-radius:999px;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em">How It Works</span>
            </div>
            <h2 class="reveal" style="--delay:0.1s;font-family:var(--pp-font-heading);font-size:clamp(1.75rem, 3vw, 2.4rem);font-weight:800;color:#F8FAFC;letter-spacing:-0.03em;margin:0;text-wrap:balance">
                From Booking to Better Vision
            </h2>
        </div>

        <div class="process-grid" style="display:grid;grid-template-columns:repeat(4, 1fr);gap:1.5rem;position:relative">
            {{-- Connector line --}}
            <div class="process-connector" style="position:absolute;top:2.5rem;left:12%;right:12%;height:1px;background:linear-gradient(to right, rgba(45,212,191,0.15), rgba(45,212,191,0.5), rgba(45,212,191,0.15));z-index:0"></div>

            @foreach($PROCESS_STEPS as $i => $step)
                <div class="reveal" style="--delay:{{ $i * 0.12 }}s;text-align:center;padding:1.5rem 1.25rem;position:relative;z-index:1">
                    <div style="width:52px;height:52px;border-radius:50%;background:rgba(20,184,166,0.14);border:2px solid rgba(45,212,191,0.4);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-family:var(--pp-font-heading);font-weight:800;font-size:0.95rem;color:#2DD4BF">
                        {{ $step['n'] }}
                    </div>
                    <div style="font-family:var(--pp-font-heading);font-size:0.97rem;font-weight:700;color:#F8FAFC;margin-bottom:0.5rem">
                        {{ $step['title'] }}
                    </div>
                    <p style="font-size:0.82rem;color:#8FA3BB;line-height:1.7;margin:0">
                        {{ $step['desc'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── 7. EYEWEAR CATALOG TEASER ───────────────────── --}}
<section style="padding:6rem 0">
    <div class="pp-container">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2.5rem;flex-wrap:wrap;gap:1rem">
            <div>
                <div style="margin-bottom:0.6rem">
                    <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.3rem 0.9rem;background:rgba(20,184,166,0.12);color:#5EEAD4;border:1px solid rgba(20,184,166,0.32);border-radius:999px;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em">New Arrivals</span>
                </div>
                <h2 style="font-family:var(--pp-font-heading);font-size:clamp(1.5rem, 2.5vw, 2rem);font-weight:800;color:#F8FAFC;letter-spacing:-0.03em;margin:0">
                    Fresh Frames, Just In
                </h2>
            </div>
            <div style="display:flex;align-items:center;gap:0.75rem">
                <a href="/eyewears" style="font-size:0.82rem;font-weight:600;color:#2DD4BF;text-decoration:none">
                    See All Eyewear →
                </a>
                <div style="display:flex;gap:0.5rem">
                    <button class="pp-arrow-btn" id="newScrollLeft" aria-label="Scroll left">←</button>
                    <button class="pp-arrow-btn" id="newScrollRight" aria-label="Scroll right">→</button>
                </div>
            </div>
        </div>

        {{-- Featured products container (filled by JS) --}}
        <div id="featuredWrap">
            <div class="pp-loading"><div class="pp-spinner"></div></div>
        </div>
    </div>
</section>

{{-- ── Categories (if available) — filled by JS ───────────────────── --}}
<section id="categoriesSection" style="padding:5rem 0;display:none">
    <div class="pp-container">
        <div style="text-align:center;margin-bottom:2.5rem">
            <div class="reveal" style="--delay:0s;margin-bottom:0.6rem">
                <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.3rem 0.9rem;background:rgba(20,184,166,0.12);color:#5EEAD4;border:1px solid rgba(20,184,166,0.32);border-radius:999px;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em">Browse by Style</span>
            </div>
            <h2 class="reveal" style="--delay:0.1s;font-family:var(--pp-font-heading);font-size:clamp(1.5rem, 2.5vw, 2rem);font-weight:800;color:#F8FAFC;letter-spacing:-0.03em;margin:0">
                Find Your Style
            </h2>
        </div>
        <div id="categoriesGrid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:1rem"></div>
    </div>
</section>

{{-- ── 8. REVIEWS — filled by JS ──────────────────────────────────── --}}
<section id="reviewsSection" style="padding:6rem 0;display:none">
    <div class="pp-container">
        <div style="text-align:center;margin-bottom:3.5rem">
            <div class="reveal" style="--delay:0s;margin-bottom:0.75rem">
                <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.3rem 0.9rem;background:rgba(20,184,166,0.12);color:#5EEAD4;border:1px solid rgba(20,184,166,0.32);border-radius:999px;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em">Customer Reviews</span>
            </div>
            <h2 class="reveal" style="--delay:0.1s;font-family:var(--pp-font-heading);font-size:clamp(1.75rem, 3vw, 2.4rem);font-weight:800;color:#F8FAFC;letter-spacing:-0.03em;margin:0;text-wrap:balance">
                Nashik Trusts Trinetraa
            </h2>
        </div>

        <div id="reviewsGrid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));gap:1.5rem"></div>

        <div style="text-align:center;margin-top:2.5rem">
            <a href="/reviews" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.75rem 1.75rem;background:transparent;border:1.5px solid rgba(45,212,191,0.4);color:#2DD4BF;border-radius:999px;font-family:var(--pp-font-heading);font-size:0.875rem;font-weight:600;text-decoration:none;transition:background 0.2s, border-color 0.2s"
                onmouseenter="this.style.background='rgba(20,184,166,0.12)';this.style.borderColor='rgba(45,212,191,0.6)'"
                onmouseleave="this.style.background='transparent';this.style.borderColor='rgba(45,212,191,0.4)'">
                Read All Reviews
            </a>
        </div>
    </div>
</section>

{{-- ── 9. FINAL CTA ────────────────────────────────── --}}
<section class="trin-cta-section">
    <div class="pp-container">
        <div class="reveal" style="--delay:0s">
            <div style="margin-bottom:1rem;display:flex;justify-content:center">
                <span style="display:inline-flex;padding:0.3rem 0.9rem;background:rgba(20,184,166,0.25);color:#93C5FD;border:1px solid rgba(147,197,253,0.3);border-radius:999px;font-size:0.72rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase">
                    Book Today
                </span>
            </div>
            <h2 style="font-family:var(--pp-font-heading);font-size:clamp(1.75rem, 3.5vw, 2.75rem);font-weight:800;color:#fff;letter-spacing:-0.03em;margin-bottom:1rem;text-wrap:balance">
                Ready for Clearer Vision?
            </h2>
            <p style="font-size:1rem;color:rgba(255,255,255,0.6);max-width:480px;margin:0 auto 2.5rem;line-height:1.75">
                Book an eye examination with our certified optometrists at Narayan Bapu Chowk, Nashik Road. Walk out seeing the world differently.
            </p>
            <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap">
                <a href="/appointment" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.9rem 2rem;background:#14B8A6;color:#fff;border-radius:999px;font-family:var(--pp-font-heading);font-size:0.95rem;font-weight:600;text-decoration:none;box-shadow:0 4px 20px rgba(20,184,166,0.5);transition:background 0.2s, transform 0.2s"
                    onmouseenter="this.style.background='#2DD4BF';this.style.transform='translateY(-2px)'"
                    onmouseleave="this.style.background='#2DD4BF';this.style.transform='none'">
                    Book Free Eye Test
                </a>
                <a href="/eyewears" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.9rem 2rem;background:transparent;color:rgba(255,255,255,0.85);border:1.5px solid rgba(255,255,255,0.2);border-radius:999px;font-family:var(--pp-font-heading);font-size:0.95rem;font-weight:600;text-decoration:none;transition:border-color 0.2s, color 0.2s"
                    onmouseenter="this.style.borderColor='rgba(255,255,255,0.5)';this.style.color='#fff'"
                    onmouseleave="this.style.borderColor='rgba(255,255,255,0.2)';this.style.color='rgba(255,255,255,0.85)'">
                    Browse Eyewear
                </a>
            </div>
            {{-- Address --}}
            <div style="margin-top:2rem;display:inline-flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:rgba(255,255,255,0.4)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                Narayan Bapu Chowk, Nashik Road, Nashik
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
@verbatim
(function () {
  "use strict";

  var newRef = null; // products scroll container, set after render

  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  /* ── Scroll reveal ── */
  function initReveal() {
    var els = document.querySelectorAll(".reveal:not(.revealed)");
    if (!els.length) return;
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add("revealed"); io.unobserve(e.target); }
      });
    }, { threshold: 0.12 });
    els.forEach(function (el) { io.observe(el); });
  }

  /* ── Stat counter count-up (10+, 500+, 20+, 8+ ...) ── */
  function initCounters() {
    var counters = document.querySelectorAll(".trin-stat-number:not(.counted)");
    if (!counters.length) return;
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        var el = e.target;
        io.unobserve(el);
        el.classList.add("counted");
        var match = el.textContent.trim().match(/^([\d,]+)(.*)$/);
        if (!match) return;
        var target = parseInt(match[1].replace(/,/g, ""), 10);
        var suffix = match[2] || "";
        var duration = 1200;
        var startTime = null;
        function step(ts) {
          if (!startTime) startTime = ts;
          var progress = Math.min((ts - startTime) / duration, 1);
          var eased = 1 - Math.pow(1 - progress, 3);
          el.textContent = Math.floor(eased * target) + suffix;
          if (progress < 1) requestAnimationFrame(step);
          else el.textContent = target + suffix;
        }
        requestAnimationFrame(step);
      });
    }, { threshold: 0.4 });
    counters.forEach(function (el) { io.observe(el); });
  }

  /* ── Cart helpers ── */
  function cartItemFor(id) {
    return (window.TrinetraaCart ? window.TrinetraaCart.items : []).find(function (i) { return i.id === id; });
  }

  function productCardHTML(item) {
    var salePrice = item.discount_percentage > 0
      ? Number(item.price) * (1 - item.discount_percentage / 100)
      : null;
    var cartItem = cartItemFor(item.id);
    var priceShown = salePrice !== null ? salePrice.toFixed(0) : Number(item.price).toFixed(0);

    var imageHTML = item.image
      ? '<img src="' + esc(item.image) + '" alt="' + esc(item.name) + '" loading="lazy">'
      : '<div class="pp-product-card__placeholder">🕶️</div>';
    var badgeHTML = item.discount_percentage > 0
      ? '<span class="pp-badge-sale">' + item.discount_percentage + '% OFF</span>'
      : '';
    var brandHTML = item.brand
      ? '<div class="pp-product-card__brand">' + esc(item.brand) + '</div>'
      : '';
    var originalHTML = salePrice !== null
      ? '<span class="pp-product-card__original">₹' + Number(item.price).toFixed(0) + '</span>'
      : '';

    var footerActionHTML;
    if (cartItem) {
      footerActionHTML =
        '<div class="pp-qty-control">' +
          '<button class="pp-qty-control__btn" data-qty-dec="' + item.id + '">−</button>' +
          '<span class="pp-qty-control__count" data-qty-count="' + item.id + '">' + cartItem.quantity + '</span>' +
          '<button class="pp-qty-control__btn" data-qty-inc="' + item.id + '">+</button>' +
        '</div>';
    } else {
      footerActionHTML = '<button class="pp-btn-primary pp-btn-sm" data-add-cart="' + item.id + '">+ Cart</button>';
    }

    return (
      '<div class="pp-product-card" data-card="' + item.id + '">' +
        '<div class="pp-product-card__image-wrap">' + imageHTML + badgeHTML + '</div>' +
        '<div class="pp-product-card__body">' +
          '<div class="pp-product-card__category">' + esc(item.category ? item.category.name : "") + '</div>' +
          '<div class="pp-product-card__name">' + esc(item.name) + '</div>' +
          brandHTML +
          '<div class="pp-product-card__pricing">' +
            '<span class="pp-product-card__price">₹' + priceShown + '</span>' +
            originalHTML +
          '</div>' +
        '</div>' +
        '<div class="pp-product-card__footer" style="display:flex;gap:0.5rem">' +
          '<a href="/eyewears/' + esc(item.slug) + '" class="pp-btn-navy pp-btn-sm" style="flex:1;justify-content:center">View</a>' +
          footerActionHTML +
        '</div>' +
      '</div>'
    );
  }

  var FEATURED = [];

  function renderFeatured() {
    var wrap = document.getElementById("featuredWrap");
    if (!wrap) return;
    if (!FEATURED.length) {
      wrap.innerHTML =
        '<div class="pp-empty">' +
          '<div class="pp-empty__icon">🕶️</div>' +
          '<div class="pp-empty__title">Products coming soon</div>' +
        '</div>';
      newRef = null;
      return;
    }
    var html = '<div class="pp-products-scroll reveal" style="--delay:0s" id="newScroll">' +
      FEATURED.map(productCardHTML).join("") + '</div>';
    wrap.innerHTML = html;
    newRef = document.getElementById("newScroll");
    bindCardActions(wrap);
    initReveal();
  }

  function bindCardActions(root) {
    root.querySelectorAll("[data-add-cart]").forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        var id = Number(btn.getAttribute("data-add-cart"));
        var item = FEATURED.find(function (x) { return x.id === id; });
        if (!item || !window.TrinetraaCart) return;
        var salePrice = item.discount_percentage > 0
          ? Number(item.price) * (1 - item.discount_percentage / 100)
          : null;
        window.TrinetraaCart.add({
          id: item.id,
          name: item.name,
          slug: item.slug,
          price: Number(item.price),
          salePrice: salePrice != null ? salePrice : Number(item.price),
          sale_price: salePrice != null ? salePrice : Number(item.price),
          image: item.image != null ? item.image : null,
          brand: item.brand != null ? item.brand : "",
        });
      });
    });
    root.querySelectorAll("[data-qty-inc]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = Number(btn.getAttribute("data-qty-inc"));
        var ci = cartItemFor(id);
        if (ci) window.TrinetraaCart.updateQty(id, ci.quantity + 1);
      });
    });
    root.querySelectorAll("[data-qty-dec]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = Number(btn.getAttribute("data-qty-dec"));
        var ci = cartItemFor(id);
        if (!ci) return;
        if (ci.quantity === 1) window.TrinetraaCart.remove(id);
        else window.TrinetraaCart.updateQty(id, ci.quantity - 1);
      });
    });
  }

  /* Re-render product cards when cart changes (qty controls reflect state) */
  document.addEventListener("cart:change", function () {
    if (FEATURED.length) renderFeatured();
  });

  /* ── Brand marquee (replace with API brands if available) ── */
  function renderBrands(apiBrands) {
    if (!apiBrands || !apiBrands.length) return;
    var names = apiBrands.map(function (b) { return b.name; });
    var track = document.getElementById("brandMarquee");
    if (!track) return;
    var all = names.concat(names);
    track.innerHTML = all.map(function (name) {
      return '<span style="padding:0 2.5rem;font-size:0.8rem;font-weight:700;color:rgba(255,255,255,0.45);white-space:nowrap;letter-spacing:0.08em;text-transform:uppercase;border-right:1px solid rgba(255,255,255,0.12)">' + esc(name) + '</span>';
    }).join("");
  }

  /* ── Reviews ── */
  function renderReviews(reviews) {
    if (!reviews || !reviews.length) return;
    var section = document.getElementById("reviewsSection");
    var grid = document.getElementById("reviewsGrid");
    if (!section || !grid) return;
    grid.innerHTML = reviews.map(function (r, i) {
      var stars = "";
      for (var s = 1; s <= 5; s++) stars += "<span>" + (s <= r.rating ? "★" : "☆") + "</span>";
      var dateStr = "";
      try {
        dateStr = new Date(r.created_at).toLocaleDateString("en-IN", { month: "long", year: "numeric" });
      } catch (e) { dateStr = ""; }
      var initial = (r.user_name || "?").charAt(0).toUpperCase();
      return (
        '<div class="trin-review-card reveal" style="--delay:' + (i * 0.1) + 's">' +
          '<div class="trin-review-stars">' + stars + '</div>' +
          '<p class="trin-review-text">"' + esc(r.review_text) + '"</p>' +
          '<div class="trin-review-author">' +
            '<div class="trin-review-avatar">' + esc(initial) + '</div>' +
            '<div>' +
              '<div style="font-weight:700;font-size:0.875rem;color:#F8FAFC">' + esc(r.user_name) + '</div>' +
              '<div style="font-size:0.75rem;color:#94A3B8">' + esc(dateStr) + '</div>' +
            '</div>' +
          '</div>' +
        '</div>'
      );
    }).join("");
    section.style.display = "";
    initReveal();
  }

  /* ── Categories ── */
  function renderCategories(categories) {
    var withCount = (categories || []).filter(function (c) { return c.eyewears_count > 0; });
    if (!withCount.length) return;
    var section = document.getElementById("categoriesSection");
    var grid = document.getElementById("categoriesGrid");
    if (!section || !grid) return;
    grid.innerHTML = withCount.map(function (cat, i) {
      var inner = cat.image
        ? '<img src="' + esc(cat.image) + '" alt="' + esc(cat.name) + '" loading="lazy">'
        : '<div style="width:100%;height:100%;background:rgba(20,184,166,0.16);display:flex;align-items:center;justify-content:center;font-size:3rem">🕶️</div>';
      return (
        '<a href="/eyewears?category_slug=' + esc(cat.slug) + '" class="pp-category-card reveal" style="--delay:' + (i * 0.07) + 's">' +
          inner +
          '<div class="pp-category-card__overlay">' +
            '<div class="pp-category-card__name">' + esc(cat.name) + '</div>' +
            '<div class="pp-category-card__count">' + cat.eyewears_count + ' styles</div>' +
          '</div>' +
        '</a>'
      );
    }).join("");
    section.style.display = "";
    initReveal();
  }

  /* ── Arrow scroll buttons ── */
  function scrollH(dir) {
    if (newRef) newRef.scrollBy({ left: dir * 270, behavior: "smooth" });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initReveal();
    initCounters();

    var leftBtn = document.getElementById("newScrollLeft");
    var rightBtn = document.getElementById("newScrollRight");
    if (leftBtn) leftBtn.addEventListener("click", function () { scrollH(-1); });
    if (rightBtn) rightBtn.addEventListener("click", function () { scrollH(1); });

    Promise.all([
      fetch("/api/public/home").then(function (r) { return r.json(); }),
      fetch("/api/public/categories").then(function (r) { return r.json(); }),
    ])
      .then(function (results) {
        var h = results[0] || {};
        var c = results[1] || {};
        var data = h.data || {};
        FEATURED = data.featured_eyewears || [];
        renderFeatured();
        renderReviews(data.approved_reviews || []);
        renderBrands(data.brands || []);
        renderCategories(c.data || []);
      })
      .catch(function () {
        renderFeatured(); // shows empty state
      });
  });
})();
@endverbatim
</script>
@endpush
