<?php

namespace App\Models;

use App\Core\BaseModel;

class Comment extends BaseModel
{
    //insert new comment on post with optional parent for threaded replies
    //returns comment id on success, false on database error
    public static function create(int $postId, int $userId, string $content, ?int $parentCommentId = null): int|false
    {
        $sql = "INSERT INTO comments (post_id, user_id, parent_comment_id, content, created_at) VALUES (?, ?, ?, ?, NOW())";
        try {
            self::execute($sql, [$postId, $userId, $parentCommentId, $content]);
            $commentId = (int) self::lastInsertId();
            self::notify(
                "comment.created",
                [
                    "id" => $commentId,
                    "post_id" => $postId,
                    "user_id" => $userId,
                    "parent_comment_id" => $parentCommentId,
                    "content" => $content
                ]
            );
            return $commentId;
        } catch (\PDOException $e) {
            return false;
        }
    }

    //load all comments for post with author info
    //returns flat list ordered by creation time descending (newest first)
    public static function getByPost(int $postId): array
    {
        $sql = "SELECT c.*, u.username, u.avatar, u.role_id as user_role_id
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.post_id = ?
                ORDER BY c.created_at DESC";
        return self::fetchAll($sql, [$postId]);
    }

    //lookup single comment by id with author details
    public static function find(int $id): ?array
    {
        $sql = "SELECT c.*, u.username, u.avatar, u.role_id as user_role_id
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.id = ?
                LIMIT 1";
        return self::fetchOne($sql, [$id]);
    }

    //remove comment from database, comment votes cascade delete via foreign key
    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM comments WHERE id = ? LIMIT 1";
        try {
            $comment = self::find($id);
            if ($comment === null) {
                return false;
            }
            self::execute($sql, [$id]);
            self::notify(
                "comment.deleted",
                [
                    "id" => $id,
                    "post_id" => $comment['post_id'],
                    "user_id" => $comment['user_id'],
                    "content" => $comment['content']
                ]
            );
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }
}
