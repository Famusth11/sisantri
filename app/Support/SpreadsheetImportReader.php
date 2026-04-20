<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetImportReader
{
    public function read(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray('', true, true, false);

        if (empty($rows)) {
            return [];
        }

        $headings = array_shift($rows);
        $normalizedHeadings = array_map(fn ($heading) => $this->normalizeHeading($heading), $headings);

        $preparedRows = [];

        foreach ($rows as $row) {
            $normalizedRow = [];
            $hasValue = false;

            foreach ($normalizedHeadings as $columnIndex => $heading) {
                if ($heading === '') {
                    continue;
                }

                $value = $this->normalizeCellValue($row[$columnIndex] ?? null);
                if ($value !== null) {
                    $hasValue = true;
                }

                $normalizedRow[$heading] = $value;
            }

            if ($hasValue) {
                $preparedRows[] = $normalizedRow;
            }
        }

        return $preparedRows;
    }

    protected function normalizeHeading($heading): string
    {
        return (string) Str::of((string) $heading)
            ->trim()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/u', '_')
            ->trim('_');
    }

    protected function normalizeCellValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
