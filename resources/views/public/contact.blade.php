@extends('layouts.public')
@section('title', 'Contact Us — Trinetraa Optician Nashik')
@section('meta_description', 'Visit Trinetraa Optician in Nashik or call us to book an eye test, enquire about frames, or get directions. Open Mon–Sat 10am–8pm, Sunday 11am–6pm.')
@section('canonical', 'https://trinetraaoptician.com/contact')
@section('content')
@php
    $services = [
        'Eye Examination',
        'Prescription Eyeglasses',
        'Sunglasses',
        'Contact Lenses',
        'Progressive Lenses',
        "Children's Eyewear",
        'Frame Repair / Adjustment',
        'Other',
    ];
    $mapsEmbed = 'https://maps.google.com/maps?q=' . urlencode($site['contactAddress']) . '&output=embed&z=16';
    $phoneClean = preg_replace('/\s/', '', $site['contactPhone']);
@endphp

<style>
    @media (prefers-reduced-motion: reduce) {
        * { transition: none !important; }
    }

    /* ── Page ── */
    .ct-page {
        background: transparent;
        min-height: 100vh;
        padding-top: 88px;
    }

    /* ── Hero header ── */
    .ct-hero {
        background: linear-gradient(135deg, rgba(15,118,110,0.35) 0%, rgba(11,18,32,0.15) 100%), rgba(255,255,255,0.03);
        padding: 64px 24px 56px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .ct-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 80% 60% at 50% 120%, rgba(37,99,235,0.25) 0%, transparent 70%);
        pointer-events: none;
    }
    .ct-hero__eyebrow {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #5EEAD4;
        margin-bottom: 1rem;
        position: relative;
    }
    .ct-hero__title {
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(2.4rem, 5vw, 3.6rem);
        font-weight: 700;
        color: #fff;
        line-height: 1.15;
        text-wrap: balance;
        margin: 0 0 1rem;
        position: relative;
    }
    .ct-hero__sub {
        font-size: 1.05rem;
        color: #5EEAD4;
        max-width: 480px;
        margin: 0 auto;
        line-height: 1.6;
        position: relative;
    }
    .ct-hero__breadcrumb {
        margin-top: 1.5rem;
        font-size: 0.8rem;
        color: rgba(94,234,212,0.65);
        position: relative;
    }
    .ct-hero__breadcrumb a { color: rgba(94,234,212,0.75); text-decoration: none; }
    .ct-hero__breadcrumb a:hover { color: #5EEAD4; }

    /* ── Body layout ── */
    .ct-body {
        max-width: 1160px;
        margin: 0 auto;
        padding: 64px 24px 80px;
    }
    .ct-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 48px;
        align-items: start;
    }
    @media (max-width: 820px) {
        .ct-grid { grid-template-columns: 1fr; gap: 40px; }
    }

    /* ── Form card ── */
    .ct-card {
        background: rgba(255,255,255,0.06);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 2px 24px rgba(0,0,0,0.35);
    }
    @media (max-width: 520px) {
        .ct-card { padding: 28px 20px; }
    }
    .ct-card__label {
        display: block;
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #8FA3BB;
        margin-bottom: 0.4rem;
    }
    .ct-card__title {
        font-family: Georgia, "Times New Roman", serif;
        font-size: 1.65rem;
        font-weight: 700;
        color: #F8FAFC;
        margin: 0 0 1.75rem;
        line-height: 1.2;
    }
    .ct-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }
    @media (max-width: 520px) {
        .ct-form-row { grid-template-columns: 1fr; }
    }
    .ct-form-group {
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .ct-form-group + .ct-form-group,
    .ct-form-row + .ct-form-group,
    .ct-form-group + .ct-form-row {
        margin-top: 14px;
    }
    .ct-submit-btn {
        width: 100%;
        padding: 0.9rem 1.5rem;
        background: #2563EB;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        margin-top: 20px;
        letter-spacing: 0.02em;
    }
    .ct-submit-btn:hover:not(:disabled) {
        background: #1D4ED8;
        box-shadow: 0 4px 16px rgba(37,99,235,0.35);
        transform: translateY(-1px);
    }
    .ct-submit-btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }
    .ct-submit-btn:focus-visible {
        outline: 3px solid rgba(37,99,235,0.5);
        outline-offset: 2px;
    }

    /* ── Right column ── */
    .ct-info-stack {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .ct-info-card {
        background: rgba(255,255,255,0.06);
        border-radius: 16px;
        padding: 24px 28px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.35);
    }
    .ct-info-card__title {
        font-family: Georgia, "Times New Roman", serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: #F8FAFC;
        margin: 0 0 16px;
    }
    .ct-info-items {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .ct-info-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .ct-info-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #E8F0FE;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .ct-info-detail {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding-top: 2px;
    }
    .ct-info-detail__key {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: #8FA3BB;
    }
    .ct-info-detail__val {
        font-size: 0.9rem;
        color: #F8FAFC;
        font-weight: 500;
        line-height: 1.4;
    }
    .ct-info-detail__val a {
        color: #2DD4BF;
        text-decoration: none;
    }
    .ct-info-detail__val a:hover { text-decoration: underline; }

    /* Hours table */
    .ct-hours {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 6px 20px;
        font-size: 0.88rem;
        color: #B8C6D8;
        margin-top: 4px;
    }
    .ct-hours__day { font-weight: 600; color: #F8FAFC; }
    .ct-hours__time { color: #8FA3BB; }

    /* Map card */
    .ct-map-card {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 16px rgba(10,22,40,0.08);
        height: 220px;
    }
    .ct-map-card iframe {
        display: block;
        width: 100%;
        height: 100%;
        border: 0;
    }

    /* ── Alert states ── */
    .ct-alert {
        padding: 0.85rem 1rem;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 20px;
        line-height: 1.5;
    }
    .ct-alert--success { background: rgba(52,211,153,0.14); color: #34D399; border: 1px solid rgba(52,211,153,0.35); }
    .ct-alert--error   { background: rgba(239,68,68,0.14); color: #F87171; border: 1px solid rgba(239,68,68,0.35); }

    /* ── CTA strip ── */
    .ct-cta {
        background: linear-gradient(135deg, rgba(15,118,110,0.35) 0%, rgba(11,18,32,0.15) 100%), rgba(255,255,255,0.03);
        padding: 64px 24px;
    }
    .ct-cta__inner {
        max-width: 1160px;
        margin: 0 auto;
    }
    .ct-cta__heading {
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(1.6rem, 3vw, 2.2rem);
        font-weight: 700;
        color: #fff;
        text-align: center;
        margin: 0 0 0.6rem;
        text-wrap: balance;
    }
    .ct-cta__sub {
        text-align: center;
        color: #5EEAD4;
        font-size: 0.95rem;
        margin: 0 0 40px;
    }
    .ct-cta__cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
    @media (max-width: 640px) {
        .ct-cta__cards { grid-template-columns: 1fr; }
    }
    .ct-action-card {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 16px;
        padding: 28px 24px;
        text-align: center;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .ct-action-card:hover {
        background: rgba(255,255,255,0.12);
        transform: translateY(-3px);
        box-shadow: 0 8px 32px rgba(0,0,0,0.25);
    }
    .ct-action-card:focus-visible {
        outline: 2px solid #5EEAD4;
        outline-offset: 3px;
    }
    .ct-action-card__icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ct-action-card__title {
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
    }
    .ct-action-card__desc {
        font-size: 0.82rem;
        color: rgba(94,234,212,0.8);
        line-height: 1.5;
    }

    /* Reveal blocks */
    .ct-reveal {
        opacity: 0;
        transform: translateY(28px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }
    .ct-reveal.is-visible {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<div class="ct-page">

    {{-- ── Hero ── --}}
    <div class="ct-hero">
        <div class="ct-hero__eyebrow">Trinetraa Optician</div>
        <h1 class="ct-hero__title">Get in Touch</h1>
        <p class="ct-hero__sub">
            Questions about frames, lenses, or an appointment? We&apos;d love to hear from you.
        </p>
        <div class="ct-hero__breadcrumb">
            <a href="/">Home</a> &nbsp;/&nbsp; Contact
        </div>
    </div>

    {{-- ── 2-column body ── --}}
    <div class="ct-body">
        <div class="ct-grid">

            {{-- LEFT: Contact form --}}
            <div class="ct-reveal" data-reveal-delay="0">
                <div class="ct-card">
                    <span class="ct-card__label">Send a Message</span>
                    <h2 class="ct-card__title">We&apos;ll reply within the day</h2>

                    <div id="ctSuccess" class="ct-alert ct-alert--success" role="status" style="display:none"></div>
                    <div id="ctError" class="ct-alert ct-alert--error" role="alert" style="display:none"></div>

                    <form id="ctForm" novalidate>
                        <div class="ct-form-row">
                            <div class="ct-form-group">
                                <label class="ct-card__label" for="ct-name">Full Name *</label>
                                <input
                                    id="ct-name"
                                    name="name"
                                    type="text"
                                    placeholder="Aisha Mehta"
                                    required
                                    class="ct-input"
                                    style="width:100%;padding:0.8rem 1rem;border:1.5px solid rgba(255,255,255,0.2);border-radius:10px;font-size:0.95rem;font-family:inherit;background:rgba(255,255,255,0.07);color:#F1F5F9;outline:none;transition:border-color 0.2s, background 0.2s, box-shadow 0.2s;box-sizing:border-box"
                                />
                            </div>
                            <div class="ct-form-group">
                                <label class="ct-card__label" for="ct-phone">Phone</label>
                                <input
                                    id="ct-phone"
                                    name="phone"
                                    type="tel"
                                    placeholder="+91 98765 43210"
                                    class="ct-input"
                                    style="width:100%;padding:0.8rem 1rem;border:1.5px solid rgba(255,255,255,0.2);border-radius:10px;font-size:0.95rem;font-family:inherit;background:rgba(255,255,255,0.07);color:#F1F5F9;outline:none;transition:border-color 0.2s, background 0.2s, box-shadow 0.2s;box-sizing:border-box"
                                />
                            </div>
                        </div>

                        <div class="ct-form-group" style="margin-top:14px">
                            <label class="ct-card__label" for="ct-email">Email Address *</label>
                            <input
                                id="ct-email"
                                name="email"
                                type="email"
                                placeholder="aisha@example.com"
                                required
                                class="ct-input"
                                style="width:100%;padding:0.8rem 1rem;border:1.5px solid rgba(255,255,255,0.2);border-radius:10px;font-size:0.95rem;font-family:inherit;background:rgba(255,255,255,0.07);color:#F1F5F9;outline:none;transition:border-color 0.2s, background 0.2s, box-shadow 0.2s;box-sizing:border-box"
                            />
                        </div>

                        <div class="ct-form-group" style="margin-top:14px">
                            <label class="ct-card__label" for="ct-service">Service Interested In</label>
                            <select
                                id="ct-service"
                                name="service"
                                class="ct-input"
                                style="width:100%;padding:0.8rem 1rem;border:1.5px solid rgba(255,255,255,0.2);border-radius:10px;font-size:0.95rem;font-family:inherit;background:rgba(255,255,255,0.07);color:#F1F5F9;outline:none;transition:border-color 0.2s, background 0.2s, box-shadow 0.2s;box-sizing:border-box;appearance:none;background-image:url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%239FB1C7' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E&quot;);background-repeat:no-repeat;background-position:right 14px center;padding-right:2.5rem;cursor:pointer"
                            >
                                <option value="">Select a service…</option>
                                @foreach($services as $s)
                                    <option value="{{ $s }}">{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="ct-form-group" style="margin-top:14px">
                            <label class="ct-card__label" for="ct-message">Message *</label>
                            <textarea
                                id="ct-message"
                                name="message"
                                placeholder="Tell us what you need — prescription details, frame styles, appointment timing…"
                                required
                                rows="5"
                                class="ct-input"
                                style="width:100%;padding:0.8rem 1rem;border:1.5px solid rgba(255,255,255,0.2);border-radius:10px;font-size:0.95rem;font-family:inherit;background:rgba(255,255,255,0.07);color:#F1F5F9;outline:none;transition:border-color 0.2s, background 0.2s, box-shadow 0.2s;box-sizing:border-box;resize:vertical;min-height:120px"
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            class="ct-submit-btn"
                            id="ctSubmit"
                        >
                            Send Message
                        </button>
                    </form>
                </div>
            </div>

            {{-- RIGHT: Info + Map + Hours --}}
            <div class="ct-info-stack">

                <div class="ct-reveal" data-reveal-delay="100">
                    <div class="ct-info-card">
                        <h3 class="ct-info-card__title">Contact Details</h3>
                        <div class="ct-info-items">
                            <div class="ct-info-item">
                                <div class="ct-info-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 6-9 12-9 12S3 16 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <div class="ct-info-detail">
                                    <span class="ct-info-detail__key">Address</span>
                                    <span class="ct-info-detail__val">
                                        <a href="{{ $site['googleMapsUrl'] }}" target="_blank" rel="noopener noreferrer">
                                            {{ $site['contactAddress'] }}
                                        </a>
                                    </span>
                                </div>
                            </div>

                            <div class="ct-info-item">
                                <div class="ct-info-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92v2z"/></svg>
                                </div>
                                <div class="ct-info-detail">
                                    <span class="ct-info-detail__key">Phone</span>
                                    <span class="ct-info-detail__val">
                                        <a href="tel:{{ $phoneClean }}">{{ $site['contactPhone'] }}</a>
                                    </span>
                                </div>
                            </div>

                            <div class="ct-info-item">
                                <div class="ct-info-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                </div>
                                <div class="ct-info-detail">
                                    <span class="ct-info-detail__key">Email</span>
                                    <span class="ct-info-detail__val">
                                        <a href="mailto:{{ $site['contactEmail'] }}">{{ $site['contactEmail'] }}</a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ct-reveal" data-reveal-delay="180">
                    <div class="ct-map-card">
                        <iframe
                            title="Trinetraa Optician on Google Maps"
                            src="{{ $mapsEmbed }}"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        ></iframe>
                    </div>
                </div>

                <div class="ct-reveal" data-reveal-delay="220">
                    <div class="ct-info-card" style="text-align:center">
                        <h3 class="ct-info-card__title">📍 Scan to Find Us</h3>
                        <a href="{{ $site['googleMapsUrl'] }}" target="_blank" rel="noopener noreferrer" title="Open in Google Maps">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($site['googleMapsUrl']) }}&color=0a1628&bgcolor=ffffff&qzone=1&format=png"
                                 alt="QR Code — Trinetraa Optician Location"
                                 width="120" height="120" loading="lazy"
                                 style="border-radius:8px;border:3px solid #E8F0FE;margin:0 auto .75rem;display:block">
                        </a>
                        <a href="{{ $site['googleMapsUrl'] }}" target="_blank" rel="noopener noreferrer"
                           style="display:inline-flex;align-items:center;gap:.4rem;background:#4285f4;color:#fff;border-radius:8px;padding:.45rem 1rem;font-size:.85rem;font-weight:600;text-decoration:none;margin-bottom:.5rem">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                            Open in Google Maps
                        </a>
                        <br>
                        <a href="https://g.page/r/trinetraa-optician/review" target="_blank" rel="noopener noreferrer"
                           style="display:inline-flex;align-items:center;gap:.4rem;background:transparent;color:#4285f4;border:1.5px solid #4285f4;border-radius:8px;padding:.4rem .9rem;font-size:.8rem;font-weight:600;text-decoration:none">
                            ⭐ Leave a Google Review
                        </a>
                    </div>
                </div>

                <div class="ct-reveal" data-reveal-delay="240">
                    <div class="ct-info-card">
                        <h3 class="ct-info-card__title">Store Hours</h3>
                        <div class="ct-hours">
                            <span class="ct-hours__day">Mon – Sat</span>
                            <span class="ct-hours__time">10:00 AM – 8:00 PM</span>
                            <span class="ct-hours__day">Sunday</span>
                            <span class="ct-hours__time">11:00 AM – 6:00 PM</span>
                        </div>
                        <div style="margin-top:14px;padding-top:14px;border-top:1px solid #E8F0FE;display:flex;align-items:center;gap:8px">
                            <span style="width:8px;height:8px;border-radius:50%;background:#22C55E;flex-shrink:0;box-shadow:0 0 0 3px rgba(34,197,94,0.2)"></span>
                            <span style="font-size:0.82rem;color:#8FA3BB">Walk-ins welcome during store hours</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── CTA Strip ── --}}
    <div class="ct-cta">
        <div class="ct-cta__inner">
            <div class="ct-reveal" data-reveal-delay="0">
                <h2 class="ct-cta__heading">Prefer to reach us directly?</h2>
                <p class="ct-cta__sub">Pick the channel that suits you best — we typically respond within an hour.</p>
            </div>
            <div class="ct-reveal" data-reveal-delay="80">
                <div class="ct-cta__cards">

                    <a
                        href="https://wa.me/{{ $site['whatsappNumber'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="ct-action-card"
                        aria-label="Chat on WhatsApp"
                    >
                        <div class="ct-action-card__icon" style="background:rgba(37,211,102,0.15)">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="#25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </div>
                        <span class="ct-action-card__title">WhatsApp</span>
                        <span class="ct-action-card__desc">Chat instantly — share photos of frames or prescriptions</span>
                    </a>

                    <a
                        href="tel:{{ $phoneClean }}"
                        class="ct-action-card"
                        aria-label="Call us at {{ $site['contactPhone'] }}"
                    >
                        <div class="ct-action-card__icon" style="background:rgba(37,99,235,0.18)">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#5EEAD4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92v2z"/></svg>
                        </div>
                        <span class="ct-action-card__title">Call Us</span>
                        <span class="ct-action-card__desc">{{ $site['contactPhone'] }} — speak with our team directly</span>
                    </a>

                    <a
                        href="mailto:{{ $site['contactEmail'] }}"
                        class="ct-action-card"
                        aria-label="Email us at {{ $site['contactEmail'] }}"
                    >
                        <div class="ct-action-card__icon" style="background:rgba(94,234,212,0.15)">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#5EEAD4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <span class="ct-action-card__title">Email</span>
                        <span class="ct-action-card__desc">{{ $site['contactEmail'] }} — for detailed queries</span>
                    </a>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    // ── Scroll reveal (IntersectionObserver) ──
    var reveals = document.querySelectorAll('.ct-reveal');
    if ('IntersectionObserver' in window) {
        reveals.forEach(function (el) {
            var delay = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
            el.style.transitionDelay = delay + 'ms';
            var obs = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        el.classList.add('is-visible');
                        obs.disconnect();
                    }
                });
            }, { threshold: 0.12 });
            obs.observe(el);
        });
    } else {
        reveals.forEach(function (el) { el.classList.add('is-visible'); });
    }

    // ── Input focus styling (mirrors React focused state) ──
    var inputs = document.querySelectorAll('.ct-input');
    inputs.forEach(function (el) {
        el.addEventListener('focus', function () {
            el.style.borderColor = '#2DD4BF';
            el.style.background = 'rgba(255,255,255,0.10)';
            el.style.boxShadow = '0 0 0 3px rgba(37,99,235,0.12)';
        });
        el.addEventListener('blur', function () {
            el.style.borderColor = 'rgba(255,255,255,0.2)';
            el.style.background = 'rgba(255,255,255,0.07)';
            el.style.boxShadow = 'none';
        });
    });

    // ── Form submit ──
    var form = document.getElementById('ctForm');
    var successBox = document.getElementById('ctSuccess');
    var errorBox = document.getElementById('ctError');
    var submitBtn = document.getElementById('ctSubmit');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending…';
        errorBox.style.display = 'none';
        successBox.style.display = 'none';

        var payload = {
            name: document.getElementById('ct-name').value,
            phone: document.getElementById('ct-phone').value,
            email: document.getElementById('ct-email').value,
            service: document.getElementById('ct-service').value,
            message: document.getElementById('ct-message').value,
        };

        try {
            var r = await window.apiFetch('/api/public/contact-us', {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            var d = await r.json();
            if (!r.ok) {
                errorBox.textContent = d.message || 'Failed to send. Please try again.';
                errorBox.style.display = 'block';
                return;
            }
            successBox.textContent = d.message || "Message sent! We'll be in touch soon.";
            successBox.style.display = 'block';
            // On success the original hides the form
            form.style.display = 'none';
            form.reset();
        } catch (err) {
            errorBox.textContent = 'Network error. Please try again.';
            errorBox.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Send Message';
        }
    });
})();
</script>
@endverbatim
@endpush
