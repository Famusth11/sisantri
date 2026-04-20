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

class RekapBulananSholatExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $data;
    protected $month;
    protected $kelas;
    protected $exportDate;
    protected $totalSantri;
    protected $totalKegiatan;
    protected $totalHadir;
    protected $totalIzin;
    protected $totalSakit;
    protected $totalAlpha;

    public function __construct($data, $month, $kelas, $stats)
    {
        $this->data = $data;
        $this->month = $month;
        $this->kelas = $kelas;
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
        
        
        $rows[] = ['REKAP BULANAN ABSENSI SHOLAT JAMAAH'];
        $rows[] = ['Periode', $this->month];
        if ($this->kelas) {
            $rows[] = ['Kelas', $this->kelas];
        }
        $rows[] = ['Dicetak', $this->exportDate];
        $rows[] = []; 
        
        
        $rows[] = ['No', 'Nama Santri', 'Kelas', 'Total Kegiatan', 'Hadir', 'Izin', 'Sakit', 'Alpha', 'Persentase (%)'];
        
        
        foreach ($this->data as $santri) {
            $rows[] = [
                $santri['no'],
                $santri['nama'],
                $santri['kelas'],
                $santri['total'],
                $santri['hadir'],
                $santri['izin'],
                $santri['sakit'],
                $santri['alpha'],
                $santri['persentase']
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
            'D' => 15,   
            'E' => 8,    
            'F' => 8,    
            'G' => 8,    
            'H' => 8,    
            'I' => 15,   
        ];
    }

    public function styles(Worksheet $sheet)
    {
        
        $headerRow = 5; 
        if ($this->kelas) $headerRow++;
        
        return [
            
            1 => ['font' => ['bold' => true, 'size' => 14]],
            
            
            $headerRow => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4a5568']
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                
                $headerRow = 5;
                if ($this->kelas) $headerRow++;
                
                
                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                
                $sheet->getStyle('A' . $headerRow . ':I' . $headerRow)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                
                $lastRow = count($this->data) + $headerRow;
                $sheet->getStyle('A' . $headerRow . ':I' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);
                
                
                $sheet->getStyle('D' . ($headerRow + 1) . ':I' . $lastRow)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}

