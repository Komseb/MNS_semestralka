<?php

namespace App\Models;

use App\Core\BaseModel;

class UserManagement extends BaseModel
{
    //load all users with role names, ban status and ban expiration details
    //includes subqueries for active ban check, expiration date and ban reason
    public static function getAllUsers(): array
    {
        $query = "
            SELECT 
                u.id,
                u.username,
                u.email,
                u.role_id,
                ur.name as role_name,
                u.created_at,
                (
                    SELECT COUNT(*) 
                    FROM user_bans ub 
                    WHERE ub.user_id = u.id 
                    AND ub.is_active = 1
                    AND (ub.expires_at IS NULL OR ub.expires_at > NOW())
                ) as is_banned,
                (
                    SELECT ub.expires_at
                    FROM user_bans ub 
                    WHERE ub.user_id = u.id 
                    AND ub.is_active = 1
                    AND (ub.expires_at IS NULL OR ub.expires_at > NOW())
                    ORDER BY ub.banned_at DESC
                    LIMIT 1
                ) as ban_expires_at,
                (
                    SELECT ub.reason
                    FROM user_bans ub 
                    WHERE ub.user_id = u.id 
                    AND ub.is_active = 1
                    AND (ub.expires_at IS NULL OR ub.expires_at > NOW())
                    ORDER BY ub.banned_at DESC
                    LIMIT 1
                ) as ban_reason
            FROM users u
            INNER JOIN user_roles ur ON u.role_id = ur.id
            ORDER BY u.role_id DESC, u.created_at DESC
        ";

        return self::fetchAll($query);
    }

    //create new ban record for user with optional expiration date
    //deactivates any previous active bans before inserting new one
    public static function banUser(int $userId, int $bannedBy, string $reason, ?string $expiresAt = null): bool
    {
        //disable all existing active bans for user
        $updateQuery = "UPDATE user_bans SET is_active = 0 WHERE user_id = ? AND is_active = 1";
        self::execute($updateQuery, [$userId]);

        //insert new ban with reason and admin who issued it
        $query = "
            INSERT INTO user_bans (user_id, banned_by, reason, banned_at, expires_at, is_active)
            VALUES (?, ?, ?, NOW(), ?, 1)
        ";

        try {
            self::execute($query, [$userId, $bannedBy, $reason, $expiresAt]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    //remove active ban by setting is_active to 0 and recording unban admin and time
    public static function unbanUser(int $userId, int $unbannedBy): bool
    {
        $query = "
            UPDATE user_bans 
            SET is_active = 0, unbanned_by = ?, unbanned_at = NOW()
            WHERE user_id = ? AND is_active = 1
        ";

        try {
            self::execute($query, [$unbannedBy, $userId]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    //modify user's role id for permission escalation or demotion
    //caller must verify superadmin permissions before using this method
    public static function changeUserRole(int $userId, int $newRoleId): bool
    {
        $query = "UPDATE users SET role_id = ? WHERE id = ?";

        try {
            self::execute($query, [$newRoleId, $userId]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    //lookup user by id with role name joined from user_roles table
    public static function getUserById(int $userId): ?array
    {
        $query = "
            SELECT 
                u.*,
                ur.name as role_name
            FROM users u
            INNER JOIN user_roles ur ON u.role_id = ur.id
            WHERE u.id = ?
            LIMIT 1
        ";

        return self::fetchOne($query, [$userId]);
    }

    //permanently remove user account from database
    //posts, comments and votes cascade delete via foreign key constraints
    public static function deleteUser(int $userId): bool
    {
        $query = "DELETE FROM users WHERE id = ?";

        try {
            self::execute($query, [$userId]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    //calculate user activity statistics: post count, comment count and total votes received
    public static function getUserStats(int $userId): array
    {
        $query = "
            SELECT 
                (SELECT COUNT(*) FROM posts WHERE user_id = ?) as post_count,
                (SELECT COUNT(*) FROM comments WHERE user_id = ?) as comment_count,
                (SELECT COALESCE(SUM(vt.value), 0) 
                 FROM post_votes pv 
                 INNER JOIN posts p ON pv.post_id = p.id 
                 INNER JOIN vote_types vt ON pv.vote_type_id = vt.id
                 WHERE p.user_id = ?) as total_post_votes
        ";

        $stats = self::fetchOne($query, [$userId, $userId, $userId]);
        return $stats ?: ['post_count' => 0, 'comment_count' => 0, 'total_post_votes' => 0];
    }

    //verify if user currently has active ban that hasn't expired
    public static function isUserBanned(int $userId): bool
    {
        $query = "
            SELECT id 
            FROM user_bans 
            WHERE user_id = ? 
            AND is_active = 1 
            AND (expires_at IS NULL OR expires_at > NOW())
            LIMIT 1
        ";
        $ban = self::fetchOne($query, [$userId]);
        return $ban !== null;
    }

    //retrieve full details of user's most recent active ban including admin who banned them
    public static function getActiveBanInfo(int $userId): ?array
    {
        $query = "
            SELECT 
                ub.*,
                u.username as banned_by_username
            FROM user_bans ub
            INNER JOIN users u ON ub.banned_by = u.id
            WHERE ub.user_id = ? 
            AND ub.is_active = 1 
            AND (ub.expires_at IS NULL OR ub.expires_at > NOW())
            ORDER BY ub.banned_at DESC
            LIMIT 1
        ";
        return self::fetchOne($query, [$userId]);
    }

    //count all registered users in database for dashboard statistics
    public static function getTotalUsers(): int
    {
        $result = self::fetchOne("SELECT COUNT(*) as total FROM users");
        return $result ? (int)$result['total'] : 0;
    }

    //count users with active non-expired bans for admin dashboard metrics
    public static function getBannedUsersCount(): int
    {
        $query = "
            SELECT COUNT(DISTINCT user_id) as total
            FROM user_bans 
            WHERE is_active = 1 
            AND (expires_at IS NULL OR expires_at > NOW())
        ";
        $result = self::fetchOne($query);
        return $result ? (int)$result['total'] : 0;
    }
}
