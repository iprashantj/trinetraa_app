@extends('layouts.public')

@section('title', 'About Us — Trinetraa Optician Nashik | Eye Care Since 2015')
@section('meta_description', 'Learn about Trinetraa Optician — Nashik\'s trusted optical store since 2015. Expert optometrists, 1000+ premium frames, personalised eye care in Nashik, Maharashtra.')
@section('canonical', 'https://trinetraaoptician.com/about')

@section('content')
<style>
/* ── Reset & tokens ── */
.ab-page *, .ab-page *::before, .ab-page *::after { box-sizing: border-box; margin: 0; padding: 0; }

.ab-page {
  --white:      #ffffff;
  --sky-50:     #f0f7ff;
  --sky-100:    #dbeafe;
  --sky-200:    #bfdbfe;
  --sky-600:    #2563eb;
  --sky-700:    #1d4ed8;
  --navy:       #0f2a4a;
  --slate-700:  #334155;
  --slate-500:  #64748b;
  --slate-300:  #cbd5e1;
  --amber:      #f59e0b;
  --radius-sm:  6px;
  --radius-md:  12px;
  --radius-lg:  20px;
  --shadow-sm:  0 1px 3px rgba(15,42,74,.08), 0 1px 2px rgba(15,42,74,.06);
  --shadow-md:  0 4px 16px rgba(15,42,74,.10);
  --shadow-lg:  0 12px 40px rgba(15,42,74,.14);
  --container:  1200px;
}

/* ── Reveal animation ── */
.ab-page [data-reveal] {
  opacity: 0;
  transform: translateY(28px);
  transition: opacity .6s ease, transform .6s ease;
}
.ab-page [data-reveal].revealed {
  opacity: 1;
  transform: none;
}
.ab-page [data-reveal][data-delay="1"] { transition-delay: .1s; }
.ab-page [data-reveal][data-delay="2"] { transition-delay: .2s; }
.ab-page [data-reveal][data-delay="3"] { transition-delay: .3s; }
.ab-page [data-reveal][data-delay="4"] { transition-delay: .4s; }
@media (prefers-reduced-motion: reduce) {
  .ab-page [data-reveal] { opacity: 1; transform: none; transition: none; }
}

/* ── Layout helpers ── */
.ab-container {
  max-width: var(--container);
  margin: 0 auto;
  padding: 0 1.5rem;
}

/* ── HERO ── */
.ab-hero {
  padding-top: 88px;
  background: linear-gradient(160deg, #e8f2ff 0%, #dbeafe 55%, #bfdbfe 100%);
  text-align: center;
  padding-bottom: 4rem;
}
.ab-hero__eyebrow {
  display: inline-block;
  background: var(--sky-600);
  color: #fff;
  font-size: .72rem;
  font-weight: 700;
  letter-spacing: .12em;
  text-transform: uppercase;
  padding: .3rem .85rem;
  border-radius: 999px;
  margin-bottom: 1.25rem;
  margin-top: 3.5rem;
}
.ab-hero h1 {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: clamp(2.1rem, 5vw, 3.4rem);
  font-weight: 700;
  color: var(--navy);
  line-height: 1.2;
  text-wrap: balance;
  margin-bottom: 1rem;
}
.ab-hero__sub {
  max-width: 560px;
  margin: 0 auto;
  font-size: 1.05rem;
  color: var(--slate-700);
  line-height: 1.7;
}

/* ── ABOUT SECTION (mosaic + content) ── */
.ab-about {
  padding: 6rem 0;
  background: var(--white);
}
.ab-about__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
  align-items: center;
}
@media (max-width: 860px) {
  .ab-about__grid { grid-template-columns: 1fr; gap: 2.5rem; }
  .ab-mosaic { max-width: 460px; margin: 0 auto; }
}

/* Mosaic */
.ab-mosaic {
  position: relative;
  display: grid;
  grid-template-rows: 240px 160px;
  gap: .75rem;
}
.ab-mosaic__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: var(--radius-md);
  display: block;
}
.ab-mosaic__badge {
  position: absolute;
  bottom: -1.25rem;
  right: -1.25rem;
  background: var(--sky-600);
  color: #fff;
  border-radius: var(--radius-md);
  padding: .9rem 1.3rem;
  font-size: .78rem;
  font-weight: 700;
  text-align: center;
  box-shadow: var(--shadow-md);
  line-height: 1.3;
}
.ab-mosaic__badge strong { font-size: 1.6rem; display: block; }

/* Content */
.ab-about__badge {
  display: inline-flex;
  align-items: center;
  gap: .45rem;
  border: 1.5px solid var(--sky-200);
  border-radius: 999px;
  font-size: .72rem;
  font-weight: 700;
  letter-spacing: .09em;
  text-transform: uppercase;
  color: var(--sky-600);
  padding: .3rem 1rem;
  margin-bottom: 1.1rem;
}
.ab-about__badge::before {
  content: '';
  width: 6px; height: 6px;
  background: var(--sky-600);
  border-radius: 50%;
}
.ab-about h2 {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: clamp(1.6rem, 3vw, 2.2rem);
  color: var(--navy);
  font-weight: 700;
  line-height: 1.25;
  text-wrap: balance;
  margin-bottom: 1.1rem;
}
.ab-about__desc {
  color: var(--slate-700);
  line-height: 1.8;
  font-size: 1rem;
  margin-bottom: 1.75rem;
}
.ab-checks {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: .75rem;
  margin-bottom: 2rem;
}
.ab-checks li {
  display: flex;
  align-items: flex-start;
  gap: .75rem;
  color: var(--slate-700);
  font-size: .95rem;
  line-height: 1.55;
}
.ab-checks li::before {
  content: '';
  flex-shrink: 0;
  margin-top: .2rem;
  width: 18px; height: 18px;
  background: var(--amber);
  border-radius: 50%;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 16 16' fill='none' stroke='%23fff' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M3 8l3.5 3.5L13 4.5'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: center;
  background-size: 70%;
}
.ab-btns {
  display: flex;
  gap: .9rem;
  flex-wrap: wrap;
}
.ab-btn-primary {
  background: var(--sky-600);
  color: #fff;
  text-decoration: none;
  padding: .75rem 1.75rem;
  border-radius: var(--radius-sm);
  font-size: .9rem;
  font-weight: 600;
  transition: background .2s, box-shadow .2s;
  box-shadow: 0 2px 8px rgba(37,99,235,.25);
}
.ab-btn-primary:hover { background: var(--sky-700); box-shadow: 0 4px 14px rgba(37,99,235,.35); }
.ab-btn-outline {
  border: 1.5px solid var(--slate-300);
  color: var(--slate-700);
  text-decoration: none;
  padding: .75rem 1.75rem;
  border-radius: var(--radius-sm);
  font-size: .9rem;
  font-weight: 600;
  background: transparent;
  transition: border-color .2s, color .2s;
}
.ab-btn-outline:hover { border-color: var(--sky-600); color: var(--sky-600); }

/* ── STATS BAR ── */
.ab-stats {
  background: var(--navy);
  padding: 2.5rem 0;
}
.ab-stats__grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0;
}
@media (max-width: 640px) {
  .ab-stats__grid { grid-template-columns: repeat(2, 1fr); }
}
.ab-stats__item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 1rem;
  border-right: 1px solid rgba(255,255,255,.1);
  gap: .4rem;
}
.ab-stats__item:last-child { border-right: none; }
.ab-stats__icon { font-size: 1.5rem; line-height: 1; }
.ab-stats__value {
  font-family: Georgia, serif;
  font-size: 2rem;
  font-weight: 700;
  color: var(--white);
  font-variant-numeric: tabular-nums;
  line-height: 1;
}
.ab-stats__label {
  font-size: .75rem;
  font-weight: 600;
  letter-spacing: .07em;
  text-transform: uppercase;
  color: var(--sky-200);
}

/* ── TEAM ── */
.ab-team {
  padding: 6rem 0;
  background: var(--sky-50);
}
.ab-section-header {
  text-align: center;
  margin-bottom: 3rem;
}
.ab-section-eyebrow {
  font-size: .72rem;
  font-weight: 700;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: var(--sky-600);
  margin-bottom: .6rem;
}
.ab-section-title {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: clamp(1.6rem, 3vw, 2.2rem);
  font-weight: 700;
  color: var(--navy);
  text-wrap: balance;
}
.ab-team__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.75rem;
}
@media (max-width: 760px) {
  .ab-team__grid { grid-template-columns: 1fr; max-width: 360px; margin: 0 auto; }
}
.ab-team-card {
  background: var(--white);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  transition: transform .25s, box-shadow .25s;
}
.ab-team-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}
.ab-team-card__img {
  width: 100%;
  aspect-ratio: 3/4;
  object-fit: cover;
  display: block;
  background: var(--sky-100);
}
.ab-team-card__body {
  padding: 1.25rem 1.5rem 1.5rem;
}
.ab-team-card__name {
  font-weight: 700;
  font-size: 1rem;
  color: var(--navy);
  margin-bottom: .2rem;
}
.ab-team-card__role {
  font-size: .82rem;
  color: var(--sky-600);
  font-weight: 500;
}

/* ── VALUES ── */
.ab-values {
  padding: 6rem 0;
  background: var(--white);
}
.ab-values__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.75rem;
}
@media (max-width: 760px) {
  .ab-values__grid { grid-template-columns: 1fr; }
}
.ab-value-card {
  border: 1.5px solid var(--sky-100);
  border-radius: var(--radius-lg);
  padding: 2.25rem 2rem;
  transition: border-color .25s, box-shadow .25s;
}
.ab-value-card:hover {
  border-color: var(--sky-200);
  box-shadow: var(--shadow-md);
}
.ab-value-card__icon {
  width: 54px; height: 54px;
  background: var(--sky-50);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--sky-600);
  margin-bottom: 1.25rem;
}
.ab-value-card__title {
  font-family: Georgia, serif;
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--navy);
  margin-bottom: .6rem;
}
.ab-value-card__text {
  font-size: .92rem;
  color: var(--slate-500);
  line-height: 1.7;
}

/* ── CTA ── */
.ab-cta {
  background: linear-gradient(135deg, var(--sky-600) 0%, #1a4fd8 100%);
  padding: 5rem 0;
  text-align: center;
}
.ab-cta h2 {
  font-family: Georgia, serif;
  font-size: clamp(1.7rem, 3.5vw, 2.4rem);
  font-weight: 700;
  color: #fff;
  margin-bottom: .85rem;
  text-wrap: balance;
}
.ab-cta p {
  color: rgba(255,255,255,.85);
  font-size: 1.05rem;
  max-width: 500px;
  margin: 0 auto 2.25rem;
  line-height: 1.7;
}
.ab-cta__btns {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}
.ab-cta-btn-white {
  background: #fff;
  color: var(--sky-700);
  text-decoration: none;
  padding: .82rem 2rem;
  border-radius: var(--radius-sm);
  font-size: .95rem;
  font-weight: 700;
  transition: background .2s, box-shadow .2s;
  box-shadow: 0 2px 12px rgba(0,0,0,.15);
}
.ab-cta-btn-white:hover { background: var(--sky-50); }
.ab-cta-btn-ghost {
  border: 2px solid rgba(255,255,255,.55);
  color: #fff;
  text-decoration: none;
  padding: .78rem 2rem;
  border-radius: var(--radius-sm);
  font-size: .95rem;
  font-weight: 600;
  background: transparent;
  transition: border-color .2s, background .2s;
}
.ab-cta-btn-ghost:hover { border-color: #fff; background: rgba(255,255,255,.1); }
</style>

<div class="ab-page">

  {{-- ── HERO ── --}}
  <section class="ab-hero">
    <div class="ab-container">
      <div data-reveal>
        <span class="ab-hero__eyebrow">Est. 2015 · Nashik Road</span>
        <h1>About Trinetraa</h1>
        <p class="ab-hero__sub">
          Nashik's trusted eye care destination — where clinical precision meets genuine care for every patient.
        </p>
      </div>
    </div>
  </section>

  {{-- ── ABOUT ── --}}
  <section class="ab-about">
    <div class="ab-container">
      <div class="ab-about__grid">
        {{-- Mosaic --}}
        <div class="ab-mosaic" data-reveal>
          <img
            class="ab-mosaic__img"
            src="https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=500&h=380&fit=crop&auto=format&q=80"
            alt="Eye examination at Trinetraa Optician"
            style="height:240px">
          <img
            class="ab-mosaic__img"
            src="https://images.unsplash.com/photo-1576671081837-49000212a370?w=500&h=320&fit=crop&auto=format&q=80"
            alt="Slit lamp diagnostic equipment"
            style="height:160px">
          <div class="ab-mosaic__badge">
            <strong>2015</strong>
            Serving Nashik
          </div>
        </div>

        {{-- Content --}}
        <div data-reveal data-delay="2">
          <span class="ab-about__badge">About Us</span>
          <h2>Caring for Your Vision,<br>Every Step of the Way</h2>
          <p class="ab-about__desc">
            Trinetraa Optician has served Nashik's community since 2015 with professional eye care
            and premium eyewear. Located at Narayan Bapu Chowk, Nashik Road, our certified
            optometrists use state-of-the-art diagnostic equipment to deliver accurate prescriptions
            and lasting vision solutions.
          </p>
          <ul class="ab-checks">
            @php
              $checks = [
                'Certified optometrists with 10+ years experience',
                'Modern diagnostic equipment including slit lamp & auto-refractometer',
                '300+ premium frame brands from global designers',
              ];
            @endphp
            @foreach($checks as $c)
              <li>{{ $c }}</li>
            @endforeach
          </ul>
          <div class="ab-btns">
            <a href="/appointment" class="ab-btn-primary">Book Appointment</a>
            <a href="/contact" class="ab-btn-outline">Our Location</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ── STATS BAR ── --}}
  <div class="ab-stats" data-reveal>
    <div class="ab-container">
      <div class="ab-stats__grid">
        @php
          $stats = [
            ['icon' => '🕐', 'value' => '10+', 'label' => 'Years Experience'],
            ['icon' => '😊', 'value' => '500+', 'label' => 'Happy Patients'],
            ['icon' => '🕶️', 'value' => '300+', 'label' => 'Frame Brands'],
            ['icon' => '🏆', 'value' => '8+', 'label' => 'Awards'],
          ];
        @endphp
        @foreach($stats as $s)
          <div class="ab-stats__item">
            <span class="ab-stats__icon">{{ $s['icon'] }}</span>
            <span class="ab-stats__value">{{ $s['value'] }}</span>
            <span class="ab-stats__label">{{ $s['label'] }}</span>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- ── TEAM ── --}}
  <section class="ab-team">
    <div class="ab-container">
      <div class="ab-section-header" data-reveal>
        <p class="ab-section-eyebrow">Meet the Experts</p>
        <h2 class="ab-section-title">Our Team</h2>
      </div>
      <div class="ab-team__grid">
        @php
          $team = [
            ['name' => 'Dr. Rajesh Patil', 'role' => 'Chief Optometrist', 'img' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=300&h=380&fit=crop&crop=faces&auto=format&q=80'],
            ['name' => 'Dr. Priya Sharma', 'role' => 'Paediatric Specialist', 'img' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=300&h=380&fit=crop&crop=faces&auto=format&q=80'],
            ['name' => 'Amit Kulkarni', 'role' => 'Frame Consultant', 'img' => 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=300&h=380&fit=crop&crop=faces&auto=format&q=80'],
          ];
        @endphp
        @foreach($team as $i => $m)
          <div class="ab-team-card" data-reveal data-delay="{{ $i + 1 }}">
            <img class="ab-team-card__img" src="{{ $m['img'] }}" alt="{{ $m['name'] }}">
            <div class="ab-team-card__body">
              <p class="ab-team-card__name">{{ $m['name'] }}</p>
              <p class="ab-team-card__role">{{ $m['role'] }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ── VALUES ── --}}
  <section class="ab-values">
    <div class="ab-container">
      <div class="ab-section-header" data-reveal>
        <p class="ab-section-eyebrow">What Guides Us</p>
        <h2 class="ab-section-title">Our Values</h2>
      </div>
      <div class="ab-values__grid">
        <div class="ab-value-card" data-reveal data-delay="1">
          <div class="ab-value-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:32px;height:32px">
              <circle cx="12" cy="12" r="3"/>
              <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
            </svg>
          </div>
          <p class="ab-value-card__title">Precision</p>
          <p class="ab-value-card__text">State-of-the-art slit lamps and auto-refractometers deliver diagnostic accuracy you can trust.</p>
        </div>
        <div class="ab-value-card" data-reveal data-delay="2">
          <div class="ab-value-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:32px;height:32px">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
          </div>
          <p class="ab-value-card__title">Compassion</p>
          <p class="ab-value-card__text">Every patient — from toddlers to seniors — receives unhurried, personalised attention from our team.</p>
        </div>
        <div class="ab-value-card" data-reveal data-delay="3">
          <div class="ab-value-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:32px;height:32px">
              <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
          </div>
          <p class="ab-value-card__title">Innovation</p>
          <p class="ab-value-card__text">We continuously invest in the latest diagnostic technology so Nashik gets world-class eye care.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ── CTA ── --}}
  <section class="ab-cta" data-reveal>
    <div class="ab-container">
      <h2>Ready to See More Clearly?</h2>
      <p>Book an appointment with our certified optometrists today. Walk-ins welcome at Narayan Bapu Chowk, Nashik Road.</p>
      <div class="ab-cta__btns">
        <a href="/appointment" class="ab-cta-btn-white">Book Appointment</a>
        <a href="/eyewears" class="ab-cta-btn-ghost">Browse Eyewear</a>
      </div>
    </div>
  </section>

</div>
@endsection

@push('scripts')
<script>
@verbatim
(function () {
  function initReveal() {
    var els = document.querySelectorAll(".ab-page [data-reveal]");
    if (!("IntersectionObserver" in window)) {
      els.forEach(function (el) { el.classList.add("revealed"); });
      return;
    }
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          e.target.classList.add("revealed");
          observer.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    els.forEach(function (el) { observer.observe(el); });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initReveal);
  } else {
    initReveal();
  }
})();
@endverbatim
</script>
@endpush
