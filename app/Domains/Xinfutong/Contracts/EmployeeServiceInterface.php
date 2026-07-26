<?php

declare(strict_types=1);

namespace App\Domains\Xinfutong\Contracts;

use App\Domains\Xinfutong\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * 员工服务接口
 */
interface EmployeeServiceInterface
{
    public function create(array $data): Employee;

    public function update(Employee $employee, array $data): Employee;

    public function delete(Employee $employee): bool;

    public function findById(int $id): ?Employee;

    public function findByXinfutongId(string $xinfutongId): ?Employee;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function getByStatus(string $status): Collection;

    public function getByDepartment(string $department): Collection;

    public function search(string $keyword): Collection;
}
