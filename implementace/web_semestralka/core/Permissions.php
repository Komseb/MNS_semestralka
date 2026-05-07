<?php

namespace App\Core;

class Permissions
{
    //role ids
    const ROLE_USER = 1;
    const ROLE_ADMIN = 2;
    const ROLE_SUPERADMIN = 3;

    //check if user can moderate content (delete posts/comments)
    public static function canModerateContent(int $userRoleId): bool
    {
        return $userRoleId >= self::ROLE_ADMIN;
    }

    //check if user can manage categories
    public static function canManageCategories(int $userRoleId): bool
    {
        return $userRoleId >= self::ROLE_ADMIN;
    }

    //check if user can ban other users
    //admin can ban users, but NOT other admins
    //superadmin can ban anyone including admins
    public static function canBanUser(int $currentUserRoleId, int $targetUserRoleId): bool
    {
        //superadmin can ban anyone
        if ($currentUserRoleId === self::ROLE_SUPERADMIN) {
            return true;
        }

        //admin can ban only regular users (not admins or superadmins)
        if ($currentUserRoleId === self::ROLE_ADMIN && $targetUserRoleId === self::ROLE_USER) {
            return true;
        }

        return false;
    }

    //check if user can delete another user's content
    //admin can delete regular users' content
    //superadmin can delete anyone's content including admins
    public static function canDeleteUserContent(int $currentUserRoleId, int $contentOwnerRoleId): bool
    {
        //superadmin can delete anyone's content
        if ($currentUserRoleId === self::ROLE_SUPERADMIN) {
            return true;
        }

        //admin can delete only regular users' content (not other admins)
        if ($currentUserRoleId === self::ROLE_ADMIN && $contentOwnerRoleId === self::ROLE_USER) {
            return true;
        }

        return false;
    }

    //check if user can change other user's role
    //only superadmin can change roles
    public static function canChangeUserRole(int $currentUserRoleId): bool
    {
        return $currentUserRoleId === self::ROLE_SUPERADMIN;
    }

    //check if user can access admin panel
    public static function canAccessAdminPanel(int $userRoleId): bool
    {
        return $userRoleId >= self::ROLE_ADMIN;
    }

    //check if user can view system settings
    //only superadmin
    public static function canViewSystemSettings(int $userRoleId): bool
    {
        return $userRoleId === self::ROLE_SUPERADMIN;
    }

    //check if user is banned
    public static function isUserBanned(int $userId): bool
    {
        // delegate to UserManagement where BaseModel protected methods are accessible
        return \App\Models\UserManagement::isUserBanned($userId);
    }

    //get active ban info for user
    public static function getUserBanInfo(int $userId): ?array
    {
        return \App\Models\UserManagement::getActiveBanInfo($userId);
    }

    //get role name by id
    public static function getRoleName(int $roleId): string
    {
        return match ($roleId) {
            self::ROLE_USER => 'user',
            self::ROLE_ADMIN => 'admin',
            self::ROLE_SUPERADMIN => 'superadmin',
            default => 'unknown'
        };
    }

    //get role id by name
    public static function getRoleId(string $roleName): int
    {
        return match ($roleName) {
            'user' => self::ROLE_USER,
            'admin' => self::ROLE_ADMIN,
            'superadmin' => self::ROLE_SUPERADMIN,
            default => self::ROLE_USER
        };
    }
}
