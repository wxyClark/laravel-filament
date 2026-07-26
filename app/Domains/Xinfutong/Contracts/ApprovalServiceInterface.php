<?php

declare(strict_types=1);

namespace App\Domains\Xinfutong\Contracts;

use App\Domains\Xinfutong\Models\ApprovalRequest;

/**
 * 审批服务接口
 */
interface ApprovalServiceInterface
{
    public function create(array $data): ApprovalRequest;

    public function update(ApprovalRequest $approval, array $data): ApprovalRequest;

    public function delete(ApprovalRequest $approval): bool;

    public function submit(ApprovalRequest $approval): ApprovalRequest;

    public function withdraw(ApprovalRequest $approval): ApprovalRequest;

    public function queryStatus(ApprovalRequest $approval): ApprovalRequest;

    public function findById(int $id): ?ApprovalRequest;

    public function findByApprovalNo(string $approvalNo): ?ApprovalRequest;
}
