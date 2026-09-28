@extends('layouts.public')
@section('title', 'Disclaimer | Trinetraa Optician')
@section('meta_description', 'Disclaimer for Trinetraa Optician, Nashik.')
@section('content')
<div style="max-width:800px;margin:0 auto;padding:calc(64px + 3.5rem) 1.5rem 4rem">
    <h1 style="font-size:2rem;font-weight:800;margin-bottom:.5rem">Disclaimer</h1>
    <p style="color:rgba(255,255,255,0.45);margin-bottom:2.5rem">Last updated: June 2025</p>

    @php($sections = [
        ['1. General Information', 'The information on this website is for general informational purposes only. While we strive to keep information accurate and up-to-date, we make no representations or warranties of any kind about the completeness, accuracy, or reliability of the information.'],
        ['2. Medical Disclaimer', 'The eye care information on this website is not a substitute for professional medical advice, diagnosis, or treatment. Always consult a qualified eye care professional for any concerns about your vision or eye health.'],
        ['3. Product Information', 'Product images, descriptions, and prices on this website are subject to change without notice. We do not guarantee that product colours will accurately reflect actual products due to variations in monitor displays.'],
        ['4. External Links', 'Our website may contain links to third-party websites. We are not responsible for the content, privacy practices, or accuracy of those sites.'],
        ['5. Liability', 'Trinetraa Optician is not liable for any direct or indirect losses arising from the use of this website or reliance on information provided herein.'],
    ])
    @foreach($sections as $s)
        <div style="margin-bottom:2rem">
            <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:.75rem;color:#F8FAFC">{{ $s[0] }}</h2>
            <p style="color:#9FB1C7;line-height:1.8">{{ $s[1] }}</p>
        </div>
    @endforeach
</div>
@endsection
