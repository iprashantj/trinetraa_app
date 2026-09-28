<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerUser;
use App\Support\Jwt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CustomerAuthController extends Controller
{
    public function login(Request $request)
    {
        $payload = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:1',
        ]);

        $customer = CustomerUser::where('email', $payload['email'])->first();
        if (! $customer || ! password_verify($payload['password'], $customer->password_hash)) {
            return $this->message('Invalid email or password.', 401);
        }

        $token = Jwt::signCustomer(['id' => $customer->id, 'email' => $customer->email, 'name' => $customer->name]);

        return $this->data(['id' => $customer->id, 'name' => $customer->name, 'email' => $customer->email])
            ->withCookie($this->customerCookie($token));
    }

    public function register(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|max:100',
        ]);

        if (CustomerUser::where('email', $payload['email'])->exists()) {
            return $this->message('Email already registered.', 422);
        }

        $customer = CustomerUser::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? null,
            'password_hash' => password_hash($payload['password'], PASSWORD_BCRYPT, ['cost' => 10]),
        ]);

        $token = Jwt::signCustomer(['id' => $customer->id, 'email' => $customer->email, 'name' => $customer->name]);

        return $this->data(['id' => $customer->id, 'name' => $customer->name, 'email' => $customer->email], null, 201)
            ->withCookie($this->customerCookie($token));
    }

    public function me(Request $request)
    {
        $customer = $request->attributes->get('customer');
        if (! $customer) {
            return $this->message('Unauthorized.', 401);
        }
        return $this->data(['id' => $customer['id'], 'email' => $customer['email'], 'name' => $customer['name']]);
    }

    public function logout()
    {
        return $this->message('Logged out.')->withCookie(Cookie::forget('customer_token', '/'));
    }

    protected function customerCookie(string $token)
    {
        // SESSION_DOMAIN targets the live domain; on localhost that (and the
        // Secure flag) would prevent the cookie from sticking. Decide per request.
        // CookieJar falls back to config('session.domain') for any falsy domain,
        // so host-only cookies require clearing the config for this request.
        $isLocal = in_array(request()->getHost(), ['127.0.0.1', 'localhost', '::1'], true);
        if ($isLocal) {
            config(['session.domain' => null]);
        }

        return Cookie::make('customer_token', $token, 60 * 24 * 30, '/', null, ! $isLocal, true, false, 'lax');
    }
}
