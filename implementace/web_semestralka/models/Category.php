<?php

namespace App\Models;

use App\Core\BaseModel;

class Category extends BaseModel
{
    //load all categories with post count and highest voted post info
    //includes complex subqueries to find top post title and vote score per category
    public static function getAllWithStats(): array
    {
        $query = "
            SELECT 
                c.id,
                c.name,
                c.slug,
                c.description,
                COUNT(DISTINCT p.id) as post_count,
                (
                    SELECT p2.title 
                    FROM posts p2
                    WHERE p2.category_id = c.id
                    ORDER BY (
                        SELECT COALESCE(SUM(vt.value), 0)
                        FROM post_votes pv2
                        INNER JOIN vote_types vt ON pv2.vote_type_id = vt.id
                        WHERE pv2.post_id = p2.id
                    ) DESC, p2.created_at DESC
                    LIMIT 1
                ) as top_post_title,
                (
                    SELECT COALESCE(SUM(vt.value), 0)
                    FROM posts p3
                    LEFT JOIN post_votes pv3 ON pv3.post_id = p3.id
                    LEFT JOIN vote_types vt ON pv3.vote_type_id = vt.id
                    WHERE p3.category_id = c.id
                    AND p3.id = (
                        SELECT p4.id 
                        FROM posts p4
                        WHERE p4.category_id = c.id
                        ORDER BY (
                            SELECT COALESCE(SUM(vt2.value), 0)
                            FROM post_votes pv4
                            INNER JOIN vote_types vt2 ON pv4.vote_type_id = vt2.id
                            WHERE pv4.post_id = p4.id
                        ) DESC, p4.created_at DESC
                        LIMIT 1
                    )
                ) as top_post_votes
            FROM categories c
            LEFT JOIN posts p ON c.id = p.category_id
            GROUP BY c.id
            ORDER BY c.name ASC
        ";

        return self::fetchAll($query);
    }

    //lookup category by url-friendly slug identifier
    public static function findBySlug(string $slug): ?array
    {
        $query = "SELECT * FROM categories WHERE slug = ? LIMIT 1";
        return self::fetchOne($query, [$slug]);
    }

    //load category by primary key id
    public static function findById(int $id): ?array
    {
        $query = "SELECT * FROM categories WHERE id = ? LIMIT 1";
        return self::fetchOne($query, [$id]);
    }

    //retrieve all categories alphabetically sorted by name
    public static function getAll(): array
    {
        $query = "SELECT * FROM categories ORDER BY name ASC";
        return self::fetchAll($query);
    }

    //insert new category with name, slug and optional description
    public static function create(string $name, string $slug, string $description = ''): bool
    {
        $query = "INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)";
        self::execute($query, [$name, $slug, $description]);
        return true;
    }

    //modify existing category's name, slug and description
    public static function update(int $id, string $name, string $slug, string $description = ''): bool
    {
        $query = "UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?";
        self::execute($query, [$name, $slug, $description, $id]);
        return true;
    }

    //remove category from database, posts in category will be affected by foreign key constraints
    public static function delete(int $id): bool
    {
        $query = "DELETE FROM categories WHERE id = ?";
        self::execute($query, [$id]);
        return true;
    }
}
