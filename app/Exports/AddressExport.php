<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Database\Eloquent\Builder;

class AddressExport
{
    protected Builder $query;

    protected array $columns;

    public function __construct(array $filters = [])
    {
        // 使用共享的查询构建器
        $service = app(AddressService::class);
        $this->query = $service->buildQuery($filters);

        $this->columns = [
            'id' => 'ID',
            'name' => '名称',
            'code' => '行政区划代码',
            'level' => '层级',
            'level_num' => '层级编号',
            'parent_name' => '上级地址',
            'pinyin' => '拼音',
            'merge_path' => '合并路径',
            'sort' => '排序',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    public function getTotalRows(): int
    {
        return (clone $this->query)->count();
    }

    /**
     * Export data in chunks to CSV file.
     */
    public function exportToCsv(string $filePath): void
    {
        $handle = fopen($filePath, 'w');

        // Write BOM for Excel compatibility
        fwrite($handle, "\xEF\xBB\xBF");

        // Write header
        fputcsv($handle, array_values($this->columns));

        // Write data in chunks to save memory
        $chunkSize = 1000;
        $offset = 0;

        do {
            $rows = (clone $this->query)
                ->orderBy('id')
                ->offset($offset)
                ->limit($chunkSize)
                ->get();

            foreach ($rows as $row) {
                fputcsv($handle, $this->formatRow($row));
            }

            $count = $rows->count();
            $offset += $chunkSize;
        } while ($count === $chunkSize);

        fclose($handle);
    }

    /**
     * Export data in chunks to Excel (xlsx) file using simple XML approach.
     */
    public function exportToExcel(string $filePath): void
    {
        $zip = new \ZipArchive;
        if ($zip->open($filePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("无法创建 Excel 文件: {$filePath}");
        }

        // Content types
        $zip->addFromString('[Content_Types].xml', $this->getContentTypesXml());

        // Relationships
        $zip->addFromString('_rels/.rels', $this->getRelsXml());

        // Workbook relationships
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->getWorkbookRelsXml());

        // Styles
        $zip->addFromString('xl/styles.xml', $this->getStylesXml());

        // Workbook
        $zip->addFromString('xl/workbook.xml', $this->getWorkbookXml());

        // Sheet data
        $sheetContent = $this->getSheetXml();
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetContent);

        $zip->close();
    }

    protected function formatRow(Address $row): array
    {
        return [
            $row->id,
            $row->name,
            $row->code,
            $this->getLevelLabel($row->level),
            $row->level_num,
            $row->parent->name ?? '',
            $row->pinyin ?? '',
            $row->full_path,
            $row->sort ?? 0,
            $row->created_at?->format('Y-m-d H:i:s') ?? '',
            $row->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    protected function getLevelLabel(string $level): string
    {
        return match ($level) {
            'province' => '省级',
            'city' => '地级',
            'district' => '县级',
            'township' => '街道',
            default => $level,
        };
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

    protected function getWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="地址数据" sheetId="1" r:id="rId1"/>
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

    protected function getSheetXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetData>';

        // Header row
        $xml .= '<row r="1">';
        $colIndex = 0;
        foreach (array_values($this->columns) as $header) {
            $cellRef = $this->getColumnLetter($colIndex).'1';
            $xml .= '<c r="'.$cellRef.'" t="inlineStr"><is><t>'.htmlspecialchars($header).'</t></is></c>';
            $colIndex++;
        }
        $xml .= '</row>';

        // Data rows
        $rowNum = 2;
        $chunkSize = 1000;
        $offset = 0;

        do {
            $rows = (clone $this->query)
                ->orderBy('id')
                ->offset($offset)
                ->limit($chunkSize)
                ->get();

            foreach ($rows as $row) {
                $xml .= '<row r="'.$rowNum.'">';
                $data = $this->formatRow($row);
                $colIndex = 0;
                foreach ($data as $value) {
                    $cellRef = $this->getColumnLetter($colIndex).$rowNum;
                    $xml .= '<c r="'.$cellRef.'" t="inlineStr"><is><t>'.htmlspecialchars((string) $value).'</t></is></c>';
                    $colIndex++;
                }
                $xml .= '</row>';
                $rowNum++;
            }

            $count = $rows->count();
            $offset += $chunkSize;
        } while ($count === $chunkSize);

        $xml .= '</sheetData>
</worksheet>';

        return $xml;
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
}
