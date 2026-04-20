<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Santri - {{ $santri->id_santri }}</title>
    <style>
        @page {
            margin: 20mm;
            size: A4 portrait;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #007bff;
            font-size: 24px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .header p {
            color: #6c757d;
            font-size: 14px;
            margin: 0;
        }
        
        .qr-section {
            text-align: center;
            margin: 40px 0;
        }
        
        .qr-container {
            display: inline-block;
            padding: 20px;
            background: white;
            border: 3px solid #007bff;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
        }
        
        .qr-image {
            max-width: 200px;
            height: auto;
        }
        
        .qr-text {
            margin-top: 15px;
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            background: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        
        .santri-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
            border-left: 5px solid #28a745;
        }
        
        .santri-info h3 {
            color: #28a745;
            font-size: 18px;
            margin: 0 0 15px 0;
            font-weight: bold;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 40%;
            font-weight: bold;
            color: #495057;
            padding: 8px 0;
            vertical-align: top;
        }
        
        .info-value {
            display: table-cell;
            color: #212529;
            padding: 8px 0;
            vertical-align: top;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
        }
        
        .footer p {
            color: #6c757d;
            font-size: 12px;
            margin: 5px 0;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            color: white;
        }
        
        .badge-primary {
            background-color: #007bff;
        }
        
        .badge-secondary {
            background-color: #6c757d;
        }
        
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .instructions h4 {
            color: #856404;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        
        .instructions ul {
            margin: 0;
            padding-left: 20px;
            color: #856404;
        }
        
        .instructions li {
            margin: 5px 0;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header">
            <h1>QR CODE SANTRI</h1>
            <p>Sistem Informasi Santri - {{ config('app.name', 'Sistem Santri') }}</p>
        </div>
        
        
        <div class="qr-section">
            <div class="qr-container">
                <img src="data:image/png;base64,{{ $qrImage }}" alt="QR Code Santri" class="qr-image">
                <div class="qr-text">{{ $qrText }}</div>
            </div>
        </div>
        
        
        <div class="santri-info">
            <h3>Informasi Santri</h3>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">ID Santri:</div>
                    <div class="info-value"><strong>{{ $santri->id_santri }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Nama Lengkap:</div>
                    <div class="info-value">{{ $santri->nama ?? 'Tidak tersedia' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Kelas:</div>
                    <div class="info-value">{{ $santri->kelas ?? 'Tidak tersedia' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Golongan:</div>
                    <div class="info-value">{{ $santri->golongan ?? 'Tidak tersedia' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jenis Kelamin:</div>
                    <div class="info-value">
                        @if(isset($santri->jenis_kelamin))
                            <span class="badge {{ $santri->jenis_kelamin === 'Putra' ? 'badge-primary' : 'badge-secondary' }}">
                                {{ $santri->jenis_kelamin }}
                            </span>
                        @else
                            Tidak tersedia
                        @endif
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Pembina:</div>
                    <div class="info-value">{{ $santri[5] ?? 'Tidak tersedia' }}</div>
                </div>
            </div>
        </div>
        
        
        <div class="instructions">
            <h4>Cara Menggunakan QR Code:</h4>
            <ul>
                <li>Scan QR code ini menggunakan aplikasi scanner QR di smartphone</li>
                <li>QR code berisi informasi ID dan nama santri</li>
                <li>Gunakan untuk presensi atau verifikasi identitas santri</li>
                <li>Pastikan QR code tidak rusak atau terpotong saat dicetak</li>
            </ul>
        </div>
        
        
        <div class="footer">
            <p><strong>Dokumen ini dibuat secara otomatis oleh sistem</strong></p>
            <p>Dicetak pada: {{ $generatedAt }}</p>
            <p>Â© {{ date('Y') }} {{ config('app.name', 'Sistem Santri') }}. Semua hak dilindungi.</p>
        </div>
    </div>
</body>
</html>



