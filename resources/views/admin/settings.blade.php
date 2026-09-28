@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
@php
    $settingGroups = [
        [
            'title' => '🛒 Marketplace',
            'fields' => [
                ['key' => 'amazon_store_url', 'label' => 'Amazon Store URL', 'placeholder' => 'https://www.amazon.in/stores/...', 'type' => 'url'],
                ['key' => 'flipkart_store_url', 'label' => 'Flipkart Store URL', 'placeholder' => 'https://www.flipkart.com/...', 'type' => 'url'],
                ['key' => 'amazon_enabled', 'label' => 'Amazon Globally Enabled', 'type' => 'checkbox'],
                ['key' => 'flipkart_enabled', 'label' => 'Flipkart Globally Enabled', 'type' => 'checkbox'],
            ],
        ],
        [
            'title' => '📱 Contact & Social — shown across the whole website',
            'fields' => [
                ['key' => 'whatsapp_number', 'label' => 'WhatsApp Number (with country code)', 'placeholder' => '919876543210', 'type' => 'text'],
                ['key' => 'contact_phone', 'label' => 'Contact Phone (footer & contact page)', 'placeholder' => '+91 88888 99737', 'type' => 'text'],
                ['key' => 'contact_email', 'label' => 'Contact Email', 'placeholder' => 'info@trinetraaoptician.com', 'type' => 'text'],
                ['key' => 'contact_address', 'label' => 'Store Address (footer & contact page)', 'placeholder' => 'Narayan Bapu Chowk, Nashik Road, Nashik', 'type' => 'text'],
                ['key' => 'store_phone', 'label' => 'Store Phone (pincode checker)', 'placeholder' => '0253-123456', 'type' => 'text'],
                ['key' => 'store_address', 'label' => 'Pickup Address (pincode checker)', 'placeholder' => '123 Main Street, Nashik', 'type' => 'text'],
                ['key' => 'store_hours', 'label' => 'Store Hours', 'placeholder' => 'Mon–Sat: 10 AM – 8 PM', 'type' => 'text'],
                ['key' => 'instagram_url', 'label' => 'Instagram URL', 'placeholder' => 'https://instagram.com/...', 'type' => 'url'],
                ['key' => 'facebook_url', 'label' => 'Facebook URL', 'placeholder' => 'https://facebook.com/...', 'type' => 'url'],
                ['key' => 'google_maps_url', 'label' => 'Google Maps URL (footer QR & links)', 'placeholder' => 'https://maps.google.com/?q=...', 'type' => 'url'],
            ],
        ],
        [
            'title' => '📢 Announcement Bar — banner on every public page',
            'fields' => [
                ['key' => 'announcement_text', 'label' => 'Announcement Text (leave empty to hide)', 'placeholder' => 'Monsoon Sale — 20% off all frames this week!', 'type' => 'text'],
                ['key' => 'announcement_enabled', 'label' => 'Show Announcement Bar', 'type' => 'checkbox'],
            ],
        ],
        [
            'title' => '🔍 SEO',
            'fields' => [
                ['key' => 'meta_title', 'label' => 'Default Meta Title', 'placeholder' => 'Trinetraa Optician Nashik', 'type' => 'text'],
                ['key' => 'meta_description', 'label' => 'Default Meta Description', 'placeholder' => 'Premium eyewear...', 'type' => 'textarea'],
                ['key' => 'google_analytics_id', 'label' => 'Google Analytics ID', 'placeholder' => 'G-XXXXXXXXXX', 'type' => 'text'],
            ],
        ],
    ];
@endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Site Settings</h1>
    <span class="badge bg-success" id="savedBadge" style="display:none">✓ Saved</span>
</div>
<div id="settingsError"></div>
<form id="settingsForm">
    @foreach($settingGroups as $group)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">{{ $group['title'] }}</div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($group['fields'] as $field)
                        @php $type = $field['type'] ?? 'text'; @endphp
                        <div class="{{ $type === 'textarea' ? 'col-12' : 'col-md-6' }}">
                            <label class="form-label fw-semibold" for="{{ $field['key'] }}">{{ $field['label'] }}</label>
                            @if($type === 'checkbox')
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="{{ $field['key'] }}" data-setting="{{ $field['key'] }}" data-type="checkbox">
                                    <label class="form-check-label" for="{{ $field['key'] }}">Enabled</label>
                                </div>
                            @elseif($type === 'textarea')
                                <textarea class="form-control" rows="3" id="{{ $field['key'] }}" data-setting="{{ $field['key'] }}" placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
                            @else
                                <input type="{{ $type }}" class="form-control" id="{{ $field['key'] }}" data-setting="{{ $field['key'] }}" placeholder="{{ $field['placeholder'] ?? '' }}">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
    <button type="submit" class="btn btn-primary btn-lg" id="saveBtn">Save All Settings</button>
</form>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var form = document.getElementById('settingsForm');
    var saveBtn = document.getElementById('saveBtn');
    var savedBadge = document.getElementById('savedBadge');
    var errorEl = document.getElementById('settingsError');
    var fields = Array.prototype.slice.call(document.querySelectorAll('[data-setting]'));

    function hideSaved() { savedBadge.style.display = 'none'; }

    fields.forEach(function (el) {
        var ev = el.getAttribute('data-type') === 'checkbox' ? 'change' : 'input';
        el.addEventListener(ev, hideSaved);
    });

    function applySettings(settings) {
        settings = settings || {};
        fields.forEach(function (el) {
            var key = el.getAttribute('data-setting');
            var val = settings[key];
            if (el.getAttribute('data-type') === 'checkbox') {
                el.checked = val === 'true' || val === true;
            } else {
                el.value = val == null ? '' : val;
            }
        });
    }

    function collect() {
        var out = {};
        fields.forEach(function (el) {
            var key = el.getAttribute('data-setting');
            if (el.getAttribute('data-type') === 'checkbox') {
                out[key] = el.checked ? 'true' : 'false';
            } else {
                out[key] = el.value;
            }
        });
        return out;
    }

    fetch('/api/admin/settings').then(function (r) { return r.json(); }).then(function (d) {
        applySettings(d.data || {});
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        errorEl.innerHTML = '';
        savedBadge.style.display = 'none';
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving…';
        try {
            var res = await fetch('/api/admin/settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(collect())
            });
            if (!res.ok) throw new Error('Failed to save');
            savedBadge.style.display = '';
        } catch (err) {
            errorEl.innerHTML = '<div class="alert alert-danger py-2">' + (err.message || 'Failed to save') + '</div>';
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save All Settings';
        }
    });
})();
</script>
@endverbatim
@endpush
