<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domains\Auth\Repositories\AdminRepositoryInterface;
use App\Models\Admin;

class AdminRepository implements AdminRepositoryInterface
{
    public function __construct(
        private readonly Admin $model
    ) {}

    public function findById(int $id): ?Admin
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?Admin
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $data): Admin
    {
        return $this->model->create($data);
    }

    public function emailExists(string $email): bool
    {
        return $this->model->where('email', $email)->exists();
    }
}
