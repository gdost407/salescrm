<?php

namespace App\Actions;

use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;
use XMLWriter;
use ZipArchive;

class ExportReportWorkbook
{
    private const MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const REL = 'http://schemas.openxmlformats.org/package/2006/relationships';

    private const OFFICE_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /** @param array<string, string> $columns
     * @param  iterable<array<string, string>>  $rows
     */
    public function handle(array $columns, iterable $rows): string
    {
        $archivePath = $this->temporaryFile();
        $sheets = [];
        $zip = new ZipArchive;
        $opened = false;
        try {
            if ($zip->open($archivePath, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Unable to create the report workbook.');
            }
            $opened = true;
            $sheet = null;
            $rowNumber = 1048576;
            foreach ($this->withHeader($columns, $rows) as $values) {
                if ($rowNumber >= 1048576) {
                    if ($sheet) {
                        $this->finishSheet($sheet, $rowNumber, count($columns));
                    }
                    $path = $this->temporaryFile();
                    $sheets[] = $path;
                    $sheet = new XMLWriter;
                    if (! $sheet->openUri($path)) {
                        throw new RuntimeException('Unable to write the worksheet.');
                    }
                    $sheet->startDocument('1.0', 'UTF-8');
                    $sheet->startElementNS(null, 'worksheet', self::MAIN);
                    $sheet->startElement('sheetViews');
                    $sheet->startElement('sheetView');
                    $sheet->writeAttribute('workbookViewId', '0');
                    $sheet->startElement('pane');
                    $sheet->writeAttribute('ySplit', '1');
                    $sheet->writeAttribute('topLeftCell', 'A2');
                    $sheet->writeAttribute('state', 'frozen');
                    $sheet->endElement();
                    $sheet->endElement();
                    $sheet->endElement();
                    $sheet->startElement('sheetData');
                    $rowNumber = 1;
                    $this->writeRow($sheet, $columns, $rowNumber, false);
                    if (count($sheets) === 1) {
                        continue;
                    }
                }
                $this->writeRow($sheet, $values, ++$rowNumber, true);
            }
            $this->finishSheet($sheet, $rowNumber, count($columns));
            $contentTypes = '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
            $workbook = '<workbook xmlns="'.self::MAIN.'" xmlns:r="'.self::OFFICE_REL.'"><sheets>';
            $relationships = '<Relationships xmlns="'.self::REL.'">';
            foreach ($sheets as $index => $path) {
                $number = $index + 1;
                $contentTypes .= '<Override PartName="/xl/worksheets/sheet'.$number.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
                $workbook .= '<sheet name="Report '.$number.'" sheetId="'.$number.'" r:id="rId'.$number.'"/>';
                $relationships .= '<Relationship Id="rId'.$number.'" Type="'.self::OFFICE_REL.'/worksheet" Target="worksheets/sheet'.$number.'.xml"/>';
                if (! $zip->addFile($path, 'xl/worksheets/sheet'.$number.'.xml')) {
                    throw new RuntimeException('Unable to add the worksheet.');
                }
            }
            $parts = [
                '[Content_Types].xml' => $contentTypes.'</Types>',
                '_rels/.rels' => '<Relationships xmlns="'.self::REL.'"><Relationship Id="rId1" Type="'.self::OFFICE_REL.'/officeDocument" Target="xl/workbook.xml"/></Relationships>',
                'xl/workbook.xml' => $workbook.'</sheets></workbook>',
                'xl/_rels/workbook.xml.rels' => $relationships.'</Relationships>',
            ];
            foreach ($parts as $name => $xml) {
                if (! $zip->addFromString($name, '<?xml version="1.0" encoding="UTF-8"?>'.$xml)) {
                    throw new RuntimeException('Unable to assemble the report workbook.');
                }
            }
            if (! $zip->close()) {
                throw new RuntimeException('Unable to finish the report workbook.');
            }
            $opened = false;
            clearstatcache(true, $archivePath);

            return $archivePath;
        } catch (Throwable $exception) {
            if ($opened) {
                $zip->close();
            }
            if (is_file($archivePath)) {
                unlink($archivePath);
            }
            throw $exception;
        } finally {
            unset($sheet);
            foreach ($sheets as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }

    /** @param array<string, string> $columns
     * @param  iterable<array<string, string>>  $rows
     * @return \Generator<array<string, string>>
     */
    private function withHeader(array $columns, iterable $rows): \Generator
    {
        yield $columns;
        yield from $rows;
    }

    /** @param array<string, string> $values */
    private function writeRow(XMLWriter $sheet, array $values, int $number, bool $numeric): void
    {
        $sheet->startElement('row');
        $sheet->writeAttribute('r', (string) $number);
        $column = 0;
        foreach ($values as $field => $value) {
            $sheet->startElement('c');
            $sheet->writeAttribute('r', $this->columnName(++$column).$number);
            if ($numeric && in_array($field, SalesReport::NUMERIC, true) && preg_match('/^-?\\d+(\\.\\d+)?$/D', $value)) {
                $sheet->writeAttribute('t', 'n');
                $sheet->writeElement('v', $value);
            } else {
                if (mb_strlen($value) > 32767) {
                    throw ValidationException::withMessages(['export' => 'A report cell exceeds Excel’s 32,767 character limit. Narrow the report filters to export other records.']);
                }
                $sheet->writeAttribute('t', 'inlineStr');
                $sheet->startElement('is');
                $sheet->startElement('t');
                $sheet->writeAttribute('xml:space', 'preserve');
                $sheet->text(preg_replace('/[^\\x{9}\\x{A}\\x{D}\\x{20}-\\x{D7FF}\\x{E000}-\\x{FFFD}\\x{10000}-\\x{10FFFF}]/u', '', $value) ?? '');
                $sheet->endElement();
                $sheet->endElement();
            }
            $sheet->endElement();
        }
        $sheet->endElement();
        $sheet->flush();
    }

    private function finishSheet(XMLWriter $sheet, int $rows, int $columns): void
    {
        $sheet->endElement();
        $sheet->startElement('autoFilter');
        $sheet->writeAttribute('ref', 'A1:'.$this->columnName($columns).$rows);
        $sheet->endElement();
        $sheet->endElement();
        $sheet->endDocument();
        $sheet->flush();
    }

    private function columnName(int $column): string
    {
        $name = '';
        while ($column > 0) {
            $column--;
            $name = chr(65 + $column % 26).$name;
            $column = intdiv($column, 26);
        }

        return $name;
    }

    private function temporaryFile(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'crm-report-');
        if ($path === false) {
            throw new RuntimeException('Unable to allocate report storage.');
        }

        return $path;
    }
}
