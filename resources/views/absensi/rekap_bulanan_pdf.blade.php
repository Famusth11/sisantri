<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Bulanan Presensi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 5px;
        }
        h2 {
            text-align: center;
            font-size: 14px;
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: normal;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4a5568;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        td {
            text-align: center;
        }
        td.text-left {
            text-align: left;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-success {
            background-color: #48bb78;
            color: white;
        }
        .badge-warning {
            background-color: #ecc94b;
            color: #2d3748;
        }
        .badge-danger {
            background-color: #f56565;
            color: white;
        }
        .info {
            margin-bottom: 15px;
            font-size: 11px;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
            color: #718096;
        }
    </style>
</head>
<body>
    <h1>REKAP BULANAN PRESENSI {{ isset($jenis) ? strtoupper($jenis) : 'DINIYAH' }}</h1>
    <h2>Periode: {{ $month }}</h2>
    
    <div class="info">
        @if($kelas)
            <strong>Kelas:</strong> {{ $kelas }}<br>
        @endif
        @if($golongan)
            <strong>Golongan:</strong> {{ $golongan }}<br>
        @endif
        <strong>Total Santri:</strong> {{ count($data) }} orang
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Nama Santri</th>
                <th width="8%">Kelas</th>
                <th width="10%">Golongan</th>
                <th width="8%">Total</th>
                <th width="8%">Hadir</th>
                <th width="8%">Izin</th>
                <th width="8%">Sakit</th>
                <th width="8%">Alpha</th>
                <th width="12%">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ $item['nama'] }}</td>
                <td>{{ $item['kelas'] }}</td>
                <td>{{ $item['golongan'] }}</td>
                <td>{{ $item['total'] }}</td>
                <td>{{ $item['hadir'] }}</td>
                <td>{{ $item['izin'] }}</td>
                <td>{{ $item['sakit'] }}</td>
                <td>{{ $item['alpha'] }}</td>
                <td>
                    @php
                        $badgeClass = 'badge-danger';
                        if ($item['persentase'] >= 75) {
                            $badgeClass = 'badge-success';
                        } elseif ($item['persentase'] >= 50) {
                            $badgeClass = 'badge-warning';
                        }
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $item['persentase'] }}%</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 30px; color: #718096;">
                    <strong>Tidak ada data untuk filter yang dipilih</strong><br>
                    <small>Pastikan data sudah tersedia di periode yang dipilih</small>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->locale('id')->format('d F Y H:i') }} | 
        Sistem Informasi Santri
    </div>
</body>
</html>



