<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Post;
use App\Models\Category;

class HomeController extends BaseController
{
    //render main feed with filtering, sorting and pagination
    public function handle(array $params = []): void
    {
        //extract query parameters for filters
        $categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
        $sortParam = $_GET['sort'] ?? 'date';
        $page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;

        $strategy = ($sortParam == 'votes') ? new \App\Core\Sorting\TopSortingStrategy() : new \App\Core\Sorting\NewestSortingStrategy();

        //configure posts per page and calculate offset
        $postsPerPage = 10;
        $offset = ($page - 1) * $postsPerPage;

        //load categories for filter dropdown menu
        $categories = Category::getAll();

        //count total posts matching filter for pagination calculation
        $totalPosts = Post::countFiltered($categoryId);
        $totalPages = (int)ceil($totalPosts / $postsPerPage);

        //fetch posts with category filter, sort and pagination
        $posts = Post::getAllWithDetailsFiltered($categoryId, $strategy, $postsPerPage, $offset);

        //attach user's vote state to each post for optimistic ui rendering
        if (isset($_SESSION['user_id'])) {
            $uid = (int)$_SESSION['user_id'];
            foreach ($posts as &$post) {
                $vote = Post::getUserVoteState((int)$post['id'], $uid);
                $post['user_vote'] = $vote ?? 'none';
            }
            unset($post);
        }

        //retrieve and clear flash messages from previous actions
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        //configure feed page metadata
        $this->meta = [
            'title' => 'Příspěvky - Ziggid',
            'keywords' => 'příspěvky, diskuze, feed, komunita',
            'description' => 'Prohlížej nejnovější příspěvky od komunity Ziggid',
        ];

        //display feed with all data and current filter state
        $this->render('home/index.twig', [
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategory' => $categoryId,
            'selectedSort' => $sortParam,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPosts' => $totalPosts,
            'success' => $success,
            'error' => $error
        ]);
    }
}
