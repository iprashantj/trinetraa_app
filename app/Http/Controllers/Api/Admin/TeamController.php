<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Owner-only management of admin team members.
 */
class TeamController extends Controller
{
    public function index()
    {
        return $this->data(
            User::orderBy('created_at')->get(['id', 'name', 'email', 'role', 'is_active', 'created_at'])
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:100',
            'role' => 'required|in:owner,staff',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]),
            'role' => $data['role'],
            'is_active' => true,
        ]);

        return $this->data($user->only(['id', 'name', 'email', 'role', 'is_active']), null, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (! $user) {
            return $this->notFound();
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8|max:100',
            'role' => 'sometimes|in:owner,staff',
            'is_active' => 'sometimes|boolean',
        ]);

        $actor = $request->attributes->get('admin');
        $demoting = (isset($data['role']) && $data['role'] !== 'owner') || (isset($data['is_active']) && ! $data['is_active']);
        if ($demoting && $user->role === 'owner' && User::where('role', 'owner')->where('is_active', true)->count() <= 1) {
            return $this->message('Cannot demote or deactivate the last active owner.', 422);
        }
        if (($actor['id'] ?? null) == $user->id && isset($data['is_active']) && ! $data['is_active']) {
            return $this->message('You cannot deactivate your own account.', 422);
        }

        $update = array_intersect_key($data, array_flip(['name', 'email', 'role', 'is_active']));
        if (! empty($data['password'])) {
            $update['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
        }
        $user->update($update);

        return $this->data($user->only(['id', 'name', 'email', 'role', 'is_active']));
    }

    public function destroy(Request $request, $id)
    {
        $user = User::find($id);
        if (! $user) {
            return $this->notFound();
        }

        $actor = $request->attributes->get('admin');
        if (($actor['id'] ?? null) == $user->id) {
            return $this->message('You cannot delete your own account.', 422);
        }
        if ($user->role === 'owner' && User::where('role', 'owner')->where('is_active', true)->count() <= 1) {
            return $this->message('Cannot delete the last active owner.', 422);
        }

        $user->delete();

        return $this->message('Team member removed.');
    }
}
