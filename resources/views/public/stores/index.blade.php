@extends('layouts.public')
@section('title', 'Our Stores — Nashik, Mumbai & Pune | Trinetraa Optician')
@section('meta_description', 'Find a Trinetraa Optician store near you: Nashik, Mumbai and Pune. Eye tests, prescription glasses, sunglasses and contact lenses.')
@section('canonical', rtrim($site['siteUrl'] ?? 'https://trinetraaoptician.com', '/') . '/stores')
@push('structured_data')
    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => 'Trinetraa Optician stores',
        'itemListElement' => $branches->values()->map(fn ($b, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => 'Trinetraa Optician ' . $b->name, 'url' => 'https://trinetraaoptician.com/stores/' . strtolower($b->code)])->all(),
    ], JSON_UNESCAPED_SLASHES) !!}</script>
@endpush
@section('content')
<style>
    .sx-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem; }
    .sx-card { display: block; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.14); border-radius: 18px; padding: 1.5rem; text-decoration: none; transition: transform .25s, border-color .25s; }
    .sx-card:hover { transform: translateY(-4px); border-color: rgba(45,212,191,.5); }
    .sx-card h2 { font-family: var(--pp-font-heading); color: #F8FAFC; font-size: 1.3rem; margin: 0 0 .5rem; }
    .sx-card p { color: #9FB1C7; margin: 0 0 .35rem; font-size: .9rem; line-height: 1.6; }
    .sx-card span { color: #2DD4BF; font-weight: 600; font-size: .85rem; }
</style>
<div style="padding:calc(64px + 3rem) 0 4rem">
    <div class="pp-container">
        <h1 style="font-family:var(--pp-font-heading);font-size:clamp(1.9rem,4vw,2.8rem);font-weight:800;color:#F8FAFC;letter-spacing:-.03em;margin:0 0 .75rem">Our Stores</h1>
        <p style="color:#9FB1C7;max-width:620px;line-height:1.7;margin-bottom:2rem">Visit us in Nashik, Mumbai or Pune for a professional eye test and a wide range of frames, sunglasses and lenses.</p>
        <div class="sx-grid">
            @foreach($branches as $b)
                <a class="sx-card" href="/stores/{{ strtolower($b->code) }}">
                    <h2>{{ $b->name }}</h2>
                    <p>{{ $b->address ?: (strtolower($b->code) === 'nsk' ? $site['contactAddress'] : 'Details coming soon') }}</p>
                    @if($b->phone)<p>📞 {{ $b->phone }}</p>@endif
                    <span>View store →</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
