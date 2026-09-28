@extends('layouts.public')
@section('title', 'Terms & Conditions | Trinetraa Optician')
@section('meta_description', 'Terms and Conditions for Trinetraa Optician, Nashik.')
@section('content')
<div style="max-width:800px;margin:0 auto;padding:calc(64px + 3.5rem) 1.5rem 4rem">
    <h1 style="font-size:2rem;font-weight:800;margin-bottom:.5rem">Terms &amp; Conditions</h1>
    <p style="color:rgba(255,255,255,0.45);margin-bottom:2.5rem">Last updated: June 2025</p>

    @php($sections = [
        ['1. Acceptance of Terms', 'By accessing and using the Trinetraa Optician website, you accept and agree to be bound by these Terms and Conditions. If you do not agree, please do not use our website.'],
        ['2. Products and Pricing', 'All prices are in Indian Rupees (INR) and include applicable taxes unless stated otherwise. We reserve the right to change prices without prior notice. Product images are for illustrative purposes and actual products may vary slightly.'],
        ['3. Appointments', 'Appointment bookings are subject to availability. We require at least 2 hours notice for cancellations. Repeat no-shows may result in restricted booking privileges.'],
        ['4. Eye Prescriptions', 'Eye prescriptions provided are valid for one year from the date of testing. Trinetraa Optician is not liable for changes in vision power after the prescription date.'],
        ['5. Intellectual Property', 'All content on this website, including text, graphics, logos, and images, is the property of Trinetraa Optician and is protected by applicable intellectual property laws.'],
        ['6. Limitation of Liability', 'Trinetraa Optician shall not be liable for any indirect, incidental, or consequential damages arising from the use of our website or products beyond the value of the purchase made.'],
        ['7. Governing Law', 'These terms are governed by the laws of India and the state of Maharashtra. Any disputes shall be subject to the jurisdiction of courts in Nashik.'],
    ])
    @foreach($sections as $s)
        <div style="margin-bottom:2rem">
            <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:.75rem;color:#F8FAFC">{{ $s[0] }}</h2>
            <p style="color:#9FB1C7;line-height:1.8">{{ $s[1] }}</p>
        </div>
    @endforeach
</div>
@endsection
