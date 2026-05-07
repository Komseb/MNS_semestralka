<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Permissions;
use App\Models\Post;
use App\Models\Category;
use App\Models\Comment;
use Exception;

class PostsController extends BaseController
{
    //main handler for post operations: create, view, vote, comment, delete
    public function handle(array $params = []): void
    {
        $action = $_GET['action'] ?? 'index';

        //vote endpoint must return json before any html output
        if ($action === 'vote') {
            $this->handleVote();
            return;
        }

        //redirect anonymous users trying to create or delete content
        if (in_array($action, ['create', 'store', 'delete']) && !$this->isLoggedIn()) {
            $this->redirect(BASE_URL . '?r=auth/login');
            return;
        }

        //commenting also requires authentication
        if (in_array($action, ['comment-store', 'comment-delete', 'comment-vote']) && !$this->isLoggedIn()) {
            $this->redirect(BASE_URL . '?r=auth/login');
            return;
        }

        switch ($action) {
            case 'create':
                $this->handleCreate();
                break;
            case 'store':
                $this->handleStore();
                break;
            case 'view':
                $this->handleView();
                break;
            case 'delete':
                $this->handleDelete();
                break;
            case 'vote':
                $this->handleVote();
                break;
            case 'comment-store':
                $this->handleCommentStore();
                break;
            case 'comment-delete':
                $this->handleCommentDelete();
                break;
            default:
                $this->handleCreate();
                break;
        }
    }

    //render post creation form with category dropdown
    private function handleCreate(): void
    {
        //load all categories for selection
        $categories = Category::getAll();

        $this->render('posts/create.twig', [
            'categories' => $categories
        ]);
    }

    //process post creation form with validation and image upload
    private function handleStore(): void
    {
        //extract and sanitize form inputs
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);

        $errors = [];

        //validate title length and presence
        if (empty($title)) {
            $errors[] = 'Název příspěvku je povinný';
        } elseif (mb_strlen($title) < 3) {
            $errors[] = 'Název musí mít alespoň 3 znaky';
        } elseif (mb_strlen($title) > 300) {
            $errors[] = 'Název může mít maximálně 300 znaků';
        }

        if (empty($content)) {
            $errors[] = 'Obsah příspěvku je povinný';
        } elseif (mb_strlen($content) < 10) {
            $errors[] = 'Obsah musí mít alespoň 10 znaků';
        }

        //ensure valid category selected
        if ($categoryId <= 0) {
            $errors[] = 'Vyberte kategorii';
        }

        //process optional image attachment
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleImageUpload($_FILES['image']);

            if ($uploadResult['success']) {
                $imagePath = $uploadResult['path'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        }

        if (!empty($errors)) {
            $categories = Category::getAll();
            $this->render('posts/create.twig', [
                'errors' => $errors,
                'title' => $title,
                'content' => $content,
                'category_id' => $categoryId,
                'categories' => $categories
            ]);
            return;
        }

        //insert post into database with current user as author
        $userId = $_SESSION['user_id'];
        $success = Post::create($userId, $categoryId, $title, $content, $imagePath);

        if ($success) {
            $_SESSION['success'] = 'Příspěvek byl úspěšně vytvořen';
            $this->redirect(BASE_URL . '?r=home');
        } else {
            $categories = Category::getAll();
            $this->render('posts/create.twig', [
                'errors' => ['Při vytváření příspěvku došlo k chybě'],
                'title' => $title,
                'content' => $content,
                'category_id' => $categoryId,
                'categories' => $categories
            ]);
        }
    }

    //render single post detail page with comments and voting ui
    private function handleView(): void
    {
        //extract post id from query string
        $postId = (int)($_GET['id'] ?? 0);

        if ($postId <= 0) {
            $this->redirect(BASE_URL . '?r=home');
            return;
        }

        //fetch post with author, category and vote score
        $post = Post::getById($postId);

        if (!$post) {
            $this->redirect(BASE_URL . '?r=home');
            return;
        }

        //load all comments for this post
        $comments = Comment::getByPost($postId);

        //retrieve feedback messages from previous actions
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        //determine user's role and ban status for permissions
        $currentRoleName = $_SESSION['user_role'] ?? 'user';
        $currentRoleId = Permissions::getRoleId($currentRoleName);
        $isBanned = isset($_SESSION['user_id']) ? Permissions::isUserBanned((int)$_SESSION['user_id']) : false;

        //load user's existing vote to highlight correct button
        if (isset($_SESSION['user_id'])) {
            $vote = Post::getUserVoteState($postId, (int)$_SESSION['user_id']);
            $post['user_vote'] = $vote ?? 'none';
        }

        $this->render('posts/view.twig', [
            'post' => $post,
            'comments' => $comments,
            'success' => $success,
            'error' => $error,
            'permissions' => new Permissions(),
            'currentRoleId' => $currentRoleId,
            'isBanned' => $isBanned
        ]);
    }

    //delete post with owner/admin permission check
    private function handleDelete(): void
    {
        $postId = (int)($_POST['post_id'] ?? 0);

        if ($postId <= 0) {
            $this->redirect(BASE_URL . '?r=home');
            return;
        }

        //load post to verify existence and check owner
        $post = Post::getById($postId);

        if (!$post) {
            $_SESSION['error'] = 'Příspěvek nebyl nalezen';
            $this->redirect(BASE_URL . '?r=home');
            return;
        }

        //verify user is post owner or has admin rights over author
        $userId = $_SESSION['user_id'];
        $currentRoleName = $_SESSION['user_role'] ?? 'user';
        $currentRoleId = Permissions::getRoleId($currentRoleName);
        $ownerRoleId = (int)($post['user_role_id'] ?? 1);

        if ($post['user_id'] != $userId && !Permissions::canDeleteUserContent($currentRoleId, $ownerRoleId)) {
            $_SESSION['error'] = 'Nemáte oprávnění smazat tento příspěvek';
            $this->redirect(BASE_URL . '?r=home');
            return;
        }

        if (Post::delete($postId)) {
            $_SESSION['success'] = 'Příspěvek byl smazán';
        } else {
            $_SESSION['error'] = 'Při mazání příspěvku došlo k chybě';
        }

        $this->redirect(BASE_URL . '?r=home');
    }

    //process upvote/downvote ajax request and return json response
    private function handleVote(): void
    {
        //flush any buffered html to ensure clean json output
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        //set json content type for proper browser handling
        header('Content-Type: application/json; charset=utf-8');

        try {
            //require authentication to prevent anonymous voting
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                die(json_encode(['success' => false, 'error' => 'Musíte být přihlášeni']));
            }

            //extract and validate vote data
            $postId = (int)($_POST['post_id'] ?? 0);
            $voteType = $_POST['vote_type'] ?? '';

            //ensure post id and vote type are valid
            if ($postId <= 0 || !in_array($voteType, ['upvote', 'downvote'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'error' => 'Neplatné údaje']));
            }

            $userId = $_SESSION['user_id'];

            //toggle vote using name-based method
            $success = Post::voteByName($postId, $userId, $voteType);

            if ($success) {
                //reload post to get recalculated vote score
                $post = Post::getById($postId);
                //fetch user's current vote state after toggle
                $stateStr = Post::getUserVoteState($postId, $userId);
                $state = $stateStr ?? 'none';

                http_response_code(200);
                die(json_encode([
                    'success' => true,
                    'score' => $post ? (int)$post['vote_score'] : 0,
                    'state' => $state
                ]));
            } else {
                http_response_code(500);
                die(json_encode(['success' => false, 'error' => 'Při hlasování došlo k chybě']));
            }
        } catch (Exception $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'error' => 'Chyba serveru: ' . $e->getMessage()]));
        }
        exit;
    }

    //validate and process image upload for post attachments
    private function handleImageUpload(array $file): array
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; //5mb limit

        //verify mime type using finfo for security
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return [
                'success' => false,
                'error' => 'Povolené formáty jsou pouze JPG, PNG, GIF a WEBP'
            ];
        }

        //enforce upload size limit
        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'error' => 'Maximální velikost obrázku je 5 MB'
            ];
        }

        //create unique filename with user id and random bytes
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => 'jpg'
        };

        $filename = 'post_' . $_SESSION['user_id'] . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $uploadDir = __DIR__ . '/../images/posts/';
        $uploadPath = $uploadDir . $filename;

        //ensure posts directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        //transfer file from temp to permanent location
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'success' => true,
                'path' => 'images/posts/' . $filename
            ];
        }

        return [
            'success' => false,
            'error' => 'Při nahrávání obrázku došlo k chybě'
        ];
    }

    //helper to check if user has active session
    private function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    //process comment form submission with validation
    private function handleCommentStore(): void
    {
        //extract form data
        $postId = (int)($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $parentId = isset($_POST['parent_comment_id']) ? (int)$_POST['parent_comment_id'] : null;

        //validate comment content length
        if ($postId <= 0 || empty($content) || mb_strlen($content) < 2) {
            $_SESSION['error'] = 'Neplatný komentář';
            $this->redirect(BASE_URL . '?r=posts&action=view&id=' . $postId);
            return;
        }

        $post = Post::getById($postId);
        if (!$post) {
            $_SESSION['error'] = 'Příspěvek nenalezen';
            $this->redirect(BASE_URL . '?r=home');
            return;
        }

        //insert comment into database
        $userId = $_SESSION['user_id'];
        $commentId = Comment::create($postId, $userId, $content, $parentId);

        if ($commentId) {
            $_SESSION['success'] = 'Komentář přidán';
        } else {
            $_SESSION['error'] = 'Chyba při ukládání komentáře';
        }

        $this->redirect(BASE_URL . '?r=posts&action=view&id=' . $postId);
    }

    //delete comment with owner/admin permission check
    private function handleCommentDelete(): void
    {
        $commentId = (int)($_POST['comment_id'] ?? 0);
        if ($commentId <= 0) {
            $this->redirect(BASE_URL . '?r=home');
            return;
        }

        //load comment to verify ownership
        $comment = Comment::find($commentId);
        if (!$comment) {
            $_SESSION['error'] = 'Komentář nenalezen';
            $this->redirect(BASE_URL . '?r=home');
            return;
        }

        $userId = $_SESSION['user_id'];
        $currentRoleName = $_SESSION['user_role'] ?? 'user';
        $currentRoleId = Permissions::getRoleId($currentRoleName);
        $ownerRoleId = (int)$comment['user_role_id'];

        //verify user owns comment or has admin rights
        if ($comment['user_id'] != $userId && !Permissions::canDeleteUserContent($currentRoleId, $ownerRoleId)) {
            $_SESSION['error'] = 'Nemáš oprávnění smazat tento komentář';
            $this->redirect(BASE_URL . '?r=posts&action=view&id=' . $comment['post_id']);
            return;
        }

        if (Comment::delete($commentId)) {
            $_SESSION['success'] = 'Komentář smazán';
        } else {
            $_SESSION['error'] = 'Chyba při mazání komentáře';
        }

        $this->redirect(BASE_URL . '?r=posts&action=view&id=' . $comment['post_id']);
    }
}
