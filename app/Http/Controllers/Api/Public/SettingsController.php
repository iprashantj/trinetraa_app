<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;

class SettingsController extends Controller
{
    private const PUBLIC_KEYS = [
        'amazon_store_url',
        'flipkart_store_url',
        'whatsapp_number',
        'amazon_enabled',
        'flipkart_enabled',
        'store_address',
        'store_phone',
        'store_hours',
    ];

    public function index()
    {
        $rows = SiteSetting::query()
            ->whereIn('key', self::PUBLIC_KEYS)
            ->get();

        $settings = [];
        foreach ($rows as $r) {
            $settings[$r->key] = $r->value;
        }

        return $this->data($settings);
    }
}
