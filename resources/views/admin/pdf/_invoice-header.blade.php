{{-- Shared letterhead for one invoice slot. Expects: $bill, $title, $billNoPrefix --}}
<table class="ph-header">
    <tr>
        <td class="ph-logo"><img src="{{ public_path('logo.png') }}" alt=""></td>
        <td class="ph-storeinfo">
            <div class="ph-storename">{{ $site['siteName'] }}</div>
            <div class="ph-storeaddr">{{ $site['contactAddress'] }}</div>
            <div class="ph-storeaddr">Phone: {{ $site['contactPhone'] }} &nbsp;|&nbsp; Email: {{ $site['contactEmail'] }}</div>
            @if(!empty($site['gstin']))
            <div class="ph-storeaddr">GSTIN: {{ $site['gstin'] }}</div>
            @endif
        </td>
        <td class="ph-title">
            <div class="ph-invoicelabel">{{ $title }}</div>
            <div class="ph-billmeta"><strong>Bill #:</strong> {{ $bill->bill_number }}</div>
            <div class="ph-billmeta"><strong>Date:</strong> {{ optional($bill->bill_date)->format('d/m/Y') }}</div>
        </td>
    </tr>
</table>
<table class="ph-customer">
    <tr>
        <td><strong>Customer:</strong> {{ $bill->customer_name ?: (optional($bill->customer ?? null)->name ?? '—') }}</td>
        <td><strong>Contact:</strong> {{ $bill->customer_contact ?: '—' }}</td>
    </tr>
</table>
