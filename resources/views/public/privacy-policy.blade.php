@extends('layouts.public')
@section('title', 'Privacy Policy | Trinetraa Optician')
@section('meta_description', 'Privacy Policy for Trinetraa Optician, Nashik.')
@section('content')
<div style="max-width:800px;margin:0 auto;padding:calc(64px + 3.5rem) 1.5rem 4rem">
    <h1 style="font-size:2rem;font-weight:800;margin-bottom:.5rem">Privacy Policy</h1>
    <p style="color:rgba(255,255,255,0.45);margin-bottom:2.5rem">Last updated: June 2025</p>

    @php($sections = [
        ['1. Information We Collect', 'We collect information you provide directly to us, such as your name, email address, phone number, and eye prescription details when you book appointments, register an account, or contact us. We also collect usage data such as pages visited and device information.'],
        ['2. How We Use Your Information', 'We use the information we collect to: process appointments and orders, communicate with you about our services, send promotional offers (with your consent), improve our website and services, and comply with legal obligations.'],
        ['3. Information Sharing', 'We do not sell or rent your personal information to third parties. We may share your information with service providers who assist us in operating our website, subject to confidentiality agreements.'],
        ['4. Data Security', 'We implement appropriate technical and organisational measures to protect your personal information against unauthorised access, alteration, disclosure, or destruction.'],
        ['5. Cookies', 'We use cookies to enhance your browsing experience and analyse website traffic. You can control cookie settings through your browser. Essential cookies required for website functionality cannot be disabled.'],
        ['6. Your Rights', 'You have the right to access, correct, or delete your personal data. To exercise these rights, please contact us at info@trinetraa.com or visit our store.'],
        ['7. Contact Us', 'For any questions about this Privacy Policy, contact us at: Trinetraa Optician, Nashik, Maharashtra. Email: info@trinetraa.com | Phone: 0253-123456'],
    ])
    @foreach($sections as $s)
        <div style="margin-bottom:2rem">
            <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:.75rem;color:#F8FAFC">{{ $s[0] }}</h2>
            <p style="color:#9FB1C7;line-height:1.8">{{ $s[1] }}</p>
        </div>
    @endforeach
</div>
@endsection
