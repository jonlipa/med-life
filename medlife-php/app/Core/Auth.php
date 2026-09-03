<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\UserRepository;

final class Auth
{
    private ?array $cachedUser = null;

    public function __construct(private UserRepository $users)
    {
    }

    public function attempt(string $identifier, string $password): bool
    {
        $user = $this->validateCredentials($identifier, $password);
        if (!$user) {
            return false;
        }

        $this->login($user);

        return true;
    }

    public function validateCredentials(string $identifier, string $password): ?array
    {
        $user = $this->users->findByIdentifier($identifier);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        return $user;
    }

    public function login(array $user): void
    {
        Session::regenerate();
        Session::put('auth_user_id', (int) $user['id']);
        Session::forget('_csrf_token');
        $this->cachedUser = $user;
    }

    public function logout(): void
    {
        $this->cachedUser = null;
        Session::forget('_csrf_token');
        Session::regenerate();
        Session::destroy();
    }

    public function user(): ?array
    {
        if ($this->cachedUser !== null) {
            return $this->cachedUser;
        }

        $userId = Session::get('auth_user_id');
        if (!$userId) {
            return null;
        }

        $this->cachedUser = $this->users->findById((int) $userId);

        return $this->cachedUser;
    }

    public function id(): ?int
    {
        return $this->user()['id'] ?? null;
    }

    public function role(): ?string
    {
        return $this->user()['role'] ?? null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }
}
