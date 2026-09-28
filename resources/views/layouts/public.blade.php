<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="theme-color" content="#1a3a5c">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Trinetraa Optician Nashik')</title>
    <meta name="description" content="@yield('meta_description', 'Premium eyewear and expert eye care in Nashik, Maharashtra. 1000+ frame styles, free eye tests.')">
    @php
        $_siteUrl  = rtrim($site['siteUrl'] ?? 'https://trinetraaoptician.com', '/');
        $_ogTitle  = $__env->yieldContent('title', 'Trinetraa Optician Nashik');
        $_ogDesc   = $__env->yieldContent('meta_description', 'Premium eyewear and expert eye care in Nashik, Maharashtra. 1000+ frame styles, free eye tests.');
        $_ogImage  = $__env->yieldContent('og_image', $_siteUrl . '/icons/icon-512.png');
        $_ogType   = $__env->yieldContent('og_type', 'website');
        $_canonical = $__env->yieldContent('canonical', $_siteUrl . '/' . ltrim(request()->path(), '/'));
        $_noIndex  = $__env->yieldContent('no_index', '');
    @endphp
    @if($_noIndex)<meta name="robots" content="noindex,nofollow">@endif
    <link rel="canonical" href="{{ $_canonical }}">
    <meta property="og:type"        content="{{ $_ogType }}">
    <meta property="og:title"       content="{{ $_ogTitle }}">
    <meta property="og:description" content="{{ $_ogDesc }}">
    <meta property="og:url"         content="{{ $_canonical }}">
    <meta property="og:image"       content="{{ $_ogImage }}">
    <meta property="og:site_name"   content="Trinetraa Optician">
    <meta property="og:locale"      content="en_IN">
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="{{ $_ogTitle }}">
    <meta name="twitter:description" content="{{ $_ogDesc }}">
    <meta name="twitter:image"       content="{{ $_ogImage }}">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" type="image/svg+xml" href="/icons/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    @php
        // Cache-bust on file modification so theme updates reach browsers immediately.
        $_assetV = fn ($p) => $p . '?v=' . @filemtime(public_path(ltrim($p, '/')));
    @endphp
    <link rel="preload" href="{{ $_assetV('/css/globals.css') }}" as="style">
    <link rel="preload" href="{{ $_assetV('/css/public.css') }}" as="style">
    <link rel="preload" href="{{ $_assetV('/js/app.js') }}" as="script">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ $_assetV('/css/globals.css') }}">
    <link rel="stylesheet" href="{{ $_assetV('/css/public.css') }}">
    @php
        $at = '@';
        $jsonLd = json_encode([
            $at.'context' => 'https://schema.org',
            $at.'type'    => 'Optician',
            'name'        => 'Trinetraa Optician',
            'url'         => 'https://trinetraaoptician.com',
            'logo'        => 'https://trinetraaoptician.com/icons/icon-512.png',
            'image'       => 'https://trinetraaoptician.com/icons/icon-512.png',
            'description' => 'Premium eyewear and expert eye care in Nashik, Maharashtra. Free eye tests, 1000+ frames, branded sunglasses and eyeglasses.',
            'address' => [
                $at.'type'     => 'PostalAddress',
                'streetAddress'    => $site['contactAddress'],
                'addressLocality'  => 'Nashik',
                'addressRegion'    => 'Maharashtra',
                'postalCode'       => '422101',
                'addressCountry'   => 'IN',
            ],
            'geo' => [$at.'type' => 'GeoCoordinates', 'latitude' => 19.9975, 'longitude' => 73.7898],
            'telephone'           => preg_replace('/\s+/', '', $site['contactPhone']),
            'email'               => $site['contactEmail'],
            'priceRange'          => '₹₹',
            'currenciesAccepted'  => 'INR',
            'paymentAccepted'     => 'Cash, Credit Card, Debit Card, UPI',
            'openingHoursSpecification' => [
                [$at.'type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'], 'opens' => '10:00', 'closes' => '20:00'],
                [$at.'type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Sunday'], 'opens' => '11:00', 'closes' => '18:00'],
            ],
            'sameAs' => array_filter([$site['facebookUrl'], $site['instagramUrl']]),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $jsonLd !!}</script>
    @stack('structured_data')
    @stack('head')
</head>
<body>
@php
    $navLinks = [
        ['href' => '/', 'label' => 'Home', 'exact' => true],
        ['href' => '/eyewears', 'label' => 'Eyewears'],
        ['href' => '/brands', 'label' => 'Brands'],
        ['label' => 'Tools', 'dropdown' => [
            ['href' => '/try-on', 'label' => 'Virtual Try-On', 'desc' => 'Try frames on your face'],
            ['href' => '/recommend', 'label' => 'Find My Frames', 'desc' => 'Get personalised picks'],
            ['href' => '/vision-test', 'label' => 'Vision Test', 'desc' => 'Quick self-assessment'],
            ['href' => '/services', 'label' => 'Eye Test', 'desc' => 'Book a professional check'],
        ]],
        ['href' => '/offers', 'label' => 'Offers'],
        ['href' => '/blog', 'label' => 'Blog'],
        ['href' => '/about', 'label' => 'About'],
        ['href' => '/contact', 'label' => 'Contact'],
    ];
    $path = '/' . ltrim(request()->path() === '/' ? '' : request()->path(), '/');
    $toolsActive = collect(['/try-on','/recommend','/vision-test','/services'])->contains(fn($p) => str_starts_with($path, $p));
@endphp
<div class="pp-root" id="ppRoot">
    <nav class="pp-nav" id="ppNav">
        <div class="pp-nav__inner">
            <a href="/" class="pp-nav__logo">
                <span class="pp-nav__logo-icon"><img src="/icons/favicon.svg" width="36" height="36" alt=""></span>
                <span>Trinetraa Optician</span>
            </a>

            <ul class="pp-nav__links">
                @foreach($navLinks as $link)
                    @if(isset($link['dropdown']))
                        <li class="pp-nav__dropdown-wrap" id="toolsDropdownWrap">
                            <button class="pp-nav__dropdown-trigger{{ $toolsActive ? ' active' : '' }}" type="button">
                                {{ $link['label'] }}
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pp-nav__chevron"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="pp-nav__dropdown">
                                @foreach($link['dropdown'] as $item)
                                    <a href="{{ $item['href'] }}" class="pp-nav__dropdown-item">
                                        <span class="pp-nav__dropdown-label">{{ $item['label'] }}</span>
                                        <span class="pp-nav__dropdown-desc">{{ $item['desc'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </li>
                    @else
                        @php
                            $active = $path === $link['href'] || (empty($link['exact']) && $link['href'] !== '/' && str_starts_with($path, $link['href'].'/'));
                        @endphp
                        <li><a href="{{ $link['href'] }}" class="{{ $active ? 'active' : '' }}">{{ $link['label'] }}</a></li>
                    @endif
                @endforeach
            </ul>

            <div class="pp-nav__actions">
                <a href="/search" title="Search" aria-label="Search" class="pp-nav__search-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10.5" cy="10.5" r="3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M13.5 13.5L17 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M10.5 4a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-dasharray="2.5 2"/><path d="M10.5 1.5a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" stroke-dasharray="2 2.5" stroke-opacity="0.45"/></svg>
                </a>
                @if($authCustomer)
                    <a href="/account" class="pp-nav__account-btn">
                        <span class="pp-nav__account-avatar">{{ strtoupper(substr($authCustomer['name'] ?? 'U', 0, 1)) }}</span>
                        <span class="pp-nav__account-name">{{ explode(' ', $authCustomer['name'] ?? '')[0] }}</span>
                    </a>
                @else
                    <a href="/account/login" class="pp-nav__login-btn pp-nav__login-btn--outline">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:.3rem"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Sign In
                    </a>
                @endif
                <a href="/cart" class="pp-nav__cart-btn pp-nav__cart-btn--outline">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    Cart
                    <span class="pp-nav__cart-badge" id="cartBadge" style="display:none">0</span>
                </a>
                <a href="/appointment" class="pp-nav__cta">
                    Book Eye Test
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <button class="pp-nav__hamburger" id="hamburger" aria-label="Toggle menu">
                    <svg width="34" height="34" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                        <circle class="pp-hamburger-ring" cx="16" cy="16" r="15.4"/>
                        <circle class="pp-hamburger-fill" cx="16" cy="16" r="15.4"/>
                        <line class="pp-hamburger-bar" x1="9.5" y1="12" x2="22.5" y2="12"/>
                        <line class="pp-hamburger-bar" x1="9.5" y1="16" x2="22.5" y2="16"/>
                        <line class="pp-hamburger-bar" x1="9.5" y1="20" x2="22.5" y2="20"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="pp-nav__mobile" id="ppMobile">
            @foreach($navLinks as $link)
                @if(isset($link['dropdown']))
                    <button class="pp-nav__mobile-group-btn" id="mobileToolsBtn" type="button">{{ $link['label'] }}
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:auto"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div id="mobileTools" style="display:none">
                        @foreach($link['dropdown'] as $item)
                            <a href="{{ $item['href'] }}" style="padding-left:1.5rem;font-size:.875rem;opacity:.85">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                @else
                    <a href="{{ $link['href'] }}" class="{{ $path === $link['href'] ? 'active' : '' }}">{{ $link['label'] }}</a>
                @endif
            @endforeach
            <a href="/appointment">Book Eye Test</a>
            <a href="/cart">Cart</a>
            @if($authCustomer)
                <a href="/account">My Account ({{ explode(' ', $authCustomer['name'] ?? '')[0] }})</a>
                <button onclick="window.TrinetraaAuth.logout()" style="background:none;border:none;color:inherit;cursor:pointer;padding:.75rem 1.5rem;text-align:left;font-size:1rem">Sign Out</button>
            @else
                <a href="/account/login">Sign In / Register</a>
            @endif
        </div>
    </nav>

    {{-- Announcement bar — managed from Admin → Settings --}}
    @if(!empty($site['announcementEnabled']) && !empty($site['announcementText']))
    <div id="ppAnnounce" data-announce-key="{{ md5($site['announcementText']) }}"
         style="position:fixed;top:calc(60px + 1.2rem);left:50%;transform:translateX(-50%);z-index:999;display:none;align-items:center;gap:.75rem;max-width:min(92vw,720px);background:rgba(15,118,110,0.55);-webkit-backdrop-filter:blur(18px) saturate(1.4);backdrop-filter:blur(18px) saturate(1.4);border:1px solid rgba(94,234,212,0.35);border-radius:999px;padding:.5rem 1rem .5rem 1.25rem;box-shadow:0 12px 40px rgba(0,0,0,0.35)">
        <span style="font-size:.85rem;color:#fff;font-weight:500;line-height:1.4">📢 {{ $site['announcementText'] }}</span>
        <button onclick="window.ppDismissAnnounce()" aria-label="Dismiss announcement"
                style="background:rgba(255,255,255,0.12);border:none;color:#fff;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;font-size:.8rem">✕</button>
    </div>
    <script>
        (function () {
            var bar = document.getElementById('ppAnnounce');
            var key = 'pp-announce-dismissed-' + bar.getAttribute('data-announce-key');
            if (!localStorage.getItem(key)) bar.style.display = 'flex';
            window.ppDismissAnnounce = function () { bar.style.display = 'none'; localStorage.setItem(key, '1'); };
        })();
    </script>
    @endif

    @yield('content')

    {{-- Compare bar --}}
    <div id="compareBar" style="position:fixed;bottom:0;left:0;right:0;z-index:998;background:rgba(13,20,35,0.85);-webkit-backdrop-filter:blur(20px) saturate(1.4);backdrop-filter:blur(20px) saturate(1.4);border-top:1px solid rgba(255,255,255,0.18);color:#fff;box-shadow:0 -12px 40px rgba(0,0,0,0.4);display:none">
        <div class="pp-container" style="display:flex;align-items:center;gap:1rem;padding:.75rem 1rem;flex-wrap:wrap">
            <div id="compareItems" style="display:flex;gap:.75rem;flex:1;flex-wrap:wrap;align-items:center"></div>
            <div style="display:flex;gap:.5rem;flex-shrink:0">
                <button onclick="window.TrinetraaCompare.clear()" style="background:transparent;border:1px solid rgba(255,255,255,0.3);color:#fff;border-radius:8px;padding:.45rem .9rem;cursor:pointer;font-size:.8rem;font-family:inherit">Clear</button>
                <a href="/compare" style="background:#14B8A6;color:#06201d;border-radius:8px;padding:.45rem 1rem;font-weight:700;font-size:.85rem;text-decoration:none">Compare Now →</a>
            </div>
        </div>
    </div>

    <button id="backToTop" class="pp-back-to-top" aria-label="Back to top" style="display:none"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg></button>

    <a id="waFab" href="https://wa.me/{{ $site['whatsappNumber'] }}?text=Hello%20Trinetraa%20Optician%2C%20I%20have%20an%20enquiry." target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp" style="position:fixed;bottom:5rem;right:1.5rem;z-index:999;background:#25D366;border-radius:50%;width:56px;height:56px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(0,0,0,0.25);color:#fff;text-decoration:none">
        <svg viewBox="0 0 32 32" fill="currentColor" width="28" height="28" aria-hidden="true"><path d="M16 3C9.37 3 4 8.37 4 15c0 2.39.67 4.62 1.83 6.52L4 29l7.7-1.8A12.93 12.93 0 0016 28c6.63 0 12-5.37 12-12S22.63 3 16 3zm6.27 17.1c-.27.76-1.57 1.46-2.16 1.55-.56.09-1.27.13-2.05-.13a18.9 18.9 0 01-1.86-.7c-3.25-1.4-5.37-4.66-5.53-4.87-.16-.22-1.28-1.7-1.28-3.24s.81-2.3 1.1-2.61c.29-.32.63-.4.84-.4l.61.01c.19 0 .46-.07.72.55l.98 2.39c.09.23.05.5-.09.71l-.39.56-.38.43c-.13.15-.27.3-.12.59.15.28.68 1.13 1.46 1.83.99.89 1.83 1.16 2.1 1.29.27.13.43.11.59-.07l.84-.99c.15-.2.31-.15.52-.08l2.5.98c.24.09.39.14.45.22.06.09.06.51-.2 1.27z"/></svg>
    </a>

    {{-- Newsletter --}}
    <div class="pp-newsletter" style="background:linear-gradient(135deg,rgba(15,118,110,0.35) 0%,rgba(11,18,32,0.2) 60%),rgba(255,255,255,0.04);border-top:1px solid rgba(255,255,255,0.14);color:#fff;padding:3.5rem 0">
        <div class="pp-container" style="text-align:center">
            <div style="font-family:var(--pp-font-heading);font-size:1.9rem;font-weight:600;margin-bottom:.5rem">Stay Updated with Offers &amp; Eye Care Tips</div>
            <p style="opacity:.8;margin-bottom:1.5rem">Subscribe to our newsletter for exclusive deals, new arrivals, and expert eye care advice.</p>
            <div id="newsletterSuccess" style="background:#25a244;border-radius:.5rem;padding:.75rem 1.5rem;display:none;font-weight:600">✓ Subscribed! Thank you.</div>
            <form id="newsletterForm" style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
                <input type="email" name="email" required placeholder="Your email address" style="padding:.75rem 1.25rem;border-radius:.5rem;border:none;font-size:1rem;width:280px;max-width:100%">
                <button type="submit" class="pp-btn-primary pp-btn-primary--gold" style="padding:.75rem 1.5rem">Subscribe →</button>
            </form>
            <div id="newsletterError" style="margin-top:.5rem;color:#ff9999;font-size:.875rem;display:none">Something went wrong. Please try again.</div>
        </div>
    </div>

    <footer class="pp-footer">
        <div class="pp-container">
            <div class="pp-footer__grid">
                <div>
                    <div class="pp-footer__brand"><div class="pp-footer__brand-orb"></div> Trinetraa Optician</div>
                    <p class="pp-footer__tagline">Premium eyewear and expert eye care in the heart of Nashik. See the world more clearly.</p>
                    <div class="pp-footer__social">
                        <a href="{{ $site['facebookUrl'] }}" class="pp-footer__social-link" aria-label="Facebook" target="_blank" rel="noopener noreferrer"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
                        <a href="{{ $site['instagramUrl'] }}" class="pp-footer__social-link" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
                        <a href="https://wa.me/{{ $site['whatsappNumber'] }}" class="pp-footer__social-link" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer"><svg width="14" height="14" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9.37 3 4 8.37 4 15c0 2.39.67 4.62 1.83 6.52L4 29l7.7-1.8A12.93 12.93 0 0016 28c6.63 0 12-5.37 12-12S22.63 3 16 3z"/></svg></a>
                    </div>
                </div>
                <div>
                    <div class="pp-footer__col-title">Quick Links</div>
                    <ul class="pp-footer__links">
                        <li><a href="/about">About Us</a></li>
                        <li><a href="/services">Eye Test Services</a></li>
                        <li><a href="/brands">Brands</a></li>
                        <li><a href="/offers">Offers</a></li>
                        <li><a href="/blog">Blog</a></li>
                        <li><a href="/gallery">Gallery</a></li>
                        <li><a href="/reviews">Reviews</a></li>
                        <li><a href="/compare">Compare Products</a></li>
                    </ul>
                </div>
                <div>
                    <div class="pp-footer__col-title">Tools</div>
                    <ul class="pp-footer__links">
                        <li><a href="/try-on">Virtual Try-On</a></li>
                        <li><a href="/recommend">Find My Frames</a></li>
                        <li><a href="/vision-test">Vision Self-Test</a></li>
                        <li><a href="/services">Book Eye Test</a></li>
                        <li><a href="/eyewears">All Eyewears</a></li>
                        <li><a href="/eyewears?category_slug=sunglasses">Sunglasses</a></li>
                        <li><a href="/eyewears?category_slug=eyeglasses">Eyeglasses</a></li>
                        <li><a href="/eyewears?category_slug=contact-lenses">Contact Lenses</a></li>
                    </ul>
                </div>
                <div>
                    <div class="pp-footer__col-title">Contact</div>
                    <div class="pp-footer__contact-item"><span class="pp-footer__contact-icon">📍</span><a href="{{ $site['googleMapsUrl'] }}" target="_blank" rel="noopener noreferrer" style="color:inherit">{{ $site['contactAddress'] }}</a></div>
                    <div class="pp-footer__contact-item"><span class="pp-footer__contact-icon">📞</span><a href="tel:{{ $site['contactPhone'] }}" style="color:inherit">{{ $site['contactPhone'] }}</a></div>
                    <div class="pp-footer__contact-item"><span class="pp-footer__contact-icon">✉️</span><a href="mailto:{{ $site['contactEmail'] }}" style="color:inherit">{{ $site['contactEmail'] }}</a></div>
                    <div class="pp-footer__contact-item"><span class="pp-footer__contact-icon">🕐</span><span>Mon–Sat: 10 AM – 8 PM</span></div>
                    <div style="margin-top:1rem">
                        <div style="font-size:.75rem;color:#888;margin-bottom:.5rem;font-weight:600">📍 SCAN TO FIND US</div>
                        <a href="{{ $site['googleMapsUrl'] }}" target="_blank" rel="noopener noreferrer" title="Open in Google Maps">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ urlencode($site['googleMapsUrl']) }}&color=ffffff&bgcolor=1a3a5c&qzone=1&format=png"
                                 alt="QR Code — Trinetraa Optician Location" width="90" height="90"
                                 loading="lazy" style="border-radius:8px;display:block">
                        </a>
                    </div>
                    <a href="https://g.page/r/trinetraa-optician/review" target="_blank" rel="noopener noreferrer"
                       style="display:inline-flex;align-items:center;gap:.4rem;margin-top:.75rem;background:#4285f4;color:#fff;border-radius:6px;padding:.35rem .75rem;font-size:.78rem;font-weight:700;text-decoration:none">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Review on Google
                    </a>
                </div>
            </div>
        </div>
        <div class="pp-footer__bottom">
            <div class="pp-container" style="display:flex;flex-wrap:wrap;gap:1rem;justify-content:space-between;align-items:center">
                <span>&copy; {{ date('Y') }} Trinetraa Optician, Nashik. All rights reserved.</span>
                <div style="display:flex;gap:1.25rem;flex-wrap:wrap;font-size:.8rem">
                    <a href="/privacy-policy" style="color:#aaa">Privacy Policy</a>
                    <a href="/terms" style="color:#aaa">Terms &amp; Conditions</a>
                    <a href="/refund-policy" style="color:#aaa">Refund Policy</a>
                    <a href="/shipping-policy" style="color:#aaa">Shipping</a>
                    <a href="/cookie-policy" style="color:#aaa">Cookies</a>
                    <a href="/disclaimer" style="color:#aaa">Disclaimer</a>
                </div>
            </div>
        </div>
    </footer>
</div>

<script>
window.__SITE = {
    whatsapp: "{{ $site['whatsappNumber'] }}",
    googleMaps: "{{ $site['googleMapsUrl'] }}",
    email: "{{ $site['contactEmail'] }}"
};
</script>
<script src="{{ $_assetV('/js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
