<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\QueryExport;
use App\Infrastructure\Support\Traits\StreamDownload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    use StreamDownload;

    public function checkExportStatus(Request $request): JsonResponse
    {
        $user = Auth::guard('admin')->user();
        $notification = cache()->get("export_notification_{$user->id}");

        if (! $notification) {
            return response()->json([
                'status' => 'no_export',
                'message' => '暂无导出任务',
            ]);
        }

        if ($notification['success']) {
            cache()->forget("export_notification_{$user->id}");
        }

        return response()->json($notification);
    }

    public function downloadExport(Request $request, string $filePath): StreamedResponse
    {
        $fullPath = storage_path("app/{$filePath}");
        $realPath = realpath($fullPath);
        $allowedDir = realpath(storage_path('app/exports'));

        if (! $realPath || ! $allowedDir || ! str_starts_with($realPath, $allowedDir)) {
            abort(404, '文件不存在');
        }

        $filename = basename($realPath);

        return $this->streamFile($realPath, $filename, [
            'Content-Type' => mime_content_type($realPath),
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function syncExport(Request $request): StreamedResponse
    {
        $user = Auth::guard('admin')->user();
        $token = $request->input('token');
        $data = Cache::get("sync_export_{$user->id}:{$token}");

        if (! $data) {
            abort(404, '导出任务已过期或不存在');
        }

        Cache::forget("sync_export_{$user->id}:{$token}");

        $format = $data['format'];

        if (QueryExport::fromArray($data)->getTotalRows() === 0) {
            abort(404, '没有符合条件的数据');
        }

        $timestamp = now()->format('Ymd_His');
        $filename = "{$data['label']}_{$timestamp}.{$format}";

        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        $export = QueryExport::fromArray($data);

        if ($format === 'csv') {
            $export->exportToCsv($tempFile);
            $contentType = 'text/csv; charset=UTF-8';
        } else {
            $export->exportToExcel($tempFile);
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }

        return $this->streamFile($tempFile, $filename, [
            'Content-Type' => $contentType,
        ]);
    }
}
