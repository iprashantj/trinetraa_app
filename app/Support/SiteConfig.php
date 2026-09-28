<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class SiteConfig
{
    /**
     * DB-managed site settings (admin panel) override env defaults, so the
     * whole public frontend is editable from /admin/settings without deploys.
     * Keys: site_settings.key (snake_case) → config key (camelCase).
     */
    protected const DB_OVERRIDES = [
        'site_name'        => 'siteName',
        'whatsapp_number'  => 'whatsappNumber',
        'contact_email'    => 'contactEmail',
        'contact_phone'    => 'contactPhone',
        'contact_address'  => 'contactAddress',
        'instagram_url'    => 'instagramUrl',
        'facebook_url'     => 'facebookUrl',
        'google_maps_url'  => 'googleMapsUrl',
        'store_hours'      => 'storeHours',
        'announcement_text'    => 'announcementText',
        'announcement_enabled' => 'announcementEnabled',
        'gstin'            => 'gstin',
    ];

    public static function all(): array
    {
        $config = [
            'siteUrl'        => rtrim(env('SITE_URL', env('APP_URL', 'https://trinetraaoptician.com')), '/'),
            'siteName'       => env('APP_NAME', 'Trinetraa Optician'),
            'whatsappNumber' => env('WHATSAPP_NUMBER', '918888899737'),
            'contactEmail' => env('CONTACT_EMAIL', 'info@trinetraa.com'),
            'contactPhone' => env('CONTACT_PHONE', '+91 88888 99737'),
            'contactAddress' => env('CONTACT_ADDRESS', 'Narayan Bapu Chowk, Shriram Nagar, Nashik Road, Nashik, Maharashtra 422101'),
            'instagramUrl' => env('INSTAGRAM_URL', 'https://www.instagram.com/trinetraa_optician/'),
            'facebookUrl' => env('FACEBOOK_URL', 'https://www.facebook.com/people/Trinetraa-Optician/61587645774578/'),
            'googleMapsUrl' => env('GOOGLE_MAPS_URL', 'https://maps.google.com/?q=Trinetraa+Optician+Nashik'),
            'storeHours' => 'Mon–Sat: 10 AM – 8 PM',
            'announcementText' => '',
            'announcementEnabled' => false,
            'gstin' => env('GSTIN', ''),
        ];

        try {
            if (Schema::hasTable('site_settings')) {
                $rows = \App\Models\SiteSetting::whereIn('key', array_keys(self::DB_OVERRIDES))->pluck('value', 'key');
                foreach ($rows as $key => $value) {
                    if ($value === null || $value === '') {
                        continue;
                    }
                    $configKey = self::DB_OVERRIDES[$key];
                    $config[$configKey] = $configKey === 'announcementEnabled'
                        ? in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true)
                        : $value;
                }
            }
        } catch (\Throwable $e) {
            // DB unavailable (migrations, cold boot) — env defaults still serve the site.
        }

        return $config;
    }
}
