<!doctype html>
<html>
<head>
<meta charset="utf-8">
@include('admin.pdf._invoice-style')
</head>
<body>
@foreach($bills->chunk(2) as $pair)
    @foreach($pair as $i => $bill)
        <div class="invoice-slot">
            @include('admin.pdf._invoice-header', ['bill' => $bill, 'title' => 'EYE CHECKUP BILL'])
            <table class="item-table">
                <thead>
                    <tr><th>Eye</th><th class="text-end">Power</th></tr>
                </thead>
                <tbody>
                    <tr><td>Left (L)</td><td class="text-end">{{ $bill->left_eye ?: '—' }}</td></tr>
                    <tr><td>Right (R)</td><td class="text-end">{{ $bill->right_eye ?: '—' }}</td></tr>
                    <tr><td>Addition</td><td class="text-end">{{ $bill->addition ?: '—' }}</td></tr>
                </tbody>
            </table>
            <table class="item-table">
                <thead>
                    <tr><th>Charge</th><th class="text-end">Amount</th></tr>
                </thead>
                <tbody>
                    <tr><td>Frame Amount</td><td class="text-end">₹{{ number_format($bill->frame_amount, 2) }}</td></tr>
                    <tr><td>Glass Amount</td><td class="text-end">₹{{ number_format($bill->glass_amount, 2) }}</td></tr>
                    <tr><td>Other Charges</td><td class="text-end">₹{{ number_format($bill->other_amount, 2) }}</td></tr>
                </tbody>
            </table>
            <table class="totals-table">
                <tr><td>Subtotal</td><td class="text-end">₹{{ number_format($bill->subtotal, 2) }}</td></tr>
                @if($bill->with_gst)
                <tr><td>GST ({{ number_format($bill->gst_rate, 2) }}%)</td><td class="text-end">₹{{ number_format($bill->tax, 2) }}</td></tr>
                @endif
                <tr class="grand"><td>Total</td><td class="text-end">₹{{ number_format($bill->total, 2) }}</td></tr>
                <tr><td>Advance Paid</td><td class="text-end">₹{{ number_format($bill->advance_amount, 2) }}</td></tr>
                <tr class="grand"><td>Balance Due</td><td class="text-end">₹{{ number_format($bill->balance_due, 2) }}</td></tr>
            </table>
            <div class="clearfix"></div>
            @if($bill->notes)<div class="notes">Notes: {{ $bill->notes }}</div>@endif
        </div>
        @if($i === 0 && $pair->count() > 1)
            <div class="cut-line">- - - - - - - - - - - - - - - CUT HERE - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -</div>
        @endif
    @endforeach
    @if(!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif
@endforeach
</body>
</html>
