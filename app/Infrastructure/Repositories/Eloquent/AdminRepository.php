<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domains\Auth\Repositories\AdminRepositoryInterface;
use App\Models\Admin;

class AdminRepository extends BaseRepository implements AdminRepositoryInterface
{
    public function __construct(Admin $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id): ?Admin
    {
        return $this->find($id);
    }

    public function findByEmail(string $email): ?Admin
    {
        return $this->findBy('email', $email);
    }

    public function create(array $data): Admin
    {
        return parent::create($data);
    }

    public function emailExists(string $email): bool
    {
        return $this->exists(['email' => $email]);
    }
}
