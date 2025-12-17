<?php

namespace App\Services;

use App\Models\DocumentPermission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DocumentPermissionService
{
    public static function can(string $resource, string $ability, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        $permission = DocumentPermission::query()
            ->where('user_id', $user->id)
            ->where('resource', $resource)
            ->first();

        if (!$permission) {
            return false;
        }

        return match ($ability) {
            'view' => (bool) $permission->can_view,
            'create' => (bool) $permission->can_create,
            'update' => (bool) $permission->can_update,
            'delete' => (bool) $permission->can_delete,
            default => false,
        };
    }

    public static function canView(string $resource, ?User $user = null): bool
    {
        return self::can($resource, 'view', $user);
    }

    public static function canCreate(string $resource, ?User $user = null): bool
    {
        return self::can($resource, 'create', $user);
    }

    public static function canUpdate(string $resource, ?User $user = null): bool
    {
        return self::can($resource, 'update', $user);
    }

    public static function canDelete(string $resource, ?User $user = null): bool
    {
        return self::can($resource, 'delete', $user);
    }

    /**
     * Return the set of category IDs the user is allowed to see. Admins get null (meaning all).
     * If the user has no categories assigned, return an empty array.
     */
    public static function allowedCategoryIds(?User $user = null): ?array
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return [];
        }
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return null; // all categories
        }
        return $user->categories()->pluck('categories.id')->all();
    }
}
