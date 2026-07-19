<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ExportAddressJob;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function exportAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,xlsx',
            'parent_id' => 'nullable|integer',
            'level' => 'nullable|string',
            'keyword' => 'nullable|string|max:100',
        ]);

        /** @var Admin $user */
        $user = Auth::guard('admin')->user();
        $userId = $user->id;

        // Check if user already has a pending export
        $existingNotification = cache()->get("export_notification_{$userId}");
        if ($existingNotification && $existingNotification['type'] === 'export' && ! $existingNotification['success']) {
            // Allow new export if previous failed
        }

        // Dispatch export job
        ExportAddressJob::dispatch(
            $validated,
            $validated['format'],
            $userId
        );

        return response()->json([
            'message' => '导出任务已提交，正在处理中...',
            'job_dispatched' => true,
        ]);
    }

    public function checkExportStatus(Request $request): JsonResponse
    {
        /** @var Admin $user */
        $user = Auth::guard('admin')->user();
        $userId = $user->id;

        $notification = cache()->get("export_notification_{$userId}");

        if (! $notification) {
            return response()->json([
                'status' => 'no_export',
                'message' => '暂无导出任务',
            ]);
        }

        // Clear the notification after reading
        if ($notification['success']) {
            cache()->forget("export_notification_{$userId}");
        }

        return response()->json($notification);
    }

    public function downloadExport(Request $request, string $filePath): StreamedResponse
    {
        $fullPath = storage_path("app/{$filePath}");

        if (! file_exists($fullPath)) {
            abort(404, '文件不存在');
        }

        // Security check: ensure the file is in the exports directory
        if (! str_starts_with($filePath, 'exports/')) {
            abort(403, '禁止访问');
        }

        $filename = basename($filePath);

        return response()->streamDownload(function () use ($fullPath) {
            echo file_get_contents($fullPath);
        }, $filename, [
            'Content-Type' => mime_content_type($fullPath),
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
