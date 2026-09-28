@extends('layouts.public')
@section('title', 'Shipping Policy | Trinetraa Optician')
@section('meta_description', 'Shipping Policy for Trinetraa Optician, Nashik.')
@section('content')
<div style="max-width:800px;margin:0 auto;padding:calc(64px + 3.5rem) 1.5rem 4rem">
    <h1 style="font-size:2rem;font-weight:800;margin-bottom:.5rem">Shipping Policy</h1>
    <p style="color:rgba(255,255,255,0.45);margin-bottom:2.5rem">Last updated: June 2025</p>

    @php($sections = [
        ['1. Delivery Areas', 'We currently offer home delivery within Nashik city limits. For orders outside Nashik, please contact us at info@trinetraa.com to discuss shipping options.'],
        ['2. Delivery Timeframes', 'Standard orders: 3–5 business days. Custom prescription lenses: 5–10 business days depending on lens type. We will notify you via SMS/email once your order is ready.'],
        ['3. Shipping Charges', 'Free delivery within Nashik on orders above ₹1,000. A delivery charge of ₹50 applies on orders below ₹1,000. Contact us for outstation delivery rates.'],
        ['4. Order Tracking', 'Once your order is dispatched, you will receive an SMS with the estimated delivery time. You can also call us at 0253-123456 to track your order status.'],
        ['5. In-Store Pickup', 'You can also choose to pick up your order from our store at no extra charge. We will send you an SMS when your order is ready for collection.'],
        ['6. Damaged Deliveries', 'If your order arrives damaged, please photograph the packaging and product immediately and contact us within 24 hours. We will arrange a replacement or refund.'],
    ])
    @foreach($sections as $s)
        <div style="margin-bottom:2rem">
            <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:.75rem;color:#F8FAFC">{{ $s[0] }}</h2>
            <p style="color:#9FB1C7;line-height:1.8">{{ $s[1] }}</p>
        </div>
    @endforeach
</div>
@endsection
