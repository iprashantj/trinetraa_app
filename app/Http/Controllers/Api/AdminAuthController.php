<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Jwt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $payload = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:1',
        ]);

        $user = User::where('email', $payload['email'])->first();
        if (! $user || ! password_verify($payload['password'], $user->password_hash)) {
            return $this->message('Invalid email or password.', 401);
        }
        if (! $user->is_active) {
            return $this->message('This account has been deactivated. Contact the owner.', 403);
        }

        $token = Jwt::signAdmin(['id' => $user->id, 'email' => $user->email, 'name' => $user->name, 'role' => $user->role ?? 'staff']);

        // SESSION_DOMAIN targets the live domain; on localhost that (and the
        // Secure flag) would prevent the cookie from sticking. Decide per request.
        // CookieJar falls back to config('session.domain') for any falsy domain,
        // so host-only cookies require clearing the config for this request.
        $isLocal = in_array($request->getHost(), ['127.0.0.1', 'localhost', '::1'], true);
        if ($isLocal) {
            config(['session.domain' => null]);
        }

        return $this->data(['name' => $user->name, 'email' => $user->email, 'role' => $user->role ?? 'staff'])
            ->withCookie(Cookie::make('admin_token', $token, 60 * 8, '/', null, ! $isLocal, true, false, 'strict'));
    }

    public function me(Request $request)
    {
        $admin = $request->attributes->get('admin');
        if (! $admin) {
            return $this->message('Unauthorized.', 401);
        }
        return $this->data(['id' => $admin['id'], 'email' => $admin['email'], 'name' => $admin['name'], 'role' => $admin['role'] ?? 'staff']);
    }

    public function changePassword(Request $request)
    {
        $payload = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|max:100',
        ]);

        $admin = $request->attributes->get('admin');
        $user = User::find($admin['id'] ?? 0);
        if (! $user || ! password_verify($payload['current_password'], $user->password_hash)) {
            return $this->message('Current password is incorrect.', 422);
        }

        $user->update(['password_hash' => password_hash($payload['new_password'], PASSWORD_BCRYPT, ['cost' => 10])]);

        return $this->message('Password updated.');
    }

    public function logout()
    {
        return $this->message('Logged out.')->withCookie(Cookie::forget('admin_token', '/'));
    }
}
