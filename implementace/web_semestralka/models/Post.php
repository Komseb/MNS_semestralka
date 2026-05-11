<?php

namespace App\Models;

use App\Core\BaseModel;
use App\Core\Sorting\SortingStrategy;
use App\Core\Events\Observer;

class Post extends BaseModel
{
    //insert new post into database with optional image attachment
    public static function create(int $userId, int $categoryId, string $title, string $content, ?string $imagePath = null): bool
    {
        $sql = "INSERT INTO posts (user_id, category_id, title, content, image_path, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";

        $result = (bool)self::execute($sql, [$userId, $categoryId, $title, $content, $imagePath]);
        if ($result) {
            self::notify("post.created", [
                "user_id" => $userId,
                "category_id" => $categoryId,
                "title" => $title,
                "content" => $content,
                "image_path" => $imagePath
            ]);
        }
        return $result;
    }

    //load posts with author, category, vote score and comment count aggregated
    //returns chronologically ordered list with pagination support
    public static function getAllWithDetails(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT 
                    p.*,
                    u.username,
                    u.role_id as user_role_id,
                    u.avatar,
                    c.name as category_name,
                    c.slug as category_slug,
                    COALESCE(SUM(vt.value), 0) as vote_score,
                    COUNT(DISTINCT co.id) as comment_count
                FROM posts p
                JOIN users u ON p.user_id = u.id
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN post_votes pv ON p.id = pv.post_id
                LEFT JOIN vote_types vt ON pv.vote_type_id = vt.id
                LEFT JOIN comments co ON p.id = co.post_id
                GROUP BY p.id
                LIMIT ? OFFSET ?";

        return self::fetchAll($sql, [$limit, $offset]);
    }

    //load posts with optional category filter and dynamic sorting by date or votes
    //supports chronological or popularity-based ordering with pagination
    public static function getAllWithDetailsFiltered(?int $categoryId, SortingStrategy $sortingStrategy, int $limit = 50, int $offset = 0): array
    {
        //determine order clause based on sort parameter
        $orderBy = $sortingStrategy->getSortedQuerry();

        $sql = "SELECT 
                    p.*,
                    u.username,
                    u.role_id as user_role_id,
                    u.avatar,
                    c.name as category_name,
                    c.slug as category_slug,
                    COALESCE(SUM(vt.value), 0) as vote_score,
                    COUNT(DISTINCT co.id) as comment_count
                FROM posts p
                JOIN users u ON p.user_id = u.id
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN post_votes pv ON p.id = pv.post_id
                LEFT JOIN vote_types vt ON pv.vote_type_id = vt.id
                LEFT JOIN comments co ON p.id = co.post_id";

        $params = [];

        if ($categoryId !== null) {
            $sql .= " WHERE p.category_id = ?";
            $params[] = $categoryId;
        }

        $sql .= " GROUP BY p.id
                  {$orderBy}
                  LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        return self::fetchAll($sql, $params);
    }

    //get total post count with optional category filter for pagination calculation
    public static function countFiltered(?int $categoryId = null): int
    {
        $sql = "SELECT COUNT(DISTINCT p.id) as total FROM posts p";

        if ($categoryId !== null) {
            $sql .= " WHERE p.category_id = ?";
            $result = self::fetchOne($sql, [$categoryId]);
        } else {
            $result = self::fetchOne($sql);
        }

        return $result ? (int)$result['total'] : 0;
    }

    //load all posts in specific category with user, votes and comments aggregated
    public static function getByCategory(int $categoryId, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT 
                    p.*,
                    u.username,
                    u.role_id as user_role_id,
                    u.avatar,
                    c.name as category_name,
                    c.slug as category_slug,
                    COALESCE(SUM(vt.value), 0) as vote_score,
                    COUNT(DISTINCT co.id) as comment_count
                FROM posts p
                JOIN users u ON p.user_id = u.id
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN post_votes pv ON p.id = pv.post_id
                LEFT JOIN vote_types vt ON pv.vote_type_id = vt.id
                LEFT JOIN comments co ON p.id = co.post_id
                WHERE p.category_id = ?
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";

        return self::fetchAll($sql, [$categoryId, $limit, $offset]);
    }

    //load single post with author info, category and vote score calculated
    public static function getById(int $id): ?array
    {
        $sql = "SELECT 
                    p.*,
                    u.username,
                    u.role_id as user_role_id,
                    u.avatar,
                    c.name as category_name,
                    c.slug as category_slug,
                    COALESCE(SUM(vt.value), 0) as vote_score
                FROM posts p
                JOIN users u ON p.user_id = u.id
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN post_votes pv ON p.id = pv.post_id
                LEFT JOIN vote_types vt ON pv.vote_type_id = vt.id
                WHERE p.id = ?
                GROUP BY p.id";

        return self::fetchOne($sql, [$id]);
    }

    //remove post from database and delete associated image file from filesystem
    //votes and comments cascade delete automatically via foreign key constraints
    public static function delete(int $id): bool
    {
        //retrieve image path for file cleanup
        $post = self::fetchOne("SELECT image_path FROM posts WHERE id = ?", [$id]);

        //delete database record, triggers cascade for votes and comments
        $deleted = (bool)self::execute("DELETE FROM posts WHERE id = ?", [$id]);

        //remove physical image file if post had attachment
        if ($deleted && $post && $post['image_path']) {
            $imagePath = __DIR__ . '/../' . $post['image_path'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        if ($deleted) {
            self::notify(
                "post.deleted",
                [
                    "id" => $id,
                    "image_path" => $post ? $post['image_path'] : null
                ]
            );
        }

        return $deleted;
    }

    //modify post title, content and optionally image path with timestamp update
    public static function update(int $id, string $title, string $content, ?string $imagePath = null): bool
    {
        //include image path update if provided
        if ($imagePath !== null) {
            $sql = "UPDATE posts SET title = ?, content = ?, image_path = ?, updated_at = NOW() WHERE id = ?";
            return (bool)self::execute($sql, [$title, $content, $imagePath, $id]);
        } else {
            $sql = "UPDATE posts SET title = ?, content = ?, updated_at = NOW() WHERE id = ?";
            return (bool)self::execute($sql, [$title, $content, $id]);
        }
    }

    //process user vote on post with toggle behavior for same vote type
    //accepts 'upvote' or 'downvote' string names, converts to vote_type_id internally
    public static function voteByName(int $postId, int $userId, string $voteName): bool
    {
        //lookup vote type id from name to avoid hardcoded ids
        $voteTypeId = self::getVoteTypeIdByName($voteName);
        if ($voteTypeId === null) {
            return false;
        }
        //check if user has existing vote on this post
        $existingVote = self::fetchOne(
            "SELECT id, vote_type_id FROM post_votes WHERE post_id = ? AND user_id = ?",
            [$postId, $userId]
        );

        if ($existingVote) {
            //clicking same vote type removes the vote (toggle off)
            if ($existingVote['vote_type_id'] == $voteTypeId) {
                $success = self::execute(
                    "DELETE FROM post_votes WHERE post_id = ? AND user_id = ?",
                    [$postId, $userId]
                );
                if ($success) {
                    self::notify('post.voted', ['post_id' => $postId, 'user_id' => $userId, 'vote_type' => 'none']);
                }
                return true;
            }
            //clicking opposite vote type switches the vote
            $success = self::execute(
                "UPDATE post_votes SET vote_type_id = ? WHERE post_id = ? AND user_id = ?",
                [$voteTypeId, $postId, $userId]
            );
            if ($success) {
                self::notify('post.voted', ['post_id' => $postId, 'user_id' => $userId, 'vote_type' => $voteName]);
            }
            return true;
        }

        //create new vote record for first-time voting
        $success = self::execute(
            "INSERT INTO post_votes (post_id, user_id, vote_type_id, created_at) VALUES (?, ?, ?, NOW())",
            [$postId, $userId, $voteTypeId]
        );
        if ($success) {
            self::notify('post.voted', ['post_id' => $postId, 'user_id' => $userId, 'vote_type' => $voteName]);
        }
        return true;
    }

    //retrieve user's vote type id for post, returns vote_type_id or null if no vote
    public static function getUserVote(int $postId, int $userId): ?int
    {
        $row = self::fetchOne(
            "SELECT vote_type_id FROM post_votes WHERE post_id = ? AND user_id = ?",
            [$postId, $userId]
        );
        return $row ? (int)$row['vote_type_id'] : null;
    }

    //get user's vote as simplified state string: 'up', 'down', or null for ui display
    public static function getUserVoteState(int $postId, int $userId): ?string
    {
        $row = self::fetchOne(
            "SELECT vt.name FROM post_votes pv JOIN vote_types vt ON pv.vote_type_id = vt.id WHERE pv.post_id = ? AND pv.user_id = ?",
            [$postId, $userId]
        );
        //map vote_types.name to simplified frontend state
        if (!$row) return null;
        if ($row['name'] === 'upvote') return 'up';
        if ($row['name'] === 'downvote') return 'down';
        return null;
    }

    //lookup vote_types table id by name string for database operations
    public static function getVoteTypeIdByName(string $name): ?int
    {
        $row = self::fetchOne(
            "SELECT id FROM vote_types WHERE name = ?",
            [$name]
        );
        return $row ? (int)$row['id'] : null;
    }

    //set user's vote to specific state idempotently: 'up', 'down', or 'none'
    //ensures final vote state matches requested state regardless of current state
    public static function setUserVoteState(int $postId, int $userId, string $state): bool
    {
        $state = strtolower($state);
        //validate state parameter
        if (!in_array($state, ['up', 'down', 'none'], true)) {
            return false;
        }

        //check current vote status
        $existing = self::fetchOne(
            "SELECT id, vote_type_id FROM post_votes WHERE post_id = ? AND user_id = ?",
            [$postId, $userId]
        );

        //handle vote removal
        if ($state === 'none') {
            if ($existing) {
                return (bool)self::execute("DELETE FROM post_votes WHERE id = ?", [$existing['id']]);
            }
            return true; //already no vote
        }

        //convert simplified state to vote type name
        $name = $state === 'up' ? 'upvote' : 'downvote';
        $typeId = self::getVoteTypeIdByName($name);
        if ($typeId === null) {
            error_log("getVoteTypeIdByName returned null for name=$name");
            return false;
        }

        if ($existing) {
            //skip update if already desired state
            if ((int)$existing['vote_type_id'] === $typeId) {
                return true;
            }
            $result = self::execute(
                "UPDATE post_votes SET vote_type_id = ? WHERE post_id = ? AND user_id = ?",
                [$typeId, $postId, $userId]
            );
            if (!$result) {
                error_log("UPDATE post_votes failed for postId=$postId, userId=$userId, typeId=$typeId");
            }
            return (bool)$result;
        }

        $result = self::execute(
            "INSERT INTO post_votes (post_id, user_id, vote_type_id, created_at) VALUES (?, ?, ?, NOW())",
            [$postId, $userId, $typeId]
        );
        if (!$result) {
            error_log("INSERT post_votes failed for postId=$postId, userId=$userId, typeId=$typeId");
        }
        return (bool)$result;
    }

}
