{{-- Shared letterhead for one invoice slot. Expects: $bill, $title. Branch details (when set) replace the site-wide contact block. --}}
@php
    $br = !empty($bill->branch_id) ? \App\Models\Branch::find($bill->branch_id) : null;
    $addr = ($br && $br->address) ? $br->address : $site['contactAddress'];
    $tel = ($br && $br->phone) ? $br->phone : $site['contactPhone'];
    $mail = ($br && $br->email) ? $br->email : $site['contactEmail'];
    $gst = ($br && $br->gstin) ? $br->gstin : ($site['gstin'] ?? '');
@endphp
<table class="ph-header">
    <tr>
        <td class="ph-logo"><img src="{{ public_path(file_exists(public_path('logo-pdf.png')) ? 'logo-pdf.png' : 'logo.png') }}" alt=""></td>
        <td class="ph-storeinfo">
            <div class="ph-storename">{{ $site['siteName'] }}</div>
            <div class="ph-storeaddr">{{ $addr }}</div>
            <div class="ph-storeaddr">Phone: {{ $tel }} &nbsp;|&nbsp; Email: {{ $mail }}</div>
            @if(!empty($gst))
            <div class="ph-storeaddr">GSTIN: {{ $gst }}</div>
            @endif
        </td>
        <td class="ph-title">
            <div class="ph-invoicelabel">{{ $title }}</div>
            <div class="ph-billmeta"><strong>Invoice #:</strong> {{ $bill->invoice_no ?: $bill->bill_number }}</div>
            <div class="ph-billmeta"><strong>Date:</strong> {{ optional($bill->bill_date)->format('d/m/Y') }}</div>
        </td>
    </tr>
</table>
<table class="ph-customer">
    <tr>
        <td><strong>Customer:</strong> {{ $bill->customer_name ?: (optional($bill->customer ?? null)->name ?? '—') }}</td>
        <td><strong>Contact:</strong> {{ $bill->customer_contact ?: '—' }}@if(!empty($bill->customer_gstin)) &nbsp;|&nbsp; <strong>GSTIN:</strong> {{ $bill->customer_gstin }}@endif</td>
    </tr>
</table>
