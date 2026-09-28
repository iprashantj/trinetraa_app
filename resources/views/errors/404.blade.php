@extends('layouts.public')
@section('title', 'Page Not Found | Trinetraa Optician')
@section('meta_description', "The page you're looking for doesn't exist or may have been moved.")
@section('no_index', '1')
@section('content')
<section class="pp-section" style="min-height:60vh;display:flex;align-items:center">
    <div class="pp-container" style="text-align:center;padding:4rem 0">
        <div style="font-size:4rem;margin-bottom:1rem">🕶️</div>
        <h1 style="font-family:var(--pp-font-heading);font-size:clamp(1.75rem, 3vw, 2.4rem);font-weight:800;color:var(--pp-gray-900);margin-bottom:.75rem">Page Not Found</h1>
        <p style="color:var(--pp-gray-500);max-width:420px;margin:0 auto 2rem;line-height:1.7">
            The page you're looking for doesn't exist or may have been moved. Let's get you back on track.
        </p>
        <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
            <a href="/" class="pp-btn-primary">Back to Home</a>
            <a href="/eyewears" class="pp-btn-outline">Browse Eyewears</a>
        </div>
    </div>
</section>
@endsection
