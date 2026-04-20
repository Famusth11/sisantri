<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RekapBulananExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $data;
    protected $month;
    protected $kelas;
    protected $golongan;
    protected $exportDate;
    protected $totalSantri;
    protected $totalKegiatan;
    protected $totalHadir;
    protected $totalIzin;
    protected $totalSakit;
    protected $totalAlpha;

    public function __construct($data, $month, $kelas, $golongan, $stats)
    {
        $this->data = $data;
        $this->month = $month;
        $this->kelas = $kelas;
        $this->golongan = $golongan;
        $this->exportDate = $stats['exportDate'];
        $this->totalSantri = $stats['totalSantri'];
        $this->totalKegiatan = $stats['totalKegiatan'];
        $this->totalHadir = $stats['totalHadir'];
        $this->totalIzin = $stats['totalIzin'];
        $this->totalSakit = $stats['totalSakit'];
        $this->totalAlpha = $stats['totalAlpha'];
    }

    public function array(): array
    {
        $rows = [];
        
        
        $rows[] = ['REKAP BULANAN ABSENSI SANTRI'];
        $rows[] = ['Periode', $this->month];
        if ($this->kelas) {
            $rows[] = ['Kelas', $this->kelas];
        }
        if ($this->golongan) {
            $rows[] = ['Golongan', $this->golongan];
        }
        $rows[] = ['Dicetak', $this->exportDate];
        $rows[] = []; 
        
        
        $headerRow = 6 + ($this->kelas ? 1 : 0) + ($this->golongan ? 1 : 0);
        $rows[] = ['No', 'Nama Santri', 'Kelas', 'Golongan', 'Total Kegiatan', 'Hadir', 'Izin', 'Sakit', 'Alpha', 'Persentase (%)'];
        
        
        $no = 1;
        foreach ($this->data as $item) {
            $rows[] = [
                $no++,
                $item['nama'],
                $item['kelas'],
                $item['golongan'],
                $item['total'],
                $item['hadir'],
                $item['izin'],
                $item['sakit'],
                $item['alpha'],
                $item['persentase']
            ];
        }
        
        
        $rows[] = [];
        
        
        $rows[] = ['RINGKASAN'];
        $rows[] = ['Total Santri', $this->totalSantri];
        $rows[] = ['Total Kegiatan', $this->totalKegiatan];
        $rows[] = ['Total Hadir', $this->totalHadir];
        $rows[] = ['Total Izin', $this->totalIzin];
        $rows[] = ['Total Sakit', $this->totalSakit];
        $rows[] = ['Total Alpha', $this->totalAlpha];
        
        if ($this->totalKegiatan > 0) {
            $avgKehadiran = round($this->totalHadir / $this->totalKegiatan * 100, 1);
            $rows[] = ['Rata-rata Kehadiran', $avgKehadiran . '%'];
        }
        
        $rows[] = [];
        $rows[] = ['Sistem Informasi Santri - SISANTRI'];
        
        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,    
            'B' => 30,   
            'C' => 8,    
            'D' => 12,   
            'E' => 15,   
            'F' => 8,    
            'G' => 8,    
            'H' => 8,    
            'I' => 8,    
            'J' => 15,   
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $headerRow = 6 + ($this->kelas ? 1 : 0) + ($this->golongan ? 1 : 0);
        $dataStartRow = $headerRow + 1;
        $dataEndRow = $dataStartRow + count($this->data) - 1;
        
        return [
            
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            
            $headerRow => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $headerRow = 6 + ($this->kelas ? 1 : 0) + ($this->golongan ? 1 : 0);
                $dataStartRow = $headerRow + 1;
                $dataEndRow = $dataStartRow + count($this->data) - 1;
                
                
                $sheet->mergeCells('A1:J1');
                
                
                $tableRange = 'A' . $headerRow . ':J' . $dataEndRow;
                $event->sheet->getStyle($tableRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                
                $event->sheet->getStyle('A' . $dataStartRow . ':A' . $dataEndRow)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('C' . $dataStartRow . ':J' . $dataEndRow)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                
                $summaryStartRow = $dataEndRow + 2;
                $summaryRow = $summaryStartRow;
                $event->sheet->getStyle('A' . $summaryRow)
                    ->getFont()->setBold(true);
                
                
                for ($i = 1; $i <= 8; $i++) {
                    $event->sheet->getStyle('A' . ($summaryRow + $i))
                        ->getFont()->setBold(true);
                }
                
                
                $footerRow = $summaryRow + 9;
                $sheet->mergeCells('A' . $footerRow . ':J' . $footerRow);
                $event->sheet->getStyle('A' . $footerRow)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A' . $footerRow)
                    ->getFont()->setItalic(true);
            },
        ];
    }
}

