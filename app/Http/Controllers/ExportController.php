<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
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

        if (! file_exists($fullPath)) {
            abort(404, '文件不存在');
        }

        if (! str_starts_with($filePath, 'exports/')) {
            abort(403, '禁止访问');
        }

        $filename = basename($filePath);

        return response()->streamDownload(function () use ($fullPath) {
            $handle = fopen($fullPath, 'r');
            if ($handle) {
                while (! feof($handle)) {
                    echo fread($handle, 8192);
                }
                fclose($handle);
            }

            @unlink($fullPath);
        }, $filename, [
            'Content-Type' => mime_content_type($fullPath),
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function syncExport(Request $request): StreamedResponse
    {
        $token = $request->input('token');
        $data = Cache::get("sync_export_{$token}");

        if (! $data) {
            abort(404, '导出任务已过期或不存在');
        }

        Cache::forget("sync_export_{$token}");

        $sql = $data['sql'];
        $bindings = $data['bindings'];
        $modelClass = $data['model'];
        $eager = $data['eager'] ?? [];
        $columns = $data['columns'];
        $label = $data['label'] ?? '数据';
        $format = $data['format'];

        $totalRows = DB::select("SELECT COUNT(*) as cnt FROM ({$sql}) as t", $bindings)[0]->cnt ?? 0;

        if ($totalRows === 0) {
            abort(404, '没有符合条件的数据');
        }

        $timestamp = now()->format('Ymd_His');
        $filename = "{$label}_{$timestamp}.{$format}";

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($sql, $bindings, $modelClass, $eager, $columns) {
                $handle = fopen('php://output', 'w');
                fwrite($handle, "\xEF\xBB\xBF");
                fputcsv($handle, array_values($columns));

                $chunkSize = 1000;
                $offset = 0;

                do {
                    $rows = DB::select("{$sql} ORDER BY id LIMIT {$chunkSize} OFFSET {$offset}", $bindings);
                    foreach ($rows as $row) {
                        $model = new $modelClass;
                        $model->setRawAttributes((array) $row);
                        foreach ($eager as $relation) {
                            $model->load($relation);
                        }
                        $rowData = [];
                        foreach (array_keys($columns) as $col) {
                            $value = $model->{$col} ?? '';
                            if (is_array($value)) {
                                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                            }
                            $rowData[] = $value;
                        }
                        fputcsv($handle, $rowData);
                    }
                    $count = count($rows);
                    $offset += $chunkSize;
                } while ($count === $chunkSize);

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'export_xlsx_');
        $this->exportExcelFile($tempFile, $sql, $bindings, $modelClass, $eager, $columns, $label);

        return response()->streamDownload(function () use ($tempFile) {
            $handle = fopen($tempFile, 'r');
            if ($handle) {
                while (! feof($handle)) {
                    echo fread($handle, 8192);
                }
                fclose($handle);
            }
            @unlink($tempFile);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    protected function exportExcelFile(string $filePath, string $sql, array $bindings, string $modelClass, array $eager, array $columns, string $label): void
    {
        $zip = new \ZipArchive;
        if ($zip->open($filePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("无法创建 Excel 文件: {$filePath}");
        }

        $zip->addFromString('[Content_Types].xml', $this->getContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->getRelsXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->getWorkbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->getStylesXml());
        $zip->addFromString('xl/workbook.xml', $this->getWorkbookXml($label));

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetData>';

        $xml .= '<row r="1">';
        $colIndex = 0;
        foreach (array_values($columns) as $header) {
            $cellRef = $this->getColumnLetter($colIndex).'1';
            $xml .= '<c r="'.$cellRef.'" t="inlineStr"><is><t>'.htmlspecialchars($header).'</t></is></c>';
            $colIndex++;
        }
        $xml .= '</row>';

        $rowNum = 2;
        $chunkSize = 1000;
        $offset = 0;
        $colKeys = array_keys($columns);

        do {
            $rows = DB::select("{$sql} ORDER BY id LIMIT {$chunkSize} OFFSET {$offset}", $bindings);
            foreach ($rows as $row) {
                $model = new $modelClass;
                $model->setRawAttributes((array) $row);
                foreach ($eager as $relation) {
                    $model->load($relation);
                }
                $xml .= '<row r="'.$rowNum.'">';
                $colIndex = 0;
                foreach ($colKeys as $col) {
                    $cellRef = $this->getColumnLetter($colIndex).$rowNum;
                    $value = $model->{$col} ?? '';
                    if (is_array($value)) {
                        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                    }
                    $xml .= '<c r="'.$cellRef.'" t="inlineStr"><is><t>'.htmlspecialchars((string) $value).'</t></is></c>';
                    $colIndex++;
                }
                $xml .= '</row>';
                $rowNum++;
            }
            $count = count($rows);
            $offset += $chunkSize;
        } while ($count === $chunkSize);

        $xml .= '</sheetData>
</worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
        $zip->close();
    }

    protected function getColumnLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(65 + ($index % 26)).$letter;
            $index = intdiv($index, 26) - 1;
        }

        return $letter;
    }

    protected function getContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
    }

    protected function getRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
    }

    protected function getWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
    }

    protected function getWorkbookXml(string $sheetName): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="'.htmlspecialchars($sheetName).'" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';
    }

    protected function getStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font><b/></font>
        <font/>
    </fonts>
    <fills count="2">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
    </fills>
    <borders count="1">
        <border><left/><right/><top/><bottom/><diagonal/></border>
    </borders>
    <cellStyleXfs count="1">
        <xf/>
    </cellStyleXfs>
    <cellXfs count="2">
        <xf/>
        <xf fontId="0" applyFont="1"/>
    </cellXfs>
</styleSheet>';
    }
}
