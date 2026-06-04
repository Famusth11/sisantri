<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapMatrixExport implements FromArray, WithColumnWidths, WithEvents, WithStyles
{
    public function __construct(protected array $data)
    {
    }

    public function array(): array
    {
        $daysInMonth = (int) $this->data['daysInMonth'];
        $rows = [
            [$this->data['headerTitle']],
            ['ASRAMA SMK TAKHASSUS'],
            ['Bulan', $this->data['monthLabel'], 'Kelas', $this->data['kelasFilter'] ?: 'Semua', 'Putra/Putri', $this->data['jenisKelaminFilter'] ?: 'Semua'],
            [],
        ];

        $header = ['NO', 'NAMA SANTRI'];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $header[] = $day;
        }
        $header[] = 'PENGABSEN';
        $rows[] = $header;

        foreach ($this->data['rows'] as $index => $row) {
            $line = [$index + 1, $row['nama']];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $line[] = $row['days'][$day] ?? '';
            }
            $line[] = !empty($row['pengampu']) ? implode(', ', $row['pengampu']) : '-';
            $rows[] = $line;
        }

        $rows[] = [];
        $rows[] = ['Keterangan', 'H = Hadir, I = Izin, S = Sakit, A = Alpa'];

        return $rows;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 6,
            'B' => 24,
        ];

        $daysInMonth = (int) $this->data['daysInMonth'];
        for ($i = 3; $i <= $daysInMonth + 2; $i++) {
            $widths[Coordinate::stringFromColumnIndex($i)] = 3.2;
        }
        $widths[Coordinate::stringFromColumnIndex($daysInMonth + 3)] = 20;

        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 13]],
            3 => ['font' => ['bold' => true, 'size' => 10]],
            5 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC400']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = Coordinate::stringFromColumnIndex(((int) $this->data['daysInMonth']) + 3);
                $lastDataRow = 5 + count($this->data['rows']);

                $sheet->mergeCells('A1:' . $lastColumn . '1');
                $sheet->mergeCells('A2:' . $lastColumn . '2');
                $sheet->getStyle('A1:' . $lastColumn . '2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A3:' . $lastColumn . '3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->freezePane('C6');
                $sheet->setAutoFilter('A5:' . $lastColumn . '5');

                $sheet->getStyle('A5:' . $lastColumn . $lastDataRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A5:' . $lastColumn . $lastDataRow)->getFont()->setSize(8);
                $sheet->getStyle('A5:' . $lastColumn . '5')->getFont()->setSize(9);
                $sheet->getStyle('A5:A' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C5:' . Coordinate::stringFromColumnIndex(((int) $this->data['daysInMonth']) + 2) . $lastDataRow)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B6:B' . $lastDataRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setWrapText(true);
                $sheet->getStyle($lastColumn . '6:' . $lastColumn . $lastDataRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setWrapText(true);

                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(5)->setRowHeight(22);
                for ($row = 6; $row <= $lastDataRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);
                $sheet->getPageMargins()
                    ->setTop(0.25)
                    ->setRight(0.2)
                    ->setLeft(0.2)
                    ->setBottom(0.25);
                $sheet->getHeaderFooter()->setOddFooter('&L' . $this->data['monthLabel'] . '&RHalaman &P/&N');
            },
        ];
    }
}
