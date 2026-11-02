@extends('layouts.public')
@php
    // Nashik (the original store) falls back to the site-wide contact settings until its own fields are filled in.
    $isHome = strtolower($branch->code) === 'nsk' || strtolower($branch->city ?? '') === 'nashik';
    $address = $branch->address ?: ($isHome ? $site['contactAddress'] : null);
    $phone   = $branch->phone ?: ($isHome ? $site['contactPhone'] : null);
    $email   = $branch->email ?: ($isHome ? $site['contactEmail'] : null);
    $hours   = $branch->hours ?: ($isHome ? ($site['storeHours'] ?? 'Mon–Sat: 10 AM – 8 PM') : null);
    $mapUrl  = $branch->map_url ?: ($isHome ? $site['googleMapsUrl'] : null);
    $ready   = (bool) ($address || $phone);
    $city    = $branch->city ?: $branch->name;
    $phoneClean = $phone ? preg_replace('/[^\d+]/', '', $phone) : null;
    $url = rtrim($site['siteUrl'] ?? 'https://trinetraaoptician.com', '/') . '/stores/' . strtolower($branch->code);
    $ld = [
        '@context' => 'https://schema.org', '@type' => 'Optician',
        '@id' => $url . '#store', 'name' => 'Trinetraa Optician ' . $branch->name, 'url' => $url,
        'image' => 'https://trinetraaoptician.com/icons/icon-512.png',
        'parentOrganization' => ['@id' => 'https://trinetraaoptician.com/#business'],
        'areaServed' => $city,
    ];
    if ($address) {
        $ld['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $address, 'addressLocality' => $city, 'addressRegion' => 'Maharashtra', 'addressCountry' => 'IN'];
    }
    if ($phoneClean) { $ld['telephone'] = $phoneClean; }
    if ($email) { $ld['email'] = $email; }
    if ($mapUrl) { $ld['hasMap'] = $mapUrl; }
@endphp
@section('title', 'Trinetraa Optician ' . $branch->name . ' | Eye Test, Glasses & Sunglasses')
@section('meta_description', 'Visit Trinetraa Optician in ' . $city . ($address ? ' — ' . \Illuminate\Support\Str::limit($address, 70) : '') . '. Eye tests, branded frames, sunglasses and contact lenses. ' . ($hours ? 'Open ' . $hours . '.' : ''))
@section('canonical', $url)
@unless($ready)
    @section('no_index', '1')
@endunless
@push('structured_data')
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://trinetraaoptician.com/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Our Stores', 'item' => 'https://trinetraaoptician.com/stores'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $branch->name, 'item' => $url],
    ]], JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<style>
    .st-page { padding: calc(64px + 3rem) 0 4rem; }
    .st-crumbs { font-size: .82rem; color: #8FA3BB; margin-bottom: 1.25rem; }
    .st-crumbs a { color: #5EEAD4; text-decoration: none; }
    .st-grid { display: grid; grid-template-columns: 1fr 1.1fr; gap: 2rem; align-items: start; }
    .st-card { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.14); border-radius: 18px; padding: 1.75rem; }
    .st-row { display: flex; gap: .85rem; padding: .85rem 0; border-bottom: 1px solid rgba(255,255,255,.08); color: #B8C6D8; }
    .st-row:last-child { border-bottom: none; }
    .st-row b { display: block; color: #F8FAFC; font-size: .8rem; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 2px; }
    .st-row a { color: #5EEAD4; text-decoration: none; }
    .st-map { border-radius: 18px; overflow: hidden; border: 1px solid rgba(255,255,255,.14); min-height: 380px; background: rgba(255,255,255,.04); }
    .st-map iframe { width: 100%; height: 100%; min-height: 380px; border: 0; display: block; }
    .st-cta { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1.5rem; }
    .st-chips { display: flex; gap: .6rem; flex-wrap: wrap; margin-top: 2.5rem; }
    .st-chips a { padding: .45rem 1rem; border: 1px solid rgba(255,255,255,.25); border-radius: 999px; color: #F8FAFC; text-decoration: none; font-size: .85rem; }
    .st-chips a:hover { border-color: #2DD4BF; color: #2DD4BF; }
    @media (max-width: 900px) { .st-grid { grid-template-columns: 1fr; } }
</style>
<div class="st-page">
    <div class="pp-container">
        <div class="st-crumbs"><a href="/">Home</a> › <a href="/stores">Our Stores</a> › {{ $branch->name }}</div>
        <h1 style="font-family:var(--pp-font-heading);font-size:clamp(1.9rem,4vw,2.8rem);font-weight:800;color:#F8FAFC;letter-spacing:-.03em;margin:0 0 .75rem">Trinetraa Optician {{ $branch->name }}</h1>
        <p style="color:#9FB1C7;max-width:620px;line-height:1.7;margin-bottom:2rem">
            Eye tests by qualified optometrists, prescription glasses, sunglasses and contact lenses from 20+ brands — at our {{ $city }} store.
        </p>
        <div class="st-grid">
            <div class="st-card">
                @if($ready)
                    @if($address)<div class="st-row"><span>📍</span><div><b>Address</b>{{ $address }}</div></div>@endif
                    @if($phone)<div class="st-row"><span>📞</span><div><b>Phone</b><a href="tel:{{ $phoneClean }}">{{ $phone }}</a></div></div>@endif
                    @if($hours)<div class="st-row"><span>🕐</span><div><b>Opening hours</b>{{ $hours }}</div></div>@endif
                    @if($email)<div class="st-row"><span>✉️</span><div><b>Email</b><a href="mailto:{{ $email }}">{{ $email }}</a></div></div>@endif
                @else
                    <p style="color:#B8C6D8;margin:0">Full details for our {{ $branch->name }} store are coming soon. Meanwhile, call our Nashik store on <a href="tel:{{ preg_replace('/[^\d+]/', '', $site['contactPhone']) }}" style="color:#5EEAD4">{{ $site['contactPhone'] }}</a> and we will help you.</p>
                @endif
                <div class="st-cta">
                    <a href="/appointment?branch={{ strtolower($branch->code) }}" class="trin-btn trin-btn--primary" style="display:inline-flex;padding:.8rem 1.5rem;border-radius:999px;background:#14B8A6;color:#04201d;font-weight:700;text-decoration:none">Book an eye test</a>
                    @if($mapUrl)<a href="{{ $mapUrl }}" target="_blank" rel="noopener" style="display:inline-flex;padding:.8rem 1.5rem;border-radius:999px;border:1.5px solid rgba(255,255,255,.4);color:#F8FAFC;font-weight:600;text-decoration:none">Get directions</a>@endif
                </div>
            </div>
            <div class="st-map">
                @if($address)
                    <iframe title="Map of Trinetraa Optician {{ $branch->name }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            src="https://maps.google.com/maps?q={{ urlencode($address) }}&output=embed&z=16"></iframe>
                @endif
            </div>
        </div>
        @if($others->count())
            <div class="st-chips" aria-label="Other stores">
                <span style="color:#8FA3BB;align-self:center">Other stores:</span>
                @foreach($others as $o)<a href="/stores/{{ strtolower($o->code) }}">{{ $o->name }}</a>@endforeach
            </div>
        @endif
    </div>
</div>
@endsection
