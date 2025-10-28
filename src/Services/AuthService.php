<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    private const USERS_FILE = __DIR__ . '/../../data/users.json';

    public function __construct()
    {
        $this->ensureDataDirectory();
        $this->initializeDemoUser();
    }

    private function ensureDataDirectory(): void
    {
        $dataDir = dirname(self::USERS_FILE);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
        }
    }

    private function initializeDemoUser(): void
    {
        $users = $this->getUsers();

        // Check if demo user exists
        $demoExists = false;
        foreach ($users as $user) {
            if ($user['email'] === 'test@example.com') {
                $demoExists = true;
                break;
            }
        }

        // Create demo user if it doesn't exist
        if (!$demoExists) {
            $demoUser = new User(
                'test@example.com',
                'Test User',
                password_hash('test123', PASSWORD_DEFAULT)
            );
            $users[] = $demoUser->toArray();
            $this->saveUsers($users);
        }
    }

    private function getUsers(): array
    {
        if (!file_exists(self::USERS_FILE)) {
            return [];
        }

        $content = file_get_contents(self::USERS_FILE);
        return json_decode($content, true) ?? [];
    }

    private function saveUsers(array $users): void
    {
        file_put_contents(self::USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
    }

    public function signup(string $email, string $name, string $password): ?User
    {
        $users = $this->getUsers();

        // Check if user already exists
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return null;
            }
        }

        // Create new user
        $newUser = new User($email, $name, password_hash($password, PASSWORD_DEFAULT));
        $users[] = $newUser->toArray();
        $this->saveUsers($users);

        return $newUser;
    }

    public function login(string $email, string $password): ?User
    {
        $users = $this->getUsers();

        foreach ($users as $userData) {
            if ($userData['email'] === $email && password_verify($password, $userData['password'])) {
                return User::fromArray($userData);
            }
        }

        return null;
    }

    public function createSession(User $user): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $sessionData = [
            'user' => $user->toPublicArray(),
            'token' => $token,
            'timestamp' => time()
        ];

        $_SESSION['user'] = $sessionData['user'];
        $_SESSION['token'] = $token;

        return $sessionData;
    }

    public function validateSession(array $sessionData): bool
    {
        if (!isset($sessionData['user']) || !isset($sessionData['token'])) {
            return false;
        }

        // Validate user exists
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['id'] === $sessionData['user']['id'] && $user['email'] === $sessionData['user']['email']) {
                return true;
            }
        }

        return false;
    }

    public function restoreSession(array $sessionData): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user'] = $sessionData['user'];
        $_SESSION['token'] = $sessionData['token'];
    }

    public function isAuthenticated(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['user']) && isset($_SESSION['token']);
    }

    public function getCurrentUser(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['user'] ?? null;
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();
        session_destroy();
    }
}
