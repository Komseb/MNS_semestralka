<?php


namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Permissions;
use App\Models\UserManagement;
use App\Models\Post;
use App\Models\Category;

class AdminController extends BaseController
{
    //main handler for admin panel routes, checks permissions and delegates to specific actions
    public function handle(array $params = []): void
    {
        //ensure user is authenticated
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        //load current user data including role from database
        $currentUser = UserManagement::getUserById($_SESSION['user_id']);
        if (!$currentUser) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        //sync session role with database to prevent role privilege escalation
        $mapped = Permissions::getRoleName((int)$currentUser['role_id']);
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $mapped) {
            $_SESSION['user_role'] = $mapped;
        }

        //verify user has admin or superadmin role
        if (!Permissions::canAccessAdminPanel($currentUser['role_id'])) {
            header('Location: ' . BASE_URL . '?r=home');
            exit;
        }

        //extract action from url params, default to dashboard
        $action = $params[0] ?? 'dashboard';

        //route request to appropriate handler method
        switch ($action) {
            case 'dashboard':
                $this->showDashboard($currentUser);
                break;
            case 'users':
                $this->manageUsers($currentUser);
                break;
            case 'posts':
                $this->managePosts($currentUser);
                break;
            case 'ban-user':
                $this->handleBanUser($currentUser);
                break;
            case 'unban-user':
                $this->handleUnbanUser($currentUser);
                break;
            case 'change-role':
                $this->handleChangeRole($currentUser);
                break;
            case 'delete-user':
                $this->handleDeleteUser($currentUser);
                break;
            case 'delete-post':
                $this->handleDeletePost($currentUser);
                break;
            case 'categories':
                $this->manageCategories($currentUser);
                break;
            case 'create-category':
                $this->handleCreateCategory($currentUser);
                break;
            case 'edit-category':
                $this->handleEditCategory($currentUser);
                break;
            case 'delete-category':
                $this->handleDeleteCategory($currentUser);
                break;
            case 'settings':
                $this->manageSettings($currentUser);
                break;
            case 'update-settings':
                $this->handleUpdateSettings($currentUser);
                break;
            default:
                $this->showDashboard($currentUser);
        }
    }

    //render main admin dashboard with overview statistics
    private function showDashboard(array $currentUser): void
    {
        $this->meta = [
            'title' => 'Admin Panel - Ziggid',
            'keywords' => 'admin, dashboard, správa',
            'description' => 'Administrační panel platformy Ziggid',
        ];

        $this->render('admin/dashboard.twig', [
            'currentUser' => $currentUser
        ]);
    }

    //render users management page with list of all users and ban status
    private function manageUsers(array $currentUser): void
    {
        $this->meta = [
            'title' => 'Správa uživatelů - Admin Panel',
            'keywords' => 'admin, uživatelé, správa',
            'description' => 'Správa uživatelů platformy Ziggid',
        ];

        $users = UserManagement::getAllUsers();

        //retrieve flash messages from session and clear them
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $this->render('admin/users.twig', [
            'currentUser' => $currentUser,
            'users' => $users,
            // provide an instance so twig can call methods via dot syntax
            'permissions' => new Permissions(),
            'success' => $success,
            'error' => $error
        ]);
    }

    //process ban user form submission with reason and expiration
    private function handleBanUser(array $currentUser): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        //extract form data
        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $duration = $_POST['duration'] ?? 'permanent';

        //load target user to validate and check permissions
        $targetUser = UserManagement::getUserById($targetUserId);
        if (!$targetUser) {
            $_SESSION['error'] = 'Uživatel nenalezen';
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        //verify admin has permission to ban this role level
        if (!Permissions::canBanUser($currentUser['role_id'], $targetUser['role_id'])) {
            $_SESSION['error'] = 'Nemáš oprávnění banovat tohoto uživatele';
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        //convert duration string to datetime or null for permanent
        $expiresAt = null;
        if ($duration !== 'permanent') {
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$duration}"));
        }

        //execute ban and store in database with audit trail
        if (UserManagement::banUser($targetUserId, $currentUser['id'], $reason, $expiresAt)) {
            $_SESSION['success'] = "Uživatel {$targetUser['username']} byl zabanován";
        } else {
            $_SESSION['error'] = 'Chyba při banování uživatele';
        }

        header('Location: ' . BASE_URL . '?r=admin/users');
        exit;
    }

    //process unban user request and restore account access
    private function handleUnbanUser(array $currentUser): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        $targetUserId = (int)($_POST['user_id'] ?? 0);

        //fetch user data for validation
        $targetUser = UserManagement::getUserById($targetUserId);
        if (!$targetUser) {
            $_SESSION['error'] = 'Uživatel nenalezen';
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        //use same permission check as banning
        if (!Permissions::canBanUser($currentUser['role_id'], $targetUser['role_id'])) {
            $_SESSION['error'] = 'Nemáš oprávnění odbanovat tohoto uživatele';
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        //deactivate active ban record
        if (UserManagement::unbanUser($targetUserId, $currentUser['id'])) {
            $_SESSION['success'] = "Uživatel {$targetUser['username']} byl odbanován";
        } else {
            $_SESSION['error'] = 'Chyba při odbanování uživatele';
        }

        header('Location: ' . BASE_URL . '?r=admin/users');
        exit;
    }

    //process role change request, restricted to superadmin only
    private function handleChangeRole(array $currentUser): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        //enforce superadmin restriction for role modifications
        if (!Permissions::canChangeUserRole($currentUser['role_id'])) {
            $_SESSION['error'] = 'Pouze SuperAdmin může měnit role uživatelů';
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $newRoleId = (int)($_POST['role_id'] ?? 1);

        //get target user
        $targetUser = UserManagement::getUserById($targetUserId);
        if (!$targetUser) {
            $_SESSION['error'] = 'Uživatel nenalezen';
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        //prevent privilege escalation by self-modification
        if ($targetUserId === $currentUser['id']) {
            $_SESSION['error'] = 'Nemůžeš změnit vlastní roli';
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        //ensure role id is one of: 1=user, 2=admin, 3=superadmin
        if (!in_array($newRoleId, [1, 2, 3])) {
            $_SESSION['error'] = 'Neplatná role';
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        //change role
        if (UserManagement::changeUserRole($targetUserId, $newRoleId)) {
            $newRoleName = Permissions::getRoleName($newRoleId);
            $_SESSION['success'] = "Role uživatele {$targetUser['username']} změněna na {$newRoleName}";
        } else {
            $_SESSION['error'] = 'Chyba při změně role';
        }

        header('Location: ' . BASE_URL . '?r=admin/users');
        exit;
    }

    //permanently delete user account and all associated content
    private function handleDeleteUser(array $currentUser): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        $targetUserId = (int)($_POST['user_id'] ?? 0);

        //load user to verify existence and permissions
        $targetUser = UserManagement::getUserById($targetUserId);
        if (!$targetUser) {
            $_SESSION['error'] = 'Uživatel nenalezen';
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        //safety check to prevent accidental self-deletion
        if ($targetUserId === $currentUser['id']) {
            $_SESSION['error'] = 'Nemůžeš smazat vlastní účet';
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        //verify permission based on role hierarchy
        if (!Permissions::canDeleteUserContent($currentUser['role_id'], $targetUser['role_id'])) {
            $_SESSION['error'] = 'Nemáš oprávnění smazat tohoto uživatele';
            header('Location: ' . BASE_URL . '?r=admin/users');
            exit;
        }

        //delete user
        if (UserManagement::deleteUser($targetUserId)) {
            $_SESSION['success'] = "Uživatel {$targetUser['username']} byl smazán";
        } else {
            $_SESSION['error'] = 'Chyba při mazání uživatele';
        }

        header('Location: ' . BASE_URL . '?r=admin/users');
        exit;
    }

    //render posts management page with all posts and moderation actions
    private function managePosts(array $currentUser): void
    {
        $this->meta = [
            'title' => 'Správa příspěvků - Admin Panel',
            'keywords' => 'admin, příspěvky, správa',
            'description' => 'Správa příspěvků platformy Ziggid',
        ];

        $posts = Post::getAllWithDetails(100, 0);

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $this->render('admin/posts.twig', [
            'currentUser' => $currentUser,
            'posts' => $posts,
            'permissions' => new Permissions(),
            'success' => $success,
            'error' => $error
        ]);
    }

    //delete post from admin panel with role-based permission check
    private function handleDeletePost(array $currentUser): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?r=admin/posts');
            exit;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        $post = Post::getById($postId);
        if (!$post) {
            $_SESSION['error'] = 'Příspěvek nenalezen';
            header('Location: ' . BASE_URL . '?r=admin/posts');
            exit;
        }

        $ownerRoleId = (int)($post['user_role_id'] ?? 1);
        if ($post['user_id'] !== $currentUser['id'] && !Permissions::canDeleteUserContent((int)$currentUser['role_id'], $ownerRoleId)) {
            $_SESSION['error'] = 'Nemáš oprávnění smazat tento příspěvek';
            header('Location: ' . BASE_URL . '?r=admin/posts');
            exit;
        }

        if (Post::delete($postId)) {
            $_SESSION['success'] = 'Příspěvek byl smazán';
        } else {
            $_SESSION['error'] = 'Chyba při mazání příspěvku';
        }

        header('Location: ' . BASE_URL . '?r=admin/posts');
        exit;
    }

    //render categories crud page, superadmin only feature
    private function manageCategories(array $currentUser): void
    {
        //restrict to superadmin role
        if ($currentUser['role_id'] != 3) {
            $_SESSION['error'] = 'Pouze SuperAdmin může spravovat kategorie';
            header('Location: ' . BASE_URL . '?r=admin/dashboard');
            exit;
        }

        $this->meta = [
            'title' => 'Správa kategorií - Admin Panel',
            'keywords' => 'admin, kategorie, správa',
            'description' => 'Správa kategorií platformy Ziggid',
        ];

        $categories = Category::getAll();

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $this->render('admin/categories.twig', [
            'currentUser' => $currentUser,
            'categories' => $categories,
            'success' => $success,
            'error' => $error
        ]);
    }

    //create new category with auto-generated slug from name
    private function handleCreateCategory(array $currentUser): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $currentUser['role_id'] != 3) {
            header('Location: ' . BASE_URL . '?r=admin/categories');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)));

        if (empty($name)) {
            $_SESSION['error'] = 'Název kategorie je povinný';
            header('Location: ' . BASE_URL . '?r=admin/categories');
            exit;
        }

        if (Category::create($name, $slug, $description)) {
            $_SESSION['success'] = "Kategorie {$name} byla vytvořena";
        } else {
            $_SESSION['error'] = 'Chyba při vytváření kategorie';
        }

        header('Location: ' . BASE_URL . '?r=admin/categories');
        exit;
    }

    //update existing category details and regenerate slug
    private function handleEditCategory(array $currentUser): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $currentUser['role_id'] != 3) {
            header('Location: ' . BASE_URL . '?r=admin/categories');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)));

        if (empty($name) || $id <= 0) {
            $_SESSION['error'] = 'Neplatná data';
            header('Location: ' . BASE_URL . '?r=admin/categories');
            exit;
        }

        if (Category::update($id, $name, $slug, $description)) {
            $_SESSION['success'] = "Kategorie {$name} byla upravena";
        } else {
            $_SESSION['error'] = 'Chyba při úpravě kategorie';
        }

        header('Location: ' . BASE_URL . '?r=admin/categories');
        exit;
    }

    //delete category if no posts exist in it
    private function handleDeleteCategory(array $currentUser): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $currentUser['role_id'] != 3) {
            header('Location: ' . BASE_URL . '?r=admin/categories');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $category = Category::findById($id);

        if (!$category) {
            $_SESSION['error'] = 'Kategorie nenalezena';
            header('Location: ' . BASE_URL . '?r=admin/categories');
            exit;
        }

        if (Category::delete($id)) {
            $_SESSION['success'] = "Kategorie {$category['name']} byla smazána";
        } else {
            $_SESSION['error'] = 'Chyba při mazání kategorie. Zkontroluj, zda v ní nejsou příspěvky.';
        }

        header('Location: ' . BASE_URL . '?r=admin/categories');
        exit;
    }

    //render system settings dashboard with statistics, superadmin only
    private function manageSettings(array $currentUser): void
    {
        //restrict to superadmin role
        if ($currentUser['role_id'] != 3) {
            $_SESSION['error'] = 'Pouze SuperAdmin může spravovat nastavení';
            header('Location: ' . BASE_URL . '?r=admin/dashboard');
            exit;
        }

        $this->meta = [
            'title' => 'Systémové nastavení - Admin Panel',
            'keywords' => 'admin, nastavení, systém',
            'description' => 'Systémové nastavení platformy Ziggid',
        ];

        //gather platform statistics for dashboard
        $stats = [
            'totalUsers' => UserManagement::getTotalUsers(),
            'totalPosts' => Post::countFiltered(null),
            'totalCategories' => count(Category::getAll()),
            'bannedUsers' => UserManagement::getBannedUsersCount()
        ];

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $this->render('admin/settings.twig', [
            'currentUser' => $currentUser,
            'stats' => $stats,
            'success' => $success,
            'error' => $error
        ]);
    }

    //process settings form submission, placeholder for future functionality
    private function handleUpdateSettings(array $currentUser): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $currentUser['role_id'] != 3) {
            header('Location: ' . BASE_URL . '?r=admin/settings');
            exit;
        }

        //todo: implement actual settings persistence logic here
        $_SESSION['success'] = 'Nastavení byla uložena';
        header('Location: ' . BASE_URL . '?r=admin/settings');
        exit;
    }
}
