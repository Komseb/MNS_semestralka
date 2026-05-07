<?php

namespace App\Models;

use App\Core\BaseModel;

class User extends BaseModel
{
    //create new user account with credentials and default user role
    //returns user id on success, false if email or username already exists
    public static function register(string $email, string $username, string $password): int|false
    {
        //hash password using bcrypt algorithm for secure storage
        $hash = password_hash($password, PASSWORD_BCRYPT);

        //insert user with default role_id 1 (regular user role)
        $sql = "INSERT INTO users (email, username, password_hash, role_id, created_at) VALUES (?, ?, ?, 1, NOW())";
        try {
            self::execute($sql, [$email, $username, $hash]);
            return (int)self::lastInsertId();
        } catch (\PDOException $e) {
            //duplicate email or username
            return false;
        }
    }

    //authenticate user by username or email with password verification
    //returns user data without password hash on success, null if credentials invalid
    public static function login(string $usernameOrEmail, string $password): ?array
    {
        //lookup user by either email or username field
        $sql = "SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1";
        $user = self::fetchOne($sql, [$usernameOrEmail, $usernameOrEmail]);

        if (!$user) {
            return null;
        }

        //compare provided password with stored bcrypt hash
        if (password_verify($password, $user['password_hash'])) {
            //strip sensitive hash data before returning
            unset($user['password_hash']);
            return $user;
        }

        return null;
    }

    //load user by id with role name joined from user_roles table
    public static function findById(int $id): ?array
    {
        $sql = "SELECT u.*, ur.name as role 
                FROM users u 
                INNER JOIN user_roles ur ON u.role_id = ur.id 
                WHERE u.id = ? LIMIT 1";
        return self::fetchOne($sql, [$id]);
    }

    //lookup user by email address for uniqueness checks and profile loading
    public static function findByEmail(string $email): ?array
    {
        $sql = "SELECT id, email, username, role_id, created_at FROM users WHERE email = ? LIMIT 1";
        return self::fetchOne($sql, [$email]);
    }

    //lookup user by username for uniqueness checks and profile loading
    public static function findByUsername(string $username): ?array
    {
        $sql = "SELECT id, email, username, role_id, created_at FROM users WHERE username = ? LIMIT 1";
        return self::fetchOne($sql, [$username]);
    }

    //change user's email address with uniqueness validation
    public static function updateEmail(int $userId, string $newEmail): bool
    {
        $sql = "UPDATE users SET email = ? WHERE id = ?";
        try {
            self::execute($sql, [$newEmail, $userId]);
            return true;
        } catch (\PDOException $e) {
            //duplicate email constraint violation
            return false;
        }
    }

    //change user's password with bcrypt rehashing
    public static function updatePassword(int $userId, string $newPassword): bool
    {
        //rehash new password before storing
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
        try {
            self::execute($sql, [$hash, $userId]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    //set user's profile avatar image path, null to remove avatar
    //automatically deletes old avatar file if it exists
    public static function updateAvatar(int $userId, ?string $avatarPath): bool
    {
        //fetch current user data to get old avatar path
        $user = self::findById($userId);

        //if user has an existing avatar and we are changing or removing it
        if ($user && !empty($user['avatar']) && $user['avatar'] !== $avatarPath) {
            $oldFilePath = __DIR__ . '/../' . $user['avatar'];
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        $sql = "UPDATE users SET avatar = ? WHERE id = ?";
        try {
            self::execute($sql, [$avatarPath, $userId]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    //change user's display name with uniqueness validation
    public static function updateUsername(int $userId, string $newUsername): bool
    {
        $sql = "UPDATE users SET username = ? WHERE id = ?";
        try {
            self::execute($sql, [$newUsername, $userId]);
            return true;
        } catch (\PDOException $e) {
            //duplicate username constraint violation
            return false;
        }
    }
}
