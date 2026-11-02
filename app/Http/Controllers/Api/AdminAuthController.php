<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Support\Jwt;
use App\Support\Totp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AdminAuthController extends Controller
{
    private const MAX_FAILED = 5;
    private const LOCK_MINUTES = 15;

    private function audit(Request $request, ?User $user, string $action, int $status): void
    {
        try {
            AdminActivityLog::create([
                'user_id' => $user?->id, 'user_email' => $user?->email ?? $request->input('email'),
                'method' => 'AUTH', 'path' => '/' . ltrim($request->path(), '/'), 'action' => $action,
                'status' => $status, 'ip' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            // The audit trail must never block sign-in.
        }
    }

    private function fail(Request $request, User $user, string $why)
    {
        $n = (int) $user->failed_logins + 1;
        $locked = $n >= self::MAX_FAILED;
        $user->update(['failed_logins' => $locked ? 0 : $n, 'locked_until' => $locked ? now()->addMinutes(self::LOCK_MINUTES) : $user->locked_until]);
        $this->audit($request, $user, $locked ? 'Account locked after repeated failed sign-ins' : $why, 401);
        if ($locked) {
            return $this->message('Too many failed attempts. This account is locked for ' . self::LOCK_MINUTES . ' minutes.', 423);
        }

        return $this->message($why === 'Failed sign-in (wrong code)' ? 'Invalid authentication code.' : 'Invalid email or password.', 401);
    }

    public function login(Request $request)
    {
        $payload = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:1',
            'code' => 'nullable|string|max:12',
        ]);

        $user = User::where('email', $payload['email'])->first();
        if (! $user) {
            $this->audit($request, null, 'Failed sign-in (unknown email)', 401);

            return $this->message('Invalid email or password.', 401);
        }
        if ($user->locked_until && $user->locked_until->isFuture()) {
            $mins = max(1, (int) ceil(now()->diffInSeconds($user->locked_until, false) / 60));
            $this->audit($request, $user, 'Sign-in blocked (account locked)', 423);

            return $this->message("Account temporarily locked after repeated failed sign-ins. Try again in {$mins} minute(s).", 423);
        }
        if (! password_verify($payload['password'], $user->password_hash)) {
            return $this->fail($request, $user, 'Failed sign-in (wrong password)');
        }
        if (! $user->is_active) {
            return $this->message('This account has been deactivated. Contact the owner.', 403);
        }

        // Second factor, when the user has switched it on.
        if ($user->totp_enabled) {
            if (empty($payload['code'])) {
                return response()->json(['mfa_required' => true, 'message' => 'Enter the 6-digit code from your authenticator app.'], 200);
            }
            if (! Totp::verify((string) $user->totp_secret, $payload['code'])) {
                return $this->fail($request, $user, 'Failed sign-in (wrong code)');
            }
        }

        $user->update(['failed_logins' => 0, 'locked_until' => null]);
        $this->audit($request, $user, 'Signed in', 200);

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
        $user = User::find($admin['id']);

        return $this->data([
            'id' => $admin['id'], 'email' => $admin['email'], 'name' => $admin['name'], 'role' => $admin['role'] ?? 'staff',
            'branch_id' => $admin['branch_id'] ?? null, 'permissions' => $admin['permissions'] ?? null,
            'mfa_enabled' => (bool) ($user->totp_enabled ?? false),
        ]);
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

    // ── Multi-factor authentication (authenticator app) ───────────────────────
    public function mfaSetup(Request $request)
    {
        $user = User::find(($request->attributes->get('admin')['id'] ?? 0));
        if (! $user) {
            return $this->message('Unauthorized.', 401);
        }
        if ($user->totp_enabled) {
            return $this->message('MFA is already on. Turn it off first to set it up again.', 422);
        }
        $secret = Totp::generateSecret();
        $user->update(['totp_secret' => $secret]);

        return $this->data(['secret' => $secret, 'uri' => Totp::uri($secret, $user->email)]);
    }

    public function mfaEnable(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|max:12']);
        $user = User::find(($request->attributes->get('admin')['id'] ?? 0));
        if (! $user || ! $user->totp_secret) {
            return $this->message('Start the setup first.', 422);
        }
        if (! Totp::verify($user->totp_secret, $data['code'])) {
            return $this->message('That code is not correct. Check the time on your phone and try again.', 422);
        }
        $user->update(['totp_enabled' => true]);

        return $this->message('Multi-factor authentication is now on.');
    }

    public function mfaDisable(Request $request)
    {
        $data = $request->validate(['password' => 'required|string', 'code' => 'required|string|max:12']);
        $user = User::find(($request->attributes->get('admin')['id'] ?? 0));
        if (! $user || ! password_verify($data['password'], $user->password_hash)) {
            return $this->message('Password is incorrect.', 422);
        }
        if (! $user->totp_enabled || ! Totp::verify((string) $user->totp_secret, $data['code'])) {
            return $this->message('That code is not correct.', 422);
        }
        $user->update(['totp_enabled' => false, 'totp_secret' => null]);

        return $this->message('Multi-factor authentication is now off.');
    }

    public function logout()
    {
        return $this->message('Logged out.')->withCookie(Cookie::forget('admin_token', '/'));
    }
}
