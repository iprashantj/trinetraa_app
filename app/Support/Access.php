<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Role/permission/branch helpers. The owner/staff role model is unchanged; on top of it
 * a staff user can be tied to one branch and limited to named modules.
 * A user with no branch_id / no permissions list behaves exactly as before (unrestricted),
 * so existing accounts keep working until the owner narrows them.
 */
class Access
{
    public const PERMISSIONS = [
        'inventory' => 'Inventory & stock inward',
        'transfers' => 'Stock transfers',
        'billing' => 'Billing, POS & returns',
        'customers' => 'Customers',
        'prescriptions' => 'Eye-test / prescription records',
        'reports' => 'Reports',
        'settings' => 'Branches, suppliers & settings',
    ];

    public static function admin(Request $request): array
    {
        return $request->attributes->get('admin') ?? [];
    }

    public static function isOwner(array $admin): bool
    {
        return ($admin['role'] ?? null) === 'owner';
    }

    public static function can(array $admin, string $permission): bool
    {
        if (self::isOwner($admin)) {
            return true;
        }
        $perms = $admin['permissions'] ?? null;

        return $perms === null || in_array($permission, (array) $perms, true);
    }

    /** Branch the request is limited to: owners may pick one (or all = null); branch staff are pinned. */
    public static function branchScope(Request $request): ?int
    {
        $admin = self::admin($request);
        if (! self::isOwner($admin) && ! empty($admin['branch_id'])) {
            return (int) $admin['branch_id'];
        }
        $q = $request->query('branch_id', $request->input('branch_id'));

        return $q ? (int) $q : null;
    }

    public static function canUseBranch(array $admin, int $branchId): bool
    {
        return self::isOwner($admin) || empty($admin['branch_id']) || (int) $admin['branch_id'] === $branchId;
    }

    /** Branch for a write: the pinned branch, else the one supplied. 0 if none could be resolved. */
    public static function writeBranch(Request $request, $supplied = null): int
    {
        $admin = self::admin($request);
        if (! self::isOwner($admin) && ! empty($admin['branch_id'])) {
            return (int) $admin['branch_id'];
        }

        return (int) $supplied;
    }
}
