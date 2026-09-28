<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $rows = SiteSetting::query()->orderBy('key')->get();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row->key] = $row->value;
        }

        return $this->data($settings);
    }

    public function update(Request $request)
    {
        $body = $request->all();

        if (count($body) > 100) {
            return $this->message('Too many settings in a single request.', 422);
        }

        foreach ($body as $key => $value) {
            if (! is_string($key) || ! preg_match('/^[a-z0-9_]{1,100}$/', $key)) {
                return $this->message("Invalid setting key: {$key}", 422);
            }
            if (is_array($value) || is_object($value)) {
                return $this->message("Invalid value for setting: {$key}", 422);
            }
        }

        $count = 0;
        foreach ($body as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value]
            );
            $count++;
        }

        // Site chrome (contact info, socials, announcement) is cached — refresh
        // immediately so the public frontend reflects the change on next request.
        \Illuminate\Support\Facades\Cache::forget('site_config');

        return $this->data($count);
    }
}
