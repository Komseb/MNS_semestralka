<?php


namespace App\Controllers;

use App\Core\BaseController;
use App\Models\User;

class ProfileController extends BaseController
{
    //main handler for user profile management and settings
    public function handle(array $params = []): void
    {
        //redirect anonymous users to login page
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        //load full user data from database
        $user = User::findById($_SESSION['user_id']);
        if (!$user) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        //sync session avatar with database to fix missing data
        if (!isset($_SESSION['user_avatar'])) {
            $_SESSION['user_avatar'] = $user['avatar'] ?? null;
        }

        //configure profile page metadata
        $this->meta = [
            'title' => 'Můj profil - Ziggid',
            'keywords' => 'profil, nastavení, účet',
            'description' => 'Spravuj svůj profil na platformě Ziggid',
        ];

        //process various profile update forms based on action field
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'update_email':
                    $this->handleUpdateEmail($user);
                    return;
                case 'update_password':
                    $this->handleUpdatePassword($user);
                    return;
                case 'upload_avatar':
                    $this->handleUploadAvatar($user);
                    return;
                case 'remove_avatar':
                    $this->handleRemoveAvatar($user);
                    return;
                case 'update_username':
                    $this->handleUpdateUsername($user);
                    return;
            }
        }

        //retrieve flash messages from form submissions
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        //display profile management interface
        $this->render('profile/index.twig', [
            'user' => $user,
            'success' => $success,
            'error' => $error
        ]);
    }

    //process email change request with password confirmation
    private function handleUpdateEmail(array $user): void
    {
        $newEmail = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        //ensure valid email format
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Neplatný email';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        //require current password to authorize email change
        if (!password_verify($password, $user['password_hash'])) {
            $_SESSION['error'] = 'Nesprávné heslo';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        //prevent duplicate emails across different users
        $existingUser = User::findByEmail($newEmail);
        if ($existingUser && $existingUser['id'] !== $user['id']) {
            $_SESSION['error'] = 'Tento email již používá jiný uživatel';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        //persist new email to database
        if (User::updateEmail($user['id'], $newEmail)) {
            $_SESSION['success'] = 'Email byl úspěšně změněn';
        } else {
            $_SESSION['error'] = 'Chyba při změně emailu';
        }

        header('Location: ' . BASE_URL . '?r=profile');
        exit;
    }

    //process password change with current password verification
    private function handleUpdatePassword(array $user): void
    {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        //authenticate user before allowing password change
        if (!password_verify($currentPassword, $user['password_hash'])) {
            $_SESSION['error'] = 'Nesprávné současné heslo';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        //enforce minimum password length for security
        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = 'Nové heslo musí mít alespoň 8 znaků';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        //require password confirmation to prevent typos
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'Nová hesla se neshodují';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        //hash and save new password to database
        if (User::updatePassword($user['id'], $newPassword)) {
            $_SESSION['success'] = 'Heslo bylo úspěšně změněno';
        } else {
            $_SESSION['error'] = 'Chyba při změně hesla';
        }

        header('Location: ' . BASE_URL . '?r=profile');
        exit;
    }

    //process avatar image upload with validation and file management
    private function handleUploadAvatar(array $user): void
    {
        //verify file was successfully received
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Chyba při nahrávání souboru';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        $file = $_FILES['avatar'];

        //restrict to common image formats for security
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['error'] = 'Povolené formáty: JPG, PNG, GIF, WEBP';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        //enforce 2mb limit to prevent server overload
        if ($file['size'] > 2 * 1024 * 1024) {
            $_SESSION['error'] = 'Maximální velikost souboru je 2MB';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        //ensure avatars directory exists with proper permissions
        $uploadDir = __DIR__ . '/../images/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        //create filename with user id and timestamp to prevent conflicts
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        //cleanup previous avatar is now handled by User::updateAvatar

        //transfer file from tmp to permanent location
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $avatarPath = 'images/avatars/' . $filename;

            if (User::updateAvatar($user['id'], $avatarPath)) {
                $_SESSION['user_avatar'] = $avatarPath; //sync session with new avatar path
                $_SESSION['success'] = 'Profilový obrázek byl nahrán';
            } else {
                $_SESSION['error'] = 'Chyba při ukládání do databáze';
            }
        } else {
            $_SESSION['error'] = 'Chyba při nahrávání souboru';
        }

        header('Location: ' . BASE_URL . '?r=profile');
        exit;
    }

    //process avatar deletion and reset to default
    private function handleRemoveAvatar(array $user): void
    {
        //remove physical file is now handled by User::updateAvatar

        //set avatar column to null in users table
        if (User::updateAvatar($user['id'], null)) {
            $_SESSION['user_avatar'] = null; //clear avatar from session
            $_SESSION['success'] = 'Profilový obrázek byl odstraněn';
        } else {
            $_SESSION['error'] = 'Chyba při odstraňování obrázku';
        }

        header('Location: ' . BASE_URL . '?r=profile');
        exit;
    }

    //process username change with validation and uniqueness check
    private function handleUpdateUsername(array $user): void
    {
        $newUsername = trim($_POST['new_username'] ?? '');
        $password = $_POST['password'] ?? '';

        //validate username not empty
        if ($newUsername === '') {
            $_SESSION['error'] = 'Vyplň nové uživatelské jméno';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        //skip update if username unchanged
        if (strcasecmp($newUsername, $user['username']) === 0) {
            $_SESSION['error'] = 'Nové jméno je stejné jako současné';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        //enforce alphanumeric with limited special chars for security
        if (!preg_match('/^[A-Za-z0-9._-]{3,30}$/', $newUsername)) {
            $_SESSION['error'] = 'Jméno musí mít 3-30 znaků a může obsahovat písmena, čísla, ., _, -';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        //require password confirmation for username change
        if (!password_verify($password, $user['password_hash'])) {
            $_SESSION['error'] = 'Nesprávné heslo pro potvrzení změny';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        //prevent username conflicts with other users
        $existing = User::findByUsername($newUsername);
        if ($existing && $existing['id'] !== $user['id']) {
            $_SESSION['error'] = 'Toto uživatelské jméno již používá někdo jiný';
            header('Location: ' . BASE_URL . '?r=profile');
            exit;
        }

        //save new username to database
        if (User::updateUsername($user['id'], $newUsername)) {
            $_SESSION['username'] = $newUsername; //update session for consistency
            $_SESSION['success'] = 'Uživatelské jméno bylo změněno';
        } else {
            $_SESSION['error'] = 'Chyba při změně uživatelského jména';
        }

        header('Location: ' . BASE_URL . '?r=profile');
        exit;
    }
}
