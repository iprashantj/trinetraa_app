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
            @include('admin.pdf._invoice-header', ['bill' => $bill, 'title' => 'TAX INVOICE'])
            <table class="item-table">
                <thead>
                    <tr><th>Brand</th><th>Model</th><th class="text-end">Price</th><th class="text-end">Discount</th><th class="text-end">Qty</th><th class="text-end">Subtotal</th></tr>
                </thead>
                <tbody>
                    @foreach($bill->items as $item)
                    <tr>
                        <td>{{ $item->brand_name }}</td>
                        <td>{{ $item->model_number ?: '—' }}</td>
                        <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                        <td class="text-end">₹{{ number_format($item->discount, 2) }}</td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">₹{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <table class="totals-table">
                <tr><td>Discount</td><td class="text-end">₹{{ number_format($bill->discount_amount, 2) }}</td></tr>
                <tr><td>Subtotal</td><td class="text-end">₹{{ number_format($bill->subtotal, 2) }}</td></tr>
                <tr><td>GST ({{ number_format($bill->gst_rate, 2) }}%)</td><td class="text-end">₹{{ number_format($bill->tax, 2) }}</td></tr>
                <tr class="grand"><td>Total</td><td class="text-end">₹{{ number_format($bill->total, 2) }}</td></tr>
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
