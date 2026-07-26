<?php

declare(strict_types=1);

namespace App\Domains\Xinfutong\Services;

use App\Domains\Xinfutong\Contracts\SyncRepositoryInterface;
use App\Domains\Xinfutong\Contracts\SyncServiceInterface;
use App\Domains\Xinfutong\Models\SyncLog;
use App\Infrastructure\Services\BaseService;

class SyncService extends BaseService implements SyncServiceInterface
{
    public function __construct(
        private readonly SyncRepositoryInterface $syncRepository,
        private readonly XinfutongService $xinfutongService
    ) {}

    public function syncEmployees(): SyncLog
    {
        return $this->xinfutongService->syncEmployees();
    }

    public function syncIncremental(): SyncLog
    {
        return $this->xinfutongService->syncEmployees();
    }

    public function getSyncStatus(): ?SyncLog
    {
        return $this->syncRepository->getRunning()
            ?? $this->syncRepository->getPending();
    }

    public function getLastSyncTime(): ?string
    {
        $log = $this->syncRepository->getRunning()
            ?? $this->syncRepository->getPending();

        return $log?->completed_at?->format('Y-m-d H:i:s');
    }
}
