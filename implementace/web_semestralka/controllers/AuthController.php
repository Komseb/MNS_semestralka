<?php


namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Permissions;
use App\Models\User;

class AuthController extends BaseController
{
    //main handler for authentication routes: register, login, logout
    public function handle(array $params = []): void
    {
        //extract action from url, default to login page
        $action = $params[0] ?? 'login';

        //logout requires immediate session destruction before any output
        if ($action === 'logout') {
            $this->handleLogout();
            return;
        }

        //set page metadata for seo
        $this->meta = [
            'title' => $action === 'register' ? 'Registrace' : 'Prihlaseni',
            'keywords' => 'auth, register, login',
            'description' => 'auth pages',
        ];

        //process form submissions for register and login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'register') {
                $this->handleRegister();
                return;
            }
            if ($action === 'login') {
                $this->handleLogin();
                return;
            }
        }

        //show appropriate form based on action
        if ($action === 'register') {
            $this->render('auth/register.twig');
            return;
        }

        //fallback to login form for any other action
        $this->render('auth/login.twig');
    }

    //process registration form with validation and password confirmation
    private function handleRegister(): void
    {
        //sanitize and extract form inputs
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        //validate email format, username length, password strength
        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Neplatný email';
        }
        if (strlen($username) < 3) {
            $errors[] = 'Uživatelské jméno musí mít aspoň 3 znaky';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Heslo musí mít aspoň 8 znaků';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Hesla se neshodují';
        }

        if (!empty($errors)) {
            $this->render('auth/register.twig', [
                'errors' => $errors,
                'email' => $email,
                'username' => $username,
            ]);
            return;
        }

        //attempt user creation with hashed password
        $userId = User::register($email, $username, $password);
        if ($userId === false) {
            $this->render('auth/register.twig', [
                'errors' => ['Email nebo uživatelské jméno už je použito'],
                'email' => $email,
                'username' => $username,
            ]);
            return;
        }

        //automatically log in new user to improve ux
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['user_avatar'] = null; //avatar not set on registration
        $_SESSION['user_role'] = 'user';

        //send user to main feed after successful registration
        $this->redirect(BASE_URL . '?r=home');
    }

    //process login form with username/email and password verification
    private function handleLogin(): void
    {
        $usernameOrEmail = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        //ensure both fields are provided
        if (empty($usernameOrEmail) || empty($password)) {
            $this->render('auth/login.twig', [
                'errors' => ['Vyplňte všechna pole'],
                'username' => $usernameOrEmail,
            ]);
            return;
        }

        //attempt authentication with bcrypt password verification
        $user = User::login($usernameOrEmail, $password);
        if ($user === null) {
            $this->render('auth/login.twig', [
                'errors' => ['Neplatné přihlašovací údaje'],
                'username' => $usernameOrEmail,
            ]);
            return;
        }

        //establish authenticated session with user data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_avatar'] = $user['avatar'] ?? null;
        //convert numeric role_id to string name for template convenience
        $roleName = isset($user['role_id']) ? Permissions::getRoleName((int)$user['role_id']) : 'user';
        $_SESSION['user_role'] = $roleName;

        //send authenticated user to main feed
        $this->redirect(BASE_URL . '?r=home');
    }

    //clear session data and destroy session to log user out securely
    private function handleLogout(): void
    {
        //clear all session variables
        $_SESSION = [];

        //remove session cookie from browser if cookies are enabled
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        //send user back to public landing page after logout
        $this->redirect(BASE_URL . '?r=landing');
    }
}
