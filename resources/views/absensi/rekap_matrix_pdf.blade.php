<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    @php
        $pageContentWidth = 287;
        $noColumnWidth = 8;
        $nameColumnWidth = 52;
        $pengampuColumnWidth = 38;
        $dayColumnWidth = ($pageContentWidth - $noColumnWidth - $nameColumnWidth - $pengampuColumnWidth) / max((int) $daysInMonth, 1);
        $tableWidth = $noColumnWidth + $nameColumnWidth + ((int) $daysInMonth * $dayColumnWidth) + $pengampuColumnWidth;
    @endphp
    <style>
        @page {
            size: A4 landscape;
            margin: 4mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111;
            font-size: 5.9pt;
            margin: 0;
        }
        .matrix-title {
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            line-height: 1.25;
            margin-bottom: 2mm;
            text-transform: uppercase;
        }
        .matrix-meta {
            width: 100%;
            margin-bottom: 1.5mm;
            font-weight: bold;
        }
        .matrix-meta td {
            border: 0;
            padding: 0 0 2mm;
        }
        .matrix-table {
            border-collapse: collapse;
            width: {{ number_format($tableWidth, 1, '.', '') }}mm;
            table-layout: fixed;
            border: 1px solid #111;
        }
        .matrix-table th,
        .matrix-table td {
            border: 0.7px solid #111;
            padding: 1px 1.5px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.08;
            height: 4.6mm;
        }
        .matrix-table th {
            background: #fff;
            font-weight: bold;
            font-size: 5.6pt;
            height: 5mm;
        }
        .col-no {
            width: {{ $noColumnWidth }}mm;
            font-size: 5.5pt;
        }
        .col-name {
            width: {{ $nameColumnWidth }}mm;
            text-align: left;
            font-size: 5.6pt;
            white-space: nowrap;
            overflow: hidden;
        }
        .col-day {
            width: {{ number_format($dayColumnWidth, 1, '.', '') }}mm;
            text-align: center;
            white-space: nowrap;
            padding-left: 0;
            padding-right: 0;
        }
        .col-pengampu {
            width: {{ $pengampuColumnWidth }}mm;
            text-align: left;
            font-size: 5.2pt;
            white-space: nowrap;
            overflow: hidden;
        }
        .status {
            font-weight: bold;
        }
        .legend {
            margin-top: 2mm;
            font-size: 6pt;
        }
    </style>
</head>
<body>
    <div class="matrix-title">
        <div>{{ $headerTitle }}</div>
        <div>ASRAMA SMK TAKHASSUS</div>
    </div>
    <table class="matrix-meta">
        <tr>
            <td>BULAN: {{ strtoupper($monthLabel) }}</td>
            <td style="text-align: right;">
                KELAS: {{ $kelasFilter !== '' ? $kelasFilter : 'SEMUA' }}
                @if($jenisKelaminFilter !== '')
                    | {{ strtoupper($jenisKelaminFilter) }}
                @endif
            </td>
        </tr>
    </table>
    <table class="matrix-table">
        <colgroup>
            <col class="col-no" style="width: {{ $noColumnWidth }}mm;">
            <col class="col-name" style="width: {{ $nameColumnWidth }}mm;">
            @for($day = 1; $day <= $daysInMonth; $day++)
                <col class="col-day" style="width: {{ number_format($dayColumnWidth, 1, '.', '') }}mm;">
            @endfor
            <col class="col-pengampu" style="width: {{ $pengampuColumnWidth }}mm;">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2" class="col-no">NO</th>
                <th rowspan="2" class="col-name">NAMA SANTRI</th>
                <th colspan="{{ $daysInMonth }}">TANGGAL</th>
                <th rowspan="2" class="col-pengampu">PENGABSEN</th>
            </tr>
            <tr>
                @for($day = 1; $day <= $daysInMonth; $day++)
                    <th class="col-day">{{ $day }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="col-name">{{ $row['nama'] }}</td>
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        <td class="status col-day">{{ $row['days'][$day] ?? '' }}</td>
                    @endfor
                    <td class="col-pengampu">{{ !empty($row['pengampu']) ? implode(', ', $row['pengampu']) : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $daysInMonth + 3 }}">Tidak ada data santri.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="legend">Keterangan: H = Hadir, I = Izin, S = Sakit, A = Alpa.</div>
</body>
</html>
