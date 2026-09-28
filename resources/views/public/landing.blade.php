<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0B1220">
    <title>Trinetraa Opticians — Premium Eyewear & Vision Care, Nashik</title>
    <meta name="description" content="Discover world-class eyewear, precision eye testing and premium vision care at Trinetraa Opticians, Nashik. Designer frames, sunglasses, contact lenses and certified optometrists.">
    <link rel="canonical" href="https://trinetraaoptician.com/landing">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Trinetraa Opticians — Premium Eyewear & Vision Care">
    <meta property="og:description" content="See Better. Look Better. World-class eyewear, precision eye testing and premium vision care in Nashik.">
    <meta property="og:url" content="https://trinetraaoptician.com/landing">
    <meta property="og:image" content="https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=1200&h=630&fit=crop&auto=format&q=80">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" type="image/svg+xml" href="/icons/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Optician",
      "name": "Trinetraa Opticians",
      "url": "https://trinetraaoptician.com",
      "logo": "https://trinetraaoptician.com/icons/icon-512.png",
      "description": "Premium eyewear, precision eye testing and vision care in Nashik, Maharashtra.",
      "telephone": "{{ $site['contactPhone'] ?? '+91 88888 99737' }}",
      "address": { "@@type": "PostalAddress", "streetAddress": "Narayan Bapu Chowk, Nashik Road", "addressLocality": "Nashik", "addressRegion": "Maharashtra", "postalCode": "422101", "addressCountry": "IN" },
      "priceRange": "₹₹"
    }
    </script>
    <style>
        :root {
            --emerald: #0F766E;
            --emerald-bright: #14B8A6;
            --ink: #0B1220;
            --glass: rgba(255, 255, 255, 0.10);
            --glass-strong: rgba(255, 255, 255, 0.14);
            --glass-border: rgba(255, 255, 255, 0.20);
            --white-70: rgba(255, 255, 255, 0.70);
            --white-50: rgba(255, 255, 255, 0.50);
            --serif: 'Playfair Display', Georgia, serif;
            --sans: 'Inter', -apple-system, sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: var(--sans);
            color: #fff;
            background: var(--ink);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        button { font-family: inherit; cursor: pointer; }
        :focus-visible { outline: 2px solid var(--emerald-bright); outline-offset: 3px; border-radius: 4px; }

        /* ── Stage ─────────────────────────────────────────────── */
        .stage {
            position: relative;
            min-height: 100svh;
            overflow: hidden;
            perspective: 1200px;
            display: flex;
            flex-direction: column;
        }

        .stage__video-wrap {
            position: absolute;
            inset: -4%;
            z-index: 0;
            will-change: transform;
        }
        .stage__video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: saturate(1.05);
        }
        /* Legibility vignette — kept featherlight so the video stays the hero */
        .stage__vignette {
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background:
                linear-gradient(to bottom, rgba(11,18,32,0.38) 0%, rgba(11,18,32,0.05) 22%, rgba(11,18,32,0.02) 55%, rgba(11,18,32,0.45) 100%),
                radial-gradient(120% 90% at 20% 45%, rgba(11,18,32,0.30) 0%, transparent 55%);
        }

        /* ── Glass primitives ──────────────────────────────────── */
        .glass {
            background: var(--glass);
            -webkit-backdrop-filter: blur(22px) saturate(1.4);
            backdrop-filter: blur(22px) saturate(1.4);
            border: 1px solid var(--glass-border);
        }

        /* ── Navigation ────────────────────────────────────────── */
        .nav {
            position: relative;
            z-index: 20;
            margin: clamp(0.75rem, 2vw, 1.25rem) auto 0;
            width: min(1320px, calc(100% - 2rem));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.65rem 1rem 0.65rem 1.1rem;
            animation: navIn 0.9s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .nav__brand { display: flex; align-items: center; gap: 0.6rem; flex-shrink: 0; }
        .nav__brand img { width: 38px; height: 38px; filter: drop-shadow(0 2px 8px rgba(0,0,0,0.35)); }
        .nav__brand-name {
            font-family: var(--serif);
            font-weight: 600;
            font-size: 1.18rem;
            letter-spacing: 0.01em;
            text-shadow: 0 1px 12px rgba(0,0,0,0.4);
            white-space: nowrap;
        }
        .nav__links { display: flex; align-items: center; gap: 0.1rem; list-style: none; }
        .nav__links a {
            position: relative;
            padding: 0.5rem 0.72rem;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--white-70);
            letter-spacing: 0.015em;
            transition: color 0.25s;
            white-space: nowrap;
        }
        .nav__links a::after {
            content: '';
            position: absolute;
            left: 0.72rem; right: 0.72rem; bottom: 0.2rem;
            height: 1.5px;
            background: var(--emerald-bright);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .nav__links a:hover { color: #fff; }
        .nav__links a:hover::after { transform: scaleX(1); }
        .nav__cta {
            background: #fff;
            color: var(--ink);
            border: none;
            border-radius: 999px;
            padding: 0.62rem 1.25rem;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.25s;
            box-shadow: 0 6px 24px rgba(0,0,0,0.25);
            white-space: nowrap;
        }
        .nav__cta:hover { transform: scale(1.05); box-shadow: 0 10px 32px rgba(0,0,0,0.35); }
        .nav__burger {
            display: none;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            width: 42px; height: 42px;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .nav__mobile {
            display: none;
            position: absolute;
            top: calc(100% + 0.5rem);
            left: 0; right: 0;
            border-radius: 18px;
            padding: 0.75rem;
            flex-direction: column;
            box-shadow: 0 24px 60px rgba(0,0,0,0.35);
        }
        .nav__mobile.open { display: flex; }
        .nav__mobile a {
            padding: 0.8rem 1rem;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            color: rgba(255,255,255,0.85);
            transition: background 0.2s;
        }
        .nav__mobile a:hover { background: rgba(255,255,255,0.08); }

        /* ── Hero grid ─────────────────────────────────────────── */
        .hero {
            position: relative;
            z-index: 10;
            flex: 1;
            width: min(1320px, calc(100% - 2rem));
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(320px, 0.85fr);
            align-items: center;
            gap: clamp(2rem, 5vw, 5rem);
            padding: clamp(1.5rem, 4vh, 3rem) 0;
        }

        .hero__badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 999px;
            padding: 0.4rem 1rem 0.4rem 0.55rem;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.85);
            margin-bottom: clamp(1rem, 2.5vh, 1.75rem);
        }
        .hero__badge-dot {
            width: 22px; height: 22px;
            border-radius: 50%;
            background: var(--emerald);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        .hero__title {
            font-family: var(--serif);
            font-weight: 600;
            font-size: clamp(2.6rem, 7vw, 5.4rem);
            line-height: 1.04;
            letter-spacing: -0.015em;
            text-shadow: 0 2px 30px rgba(0,0,0,0.45);
            margin-bottom: clamp(1rem, 2.5vh, 1.5rem);
        }
        .hero__title .line { display: block; overflow: hidden; }
        .hero__title .line > span {
            display: inline-block;
            transform: translateY(110%);
            animation: lineReveal 1.1s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        .hero__title .line:nth-child(2) > span { animation-delay: 0.14s; }
        .hero__title em {
            font-style: italic;
            font-weight: 500;
            color: transparent;
            background: linear-gradient(115deg, #7DF3E6 0%, #2DD4BF 45%, #E9FFFB 100%);
            -webkit-background-clip: text;
            background-clip: text;
        }

        .hero__sub {
            max-width: 480px;
            font-size: clamp(0.92rem, 1.4vw, 1.02rem);
            line-height: 1.75;
            color: var(--white-70);
            text-shadow: 0 1px 14px rgba(0,0,0,0.4);
            margin-bottom: clamp(1.4rem, 3.5vh, 2.2rem);
            animation: fadeUp 1s 0.35s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .hero__ctas { display: flex; gap: 0.85rem; flex-wrap: wrap; animation: fadeUp 1s 0.5s cubic-bezier(0.22, 1, 0.36, 1) both; }
        .btn-primary {
            background: #fff;
            color: var(--ink);
            border: none;
            border-radius: 999px;
            padding: 0.95rem 2.1rem;
            font-size: 0.92rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            box-shadow: 0 10px 36px rgba(0,0,0,0.35);
            transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.25s;
        }
        .btn-primary:hover { transform: scale(1.05) translateY(-1px); box-shadow: 0 16px 44px rgba(0,0,0,0.45); }
        .btn-primary .arr { transition: transform 0.25s; }
        .btn-primary:hover .arr { transform: translateX(3px); }
        .btn-ghost {
            border-radius: 999px;
            padding: 0.95rem 2.1rem;
            font-size: 0.92rem;
            font-weight: 500;
            color: #fff;
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1), background 0.25s;
        }
        .btn-ghost:hover { transform: scale(1.04); background: var(--glass-strong); }

        .hero__trust {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem 1.4rem;
            margin-top: clamp(1.6rem, 4vh, 2.6rem);
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--white-70);
            text-shadow: 0 1px 10px rgba(0,0,0,0.45);
            animation: fadeUp 1s 0.65s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .hero__trust .stars { color: #FBBF24; letter-spacing: 0.1em; text-shadow: 0 0 14px rgba(251,191,36,0.4); }
        .hero__trust b { color: #fff; font-weight: 600; }
        .hero__trust .sep { width: 3px; height: 3px; border-radius: 50%; background: var(--white-50); }

        /* ── Search / CTA card (3D tilt) ───────────────────────── */
        .card-wrap { position: relative; z-index: 12; transform-style: preserve-3d; animation: cardIn 1.1s 0.45s cubic-bezier(0.22, 1, 0.36, 1) both; }
        .card {
            border-radius: 24px;
            padding: clamp(1.5rem, 2.5vw, 2.1rem);
            background: var(--glass);
            -webkit-backdrop-filter: blur(26px) saturate(1.5);
            backdrop-filter: blur(26px) saturate(1.5);
            border: 1px solid var(--glass-border);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255,255,255,0.25);
            transform-style: preserve-3d;
            transition: box-shadow 0.35s;
            will-change: transform;
        }
        .card:hover { box-shadow: 0 40px 100px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255,255,255,0.3); }
        .card__eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #5EEAD4;
            margin-bottom: 0.5rem;
            transform: translateZ(30px);
        }
        .card__title {
            font-family: var(--serif);
            font-weight: 600;
            font-size: clamp(1.45rem, 2.2vw, 1.8rem);
            line-height: 1.2;
            margin-bottom: 1.4rem;
            transform: translateZ(40px);
        }
        .card__field { margin-bottom: 1rem; transform: translateZ(25px); }
        .card__label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--white-70);
            margin-bottom: 0.45rem;
        }
        .card__select-wrap { position: relative; }
        .card__select {
            width: 100%;
            appearance: none;
            -webkit-appearance: none;
            background: rgba(255,255,255,0.09);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            color: #fff;
            font-family: inherit;
            font-size: 0.92rem;
            font-weight: 500;
            padding: 0.95rem 2.6rem 0.95rem 1.1rem;
            cursor: pointer;
            transition: border-color 0.25s, background 0.25s;
        }
        .card__select:hover { background: rgba(255,255,255,0.13); }
        .card__select:focus { outline: none; border-color: var(--emerald-bright); background: rgba(255,255,255,0.13); }
        .card__select option { color: var(--ink); background: #fff; }
        .card__chev {
            position: absolute;
            right: 1rem; top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--white-70);
        }
        .card__btn {
            width: 100%;
            background: linear-gradient(135deg, var(--emerald) 0%, #0D9488 100%);
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 1rem 1.2rem;
            font-size: 0.94rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            box-shadow: 0 12px 30px rgba(15, 118, 110, 0.45);
            transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.25s, filter 0.25s;
            transform: translateZ(35px);
        }
        .card__btn:hover { transform: translateZ(35px) scale(1.03); filter: brightness(1.1); box-shadow: 0 18px 40px rgba(15, 118, 110, 0.55); }
        .card__note {
            margin-top: 0.9rem;
            font-size: 0.74rem;
            color: var(--white-50);
            text-align: center;
            transform: translateZ(20px);
        }
        .card__shine {
            position: absolute;
            inset: 0;
            border-radius: 24px;
            pointer-events: none;
            background: radial-gradient(400px circle at var(--mx, 50%) var(--my, 50%), rgba(255,255,255,0.14), transparent 55%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .card:hover .card__shine { opacity: 1; }

        /* ── Feature pills ─────────────────────────────────────── */
        .pills {
            position: relative;
            z-index: 10;
            width: min(1320px, calc(100% - 2rem));
            margin: 0 auto;
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 0.6rem;
            padding-bottom: 1rem;
            padding-right: 4.75rem; /* clearance for the fixed WhatsApp FAB */
        }
        .pill {
            border-radius: 999px;
            padding: 0.55rem 1.05rem;
            font-size: 0.76rem;
            font-weight: 500;
            color: rgba(255,255,255,0.85);
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) both;
            transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), background 0.3s, border-color 0.3s;
            will-change: transform;
        }
        .pill:nth-child(1) { animation-delay: 0.75s; }
        .pill:nth-child(2) { animation-delay: 0.85s; }
        .pill:nth-child(3) { animation-delay: 0.95s; }
        .pill:nth-child(4) { animation-delay: 1.05s; }
        .pill:nth-child(5) { animation-delay: 1.15s; }
        .pill:hover {
            transform: translateY(-4px);
            background: var(--glass-strong);
            border-color: rgba(94, 234, 212, 0.5);
        }
        .pill .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--emerald-bright); box-shadow: 0 0 10px var(--emerald-bright); }

        /* ── Brand marquee ─────────────────────────────────────── */
        .marquee {
            position: relative;
            z-index: 10;
            border-top: 1px solid rgba(255,255,255,0.12);
            background: rgba(11, 18, 32, 0.30);
            -webkit-backdrop-filter: blur(18px);
            backdrop-filter: blur(18px);
            overflow: hidden;
            padding: 0.9rem 0;
            -webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
            mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
            animation: fadeIn 1.2s 0.9s both;
        }
        .marquee__track {
            display: flex;
            width: max-content;
            animation: marquee 34s linear infinite;
        }
        .marquee:hover .marquee__track { animation-play-state: paused; }
        .marquee__item {
            font-family: var(--serif);
            font-size: 1.02rem;
            font-weight: 500;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--white-50);
            padding: 0 2.6rem;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 2.6rem;
            transition: color 0.3s;
        }
        .marquee__item:hover { color: rgba(255,255,255,0.9); }
        .marquee__item::after { content: '◆'; font-size: 0.5rem; color: rgba(94, 234, 212, 0.55); }

        /* ── WhatsApp FAB ──────────────────────────────────────── */
        .wa-fab {
            position: fixed;
            right: 1.4rem;
            bottom: 4.6rem;
            z-index: 40;
            width: 56px; height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 14px 40px rgba(0,0,0,0.4);
            transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), background 0.3s;
            animation: fadeUp 0.9s 1.3s both;
        }
        .wa-fab:hover { transform: scale(1.1); background: rgba(37, 211, 102, 0.85); border-color: rgba(37, 211, 102, 0.9); }

        /* ── Keyframes ─────────────────────────────────────────── */
        @keyframes navIn { from { opacity: 0; transform: translateY(-24px); } to { opacity: 1; transform: none; } }
        @keyframes lineReveal { to { transform: translateY(0); } }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(26px); } to { opacity: 1; transform: none; } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes cardIn { from { opacity: 0; transform: translateX(50px) rotateY(-8deg); } to { opacity: 1; transform: none; } }
        @keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        /* ── Responsive ────────────────────────────────────────── */
        @media (max-width: 1080px) {
            .nav__links { display: none; }
            .nav__burger { display: inline-flex; }
        }
        @media (max-width: 900px) {
            .stage { overflow: visible; }
            .hero {
                grid-template-columns: 1fr;
                gap: 2.25rem;
                padding-top: 2rem;
                padding-bottom: 2rem;
            }
            .hero__sub { max-width: 100%; }
            .pills { justify-content: flex-start; padding-right: 0; }
        }
        @media (max-width: 520px) {
            .nav__cta { display: none; }
            .hero__ctas .btn-primary, .hero__ctas .btn-ghost { width: 100%; justify-content: center; }
            .wa-fab { bottom: 4.2rem; right: 1rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; animation-delay: 0s !important; transition-duration: 0.01ms !important; }
            .marquee__track { animation: none; }
            .stage__video { display: none; }
            .stage__video-wrap { background: linear-gradient(135deg, #0B1220 0%, #123B36 100%); }
        }
    </style>
</head>
<body>

<div class="stage" id="stage">
    {{-- Cinematic background video --}}
    <div class="stage__video-wrap" id="videoWrap" aria-hidden="true">
        <video class="stage__video" id="bgVideo" autoplay muted loop playsinline preload="auto"
               poster="https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=1920&h=1080&fit=crop&auto=format&q=70">
        </video>
    </div>
    <div class="stage__vignette" aria-hidden="true"></div>

    {{-- Glass navigation --}}
    <nav class="nav glass" aria-label="Main navigation">
        <a href="/" class="nav__brand" aria-label="Trinetraa Opticians home">
            <img src="/icons/favicon.svg" alt="" width="38" height="38">
            <span class="nav__brand-name">Trinetraa Opticians</span>
        </a>
        <ul class="nav__links">
            <li><a href="/">Home</a></li>
            <li><a href="/eyewears">Eyewear</a></li>
            <li><a href="/eyewears?category_slug=sunglasses">Sunglasses</a></li>
            <li><a href="/eyewears?category_slug=contact-lenses">Contact Lenses</a></li>
            <li><a href="/appointment">Eye Test</a></li>
            <li><a href="/brands">Brands</a></li>
            <li><a href="/about">About</a></li>
            <li><a href="/contact">Contact</a></li>
        </ul>
        <div style="display:flex;align-items:center;gap:0.6rem">
            <a href="/appointment" class="nav__cta">
                Book Eye Test
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <button class="nav__burger" id="burger" aria-label="Open menu" aria-expanded="false">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" x2="20" y1="7" y2="7"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="17" y2="17"/></svg>
            </button>
        </div>
        <div class="nav__mobile glass" id="mobileMenu">
            <a href="/">Home</a>
            <a href="/eyewears">Eyewear</a>
            <a href="/eyewears?category_slug=sunglasses">Sunglasses</a>
            <a href="/eyewears?category_slug=contact-lenses">Contact Lenses</a>
            <a href="/appointment">Eye Test</a>
            <a href="/brands">Brands</a>
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
            <a href="/appointment" style="background:#fff;color:var(--ink);text-align:center;font-weight:600;margin-top:0.35rem">Book Eye Test</a>
        </div>
    </nav>

    {{-- Hero --}}
    <main class="hero">
        <div class="hero__copy">
            <span class="hero__badge glass">
                <span class="hero__badge-dot">👁</span>
                Premium Optical Boutique · Nashik
            </span>
            <h1 class="hero__title">
                <span class="line"><span>See Better.</span></span>
                <span class="line"><span><em>Look Better.</em></span></span>
            </h1>
            <p class="hero__sub">
                Discover world-class eyewear, precision eye testing and premium vision care from trusted experts.
                Designer frames, contact lenses, prescription glasses or sunglasses — we help you find the perfect vision solution.
            </p>
            <div class="hero__ctas">
                <a href="/appointment" class="btn-primary">
                    Book Eye Test
                    <svg class="arr" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <a href="/eyewears" class="btn-ghost glass">Browse Collection</a>
            </div>
            <div class="hero__trust" aria-label="Trust indicators">
                <span><span class="stars" aria-hidden="true">★★★★★</span> <b>Google Rating</b></span>
                <span class="sep" aria-hidden="true"></span>
                <span><b>10,000+</b> Happy Customers</span>
                <span class="sep" aria-hidden="true"></span>
                <span><b>20+</b> Premium Brands</span>
                <span class="sep" aria-hidden="true"></span>
                <span>Free Eye Testing<sup>*</sup></span>
                <span class="sep" aria-hidden="true"></span>
                <span>Certified Optometrists</span>
            </div>
        </div>

        {{-- Search / CTA glass card with 3D tilt --}}
        <div class="card-wrap">
            <div class="card" id="tiltCard">
                <div class="card__shine" id="cardShine" aria-hidden="true"></div>
                <div class="card__eyebrow">Curated For You</div>
                <div class="card__title">Find Your Perfect Frame</div>
                <div class="card__field">
                    <label class="card__label" for="catSelect">Select Category</label>
                    <div class="card__select-wrap">
                        <select class="card__select" id="catSelect">
                            <option value="">All Eyewear</option>
                            <option value="eyeglasses">Eyeglasses</option>
                            <option value="sunglasses">Sunglasses</option>
                            <option value="contact-lenses">Contact Lenses</option>
                            <option value="computer">Computer Glasses</option>
                            <option value="kids-eyewear">Kids Collection</option>
                        </select>
                        <svg class="card__chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>
                <button class="card__btn" id="exploreBtn">
                    Explore Collection
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
                <div class="card__note">1000+ styles in store · Expert fitting included</div>
            </div>
        </div>
    </main>

    {{-- Feature pills --}}
    <div class="pills" aria-label="Highlights">
        <span class="pill glass"><span class="dot"></span>Premium Brands</span>
        <span class="pill glass"><span class="dot"></span>Computerized Eye Testing</span>
        <span class="pill glass"><span class="dot"></span>Prescription Glasses</span>
        <span class="pill glass"><span class="dot"></span>Contact Lenses</span>
        <span class="pill glass"><span class="dot"></span>Blue Light Protection</span>
    </div>

    {{-- Brand marquee --}}
    <div class="marquee" aria-label="Brands we carry">
        <div class="marquee__track" id="marqueeTrack">
            <span class="marquee__item">Ray-Ban</span>
            <span class="marquee__item">Oakley</span>
            <span class="marquee__item">Vogue</span>
            <span class="marquee__item">Titan</span>
            <span class="marquee__item">Essilor</span>
            <span class="marquee__item">Crizal</span>
            <span class="marquee__item">Zeiss</span>
            <span class="marquee__item">Hoya</span>
            <span class="marquee__item">Police</span>
            <span class="marquee__item">Carrera</span>
        </div>
    </div>
</div>

{{-- WhatsApp FAB --}}
<a class="wa-fab glass" href="https://wa.me/{{ $site['whatsappNumber'] ?? '918888899737' }}?text=Hello%20Trinetraa%20Opticians%2C%20I%27d%20like%20to%20book%20an%20eye%20test."
   target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp">
    <svg viewBox="0 0 32 32" fill="currentColor" width="26" height="26" aria-hidden="true"><path d="M16 3C9.37 3 4 8.37 4 15c0 2.39.67 4.62 1.83 6.52L4 29l7.7-1.8A12.93 12.93 0 0016 28c6.63 0 12-5.37 12-12S22.63 3 16 3zm6.27 17.1c-.27.76-1.57 1.46-2.16 1.55-.56.09-1.27.13-2.05-.13a18.9 18.9 0 01-1.86-.7c-3.25-1.4-5.37-4.66-5.53-4.87-.16-.22-1.28-1.7-1.28-3.24s.81-2.3 1.1-2.61c.29-.32.63-.4.84-.4l.61.01c.19 0 .46-.07.72.55l.98 2.39c.09.23.05.5-.09.71l-.39.56-.38.43c-.13.15-.27.3-.12.59.15.28.68 1.13 1.46 1.83.99.89 1.83 1.16 2.1 1.29.27.13.43.11.59-.07l.84-.99c.15-.2.31-.15.52-.08l2.5.98c.24.09.39.14.45.22.06.09.06.51-.2 1.27z"/></svg>
</a>

<script>
(function () {
    "use strict";
    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    // ── Background video: pick resolution by viewport, defer to not block paint ──
    var video = document.getElementById("bgVideo");
    if (video && !reduced) {
        var src = window.innerWidth <= 720
            ? "https://videos.pexels.com/video-files/5241104/5241104-sd_640_360_25fps.mp4"
            : "https://videos.pexels.com/video-files/5241104/5241104-hd_1920_1080_25fps.mp4";
        var source = document.createElement("source");
        source.src = src;
        source.type = "video/mp4";
        video.appendChild(source);
        video.load();
        var p = video.play();
        if (p && p.catch) p.catch(function () {}); // autoplay may be blocked; poster stays
    }

    // ── Mobile menu ──
    var burger = document.getElementById("burger");
    var mobileMenu = document.getElementById("mobileMenu");
    if (burger && mobileMenu) {
        burger.addEventListener("click", function (e) {
            e.stopPropagation();
            var open = mobileMenu.classList.toggle("open");
            burger.setAttribute("aria-expanded", open ? "true" : "false");
        });
        document.addEventListener("click", function (e) {
            if (!mobileMenu.contains(e.target) && !burger.contains(e.target)) {
                mobileMenu.classList.remove("open");
                burger.setAttribute("aria-expanded", "false");
            }
        });
    }

    // ── Explore button → catalog with selected category ──
    var exploreBtn = document.getElementById("exploreBtn");
    var catSelect = document.getElementById("catSelect");
    if (exploreBtn && catSelect) {
        exploreBtn.addEventListener("click", function () {
            var v = catSelect.value;
            // "computer" has no dedicated category; land on the full catalog.
            var url = (v && v !== "computer") ? "/eyewears?category_slug=" + encodeURIComponent(v) : "/eyewears";
            window.location.href = url;
        });
    }

    if (reduced) return; // everything below is motion

    // ── Mouse parallax (video drifts, pills float at different depths) ──
    var videoWrap = document.getElementById("videoWrap");
    var pills = Array.prototype.slice.call(document.querySelectorAll(".pill"));
    var targetX = 0, targetY = 0, curX = 0, curY = 0;
    var fine = window.matchMedia("(pointer: fine)").matches;

    if (fine) {
        document.addEventListener("mousemove", function (e) {
            targetX = (e.clientX / window.innerWidth) - 0.5;
            targetY = (e.clientY / window.innerHeight) - 0.5;
        }, { passive: true });

        (function loop() {
            curX += (targetX - curX) * 0.045;
            curY += (targetY - curY) * 0.045;
            if (videoWrap) {
                videoWrap.style.transform = "translate3d(" + (curX * -22) + "px," + (curY * -14) + "px,0) scale(1.05)";
            }
            for (var i = 0; i < pills.length; i++) {
                var depth = 4 + i * 2.5;
                pills[i].style.transform = "translate3d(" + (curX * -depth) + "px," + (curY * -depth * 0.6) + "px,0)";
            }
            requestAnimationFrame(loop);
        })();
    }

    // ── 3D tilt on the glass card ──
    var card = document.getElementById("tiltCard");
    var shine = document.getElementById("cardShine");
    if (card && fine) {
        var rect = null;
        card.addEventListener("mouseenter", function () { rect = card.getBoundingClientRect(); });
        card.addEventListener("mousemove", function (e) {
            if (!rect) rect = card.getBoundingClientRect();
            var px = (e.clientX - rect.left) / rect.width;
            var py = (e.clientY - rect.top) / rect.height;
            var rx = (0.5 - py) * 10;
            var ry = (px - 0.5) * 12;
            card.style.transform = "rotateX(" + rx.toFixed(2) + "deg) rotateY(" + ry.toFixed(2) + "deg)";
            card.style.transition = "box-shadow 0.35s";
            if (shine) {
                shine.style.setProperty("--mx", (px * 100).toFixed(1) + "%");
                shine.style.setProperty("--my", (py * 100).toFixed(1) + "%");
            }
        });
        card.addEventListener("mouseleave", function () {
            card.style.transition = "transform 0.6s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.35s";
            card.style.transform = "rotateX(0deg) rotateY(0deg)";
            rect = null;
        });
    }

    // ── Duplicate marquee content for a seamless loop ──
    var track = document.getElementById("marqueeTrack");
    if (track) track.innerHTML += track.innerHTML;
})();
</script>
</body>
</html>
