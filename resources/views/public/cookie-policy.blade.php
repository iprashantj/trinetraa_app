@extends('layouts.public')
@section('title', 'Cookie Policy | Trinetraa Optician')
@section('meta_description', 'Cookie Policy for Trinetraa Optician, Nashik.')
@section('content')
<div style="max-width:800px;margin:0 auto;padding:calc(64px + 3.5rem) 1.5rem 4rem">
    <h1 style="font-size:2rem;font-weight:800;margin-bottom:.5rem">Cookie Policy</h1>
    <p style="color:rgba(255,255,255,0.45);margin-bottom:2.5rem">Last updated: June 2025</p>

    @php($sections = [
        ['1. What Are Cookies?', 'Cookies are small text files stored on your device when you visit our website. They help us remember your preferences and understand how you use our site.'],
        ['2. Cookies We Use', 'Essential cookies (required for login, cart, and site functionality), Analytics cookies (to understand website traffic — we use Google Analytics), Preference cookies (to remember your settings).'],
        ['3. Managing Cookies', 'You can control cookies through your browser settings. Disabling essential cookies may affect website functionality such as the shopping cart and account login.'],
        ['4. Third-Party Cookies', 'We may use third-party services such as Google Analytics that place their own cookies. These are governed by the respective third party\'s privacy policy.'],
        ['5. Contact', 'For questions about our cookie use, contact info@trinetraa.com.'],
    ])
    @foreach($sections as $s)
        <div style="margin-bottom:2rem">
            <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:.75rem;color:#F8FAFC">{{ $s[0] }}</h2>
            <p style="color:#9FB1C7;line-height:1.8">{{ $s[1] }}</p>
        </div>
    @endforeach
</div>
@endsection
