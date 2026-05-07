<?php

namespace App\Core;

abstract class BaseController
{
    //data passed to templates
    protected array $data = [];
    //page meta defaults
    protected array $meta = [
        'title' => '',
        'keywords' => '',
        'description' => '',
    ];

    //each controller implements this to handle request
    abstract public function handle(array $params = []): void;

    //render twig template with provided data
    protected function render(string $template, array $data = []): void
    {
        //inject global auth state
        $authData = $this->getAuthData();

        //merge meta, auth, controller data, and provided data
        $payload = array_merge($this->meta, $authData, $this->data, $data);
        echo View::twig()->render($template, $payload);
    }

    //get current auth state from session
    protected function getAuthData(): array
    {
        if (isset($_SESSION['user_id'])) {
            //user is logged in
            return [
                'isLoggedIn' => true,
                'userId' => $_SESSION['user_id'],
                'userName' => $_SESSION['username'] ?? '',
                'userAvatar' => $_SESSION['user_avatar'] ?? null,
                'userInitials' => $this->getInitials($_SESSION['username'] ?? ''),
                'userRole' => $_SESSION['user_role'] ?? 'user',
            ];
        }
        //user is not logged in
        return [
            'isLoggedIn' => false,
            'userId' => null,
            'userName' => '',
            'userAvatar' => null,
            'userInitials' => '',
            'userRole' => 'user',
        ];
    }

    //get user initials from username
    private function getInitials(string $name): string
    {
        if (empty($name)) {
            return 'U';
        }
        $parts = explode(' ', $name);
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 1));
    }

    //redirect to another path and stop execution
    protected function redirect(string $path): void
    {
        header('Location: ' . $this->sanitizeLocation($path));
        header('Connection: close');
        exit;
    }

    //basic location sanitization to avoid header injection
    private function sanitizeLocation(string $path): string
    {
        //remove any crlf and ensure it is an absolute path
        $clean = preg_replace('/[\r\n]+/', '', $path ?? '');
        if ($clean === '' || $clean[0] !== '/') {
            $clean = '/' . ltrim($clean, '/');
        }
        return $clean;
    }
}
