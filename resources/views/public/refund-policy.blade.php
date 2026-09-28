@extends('layouts.public')
@section('title', 'Refund Policy | Trinetraa Optician')
@section('meta_description', 'Refund and Return Policy for Trinetraa Optician, Nashik.')
@section('content')
<div style="max-width:800px;margin:0 auto;padding:calc(64px + 3.5rem) 1.5rem 4rem">
    <h1 style="font-size:2rem;font-weight:800;margin-bottom:.5rem">Refund &amp; Return Policy</h1>
    <p style="color:rgba(255,255,255,0.45);margin-bottom:2.5rem">Last updated: June 2025</p>

    @php($sections = [
        ['1. Frame Returns', 'Frames can be returned within 7 days of purchase if they are unused, undamaged, and in original packaging. Custom or special-order frames are non-returnable.'],
        ['2. Lens Returns', 'Prescription lenses are non-returnable once manufactured, as they are custom-made to your specific prescription. However, if there is a manufacturing defect or incorrect prescription, we will replace the lenses free of charge.'],
        ['3. Contact Lenses', 'Unopened contact lens boxes can be returned within 14 days. Opened boxes are non-returnable for hygiene reasons.'],
        ['4. Defective Products', 'If you receive a defective product, please contact us within 48 hours of receipt with photos. We will arrange a replacement or full refund at no extra cost.'],
        ['5. Refund Processing', 'Approved refunds will be processed within 7 business days. Refunds will be issued to the original payment method. Cash purchases will be refunded in cash at the store.'],
        ['6. How to Initiate a Return', 'Visit our store with the product and original receipt, or contact us at info@trinetraa.com / 0253-123456. Our team will guide you through the return process.'],
    ])
    @foreach($sections as $s)
        <div style="margin-bottom:2rem">
            <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:.75rem;color:#F8FAFC">{{ $s[0] }}</h2>
            <p style="color:#9FB1C7;line-height:1.8">{{ $s[1] }}</p>
        </div>
    @endforeach
</div>
@endsection
