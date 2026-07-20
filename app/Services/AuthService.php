<?php

declare(strict_types=1);

namespace App\Services;

use App\Domains\Auth\Data\AuthResult;
use App\Domains\Auth\Data\LoginData;
use App\Domains\Auth\Data\RegisterData;
use App\Domains\Auth\Events\AdminRegistered;
use App\Domains\Auth\Repositories\AdminRepositoryInterface;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function __construct(
        private readonly AdminRepositoryInterface $adminRepository
    ) {}

    public function register(array $data): AuthResult
    {
        $data = RegisterData::fromArray($data);

        if ($this->adminRepository->emailExists($data->email)) {
            throw ValidationException::withMessages([
                'email' => ['邮箱已注册'],
            ]);
        }

        $admin = $this->adminRepository->create($data->toArray());

        event(new AdminRegistered($admin));

        $token = JWTAuth::fromUser($admin);

        return new AuthResult(
            admin: $admin,
            token: $token,
            expiresIn: config('jwt.ttl') * 60,
        );
    }

    public function login(array $data): AuthResult
    {
        $data = LoginData::fromArray($data);

        $admin = $this->adminRepository->findByEmail($data->email);

        if (! $admin || ! Hash::check($data->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['邮箱或密码错误'],
            ]);
        }

        $token = JWTAuth::fromUser($admin);

        return new AuthResult(
            admin: $admin,
            token: $token,
            expiresIn: config('jwt.ttl') * 60,
        );
    }

    public function logout(Admin $admin): void
    {
        try {
            JWTAuth::invalidate();
        } catch (Throwable) {
            // Token missing or already invalid — safe no-op.
        }
    }

    public function refresh(Admin $admin): string
    {
        return JWTAuth::fromUser($admin);
    }
}
