@extends('layouts.app')

@section('content')
<style>
    :root {
        --brand-dark: #0f5c4d;
        --brand: #1f7a68;
        --brand-soft: #7ab8a6;
        --line-soft: #cfe5dc;
        --surface-soft: #eef6f2;
        --accent-dark: #2f8f6b;
        --accent: #5ca88a;
        --accent-soft: #a9d1c1;
        --brand-gradient: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
        --surface-gradient: linear-gradient(135deg, #dff2ea 0%, #eef6f2 100%);
        --accent-gradient: linear-gradient(135deg, #2f8f6b 0%, #5ca88a 100%);
    }

    .page-header {
        background: var(--brand-gradient);
        color: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 8px 24px rgba(15, 92, 77, 0.25);
        border: 1px solid rgba(207, 229, 220, 0.6);
    }

    .scanner-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(15, 92, 77, 0.08);
        overflow: hidden;
        border: 1px solid rgba(207, 229, 220, 0.5);
        content-visibility: auto;
        contain-intrinsic-size: 520px;
    }

    .scanner-header {
        background: var(--accent-gradient);
        color: white;
        padding: 20px;
        font-weight: 600;
        border: none;
    }

    .video-container {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(15, 92, 77, 0.1);
        border: 2px solid var(--line-soft);
        background: #000;
        min-height: 400px;
    }

    #preview {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #000;
        filter: contrast(1.2) brightness(1.1);
    }

    .video-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(15, 92, 77, 0.88);
        color: white;
        padding: 20px;
        border-radius: 16px;
        text-align: center;
        z-index: 10;
        backdrop-filter: blur(10px);
    }

    .page-btn {
        border-radius: 12px;
        padding: 14px 32px;
        font-weight: 500;
        text-transform: none;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        font-size: 0.95rem;
    }

    .page-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(15, 92, 77, 0.25);
    }

    .btn-primary {
        background: var(--brand-gradient);
        box-shadow: 0 4px 12px rgba(15, 92, 77, 0.25);
    }

    .btn-primary:hover {
        background: var(--surface-gradient);
        color: #0a463b;
    }

    .btn-info {
        background: var(--accent-gradient);
        color: white;
        box-shadow: 0 4px 12px rgba(47, 143, 107, 0.25);
    }

    .btn-info:hover {
        background: var(--accent-gradient);
        opacity: 0.9;
    }

    .status-card {
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 12px rgba(15, 92, 77, 0.1);
        border-left: 4px solid var(--brand-dark);
    }

    .santri-list {
        max-height: 400px;
        overflow-y: auto;
        border-radius: 16px;
        padding: 10px;
        content-visibility: auto;
        contain-intrinsic-size: 420px;
    }

    .santri-list::-webkit-scrollbar {
        width: 8px;
    }

    .santri-list::-webkit-scrollbar-track {
        background: var(--surface-soft);
        border-radius: 10px;
    }

    .santri-list::-webkit-scrollbar-thumb {
        background: var(--brand-soft);
        border-radius: 10px;
    }
    .santri-search {
        border-bottom: 1px solid rgba(207, 229, 220, 0.8);
        background: #fff;
    }

    .santri-item {
        border: none;
        border-bottom: 1px solid var(--line-soft);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px;
        margin-bottom: 5px;
        padding: 12px !important;
        content-visibility: auto;
        contain-intrinsic-size: 72px;
    }

    .santri-item:hover {
        background: var(--surface-gradient);
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(15, 92, 77, 0.15);
    }

    .qr-badge {
        background: var(--brand-gradient);
        color: white;
        padding: 5px 12px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(15, 92, 77, 0.2);
    }

    .santri-item.highlighted {
        background: var(--brand-gradient) !important;
        color: white !important;
        transform: scale(1.02);
        box-shadow: 0 8px 24px rgba(15, 92, 77, 0.3);
        border-radius: 12px;
        margin: 8px 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .santri-item.highlighted .text-muted {
        color: rgba(255, 255, 255, 0.9) !important;
    }

    .santri-item.highlighted .qr-badge {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.4);
    }

    .form-control, .form-select {
        border-radius: 12px;
        border: 2px solid var(--surface-soft);
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--brand-dark);
        box-shadow: 0 0 0 0.2rem rgba(15, 92, 77, 0.18);
    }

    .card-header.bg-success {
        background: var(--brand-gradient) !important;
        border: none;
    }

    .card-header.bg-warning {
        background: var(--surface-gradient) !important;
        color: #0a463b !important;
        border-bottom: 1px solid rgba(207, 229, 220, 0.45);
    }

    @media (max-width: 991.98px) {
        .container-fluid {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .page-header {
            padding: 1.15rem;
            margin-bottom: 1rem;
        }

        .page-header__controls {
            text-align: left !important;
        }

        .page-header__controls .form-label {
            margin-top: 0.5rem;
        }

        .scanner-card .card-body {
            padding: 1rem !important;
        }

        .page-btn {
            padding: 0.9rem 1rem;
        }
    }

    @media (max-width: 767.98px) {
        .page-header h1.display-5 {
            font-size: 1.45rem;
        }

        .page-header .lead {
            font-size: 0.98rem;
        }

        .video-container {
            min-height: 260px;
        }

        #preview {
            min-height: 260px;
            height: 260px !important;
        }

        .video-overlay {
            width: calc(100% - 2rem);
            padding: 1rem;
        }

        .santri-list {
            max-height: 340px;
        }

        .santri-item:hover {
            transform: none;
        }
    }

    @media (max-width: 575.98px) {
        .page-header {
            border-radius: 1rem;
        }

        .scanner-header,
        .card-header.bg-success,
        .card-header.bg-warning {
            padding: 1rem;
        }

        .santri-item {
            padding: 0.9rem !important;
        }

        .qr-badge {
            font-size: 0.78rem;
            padding: 0.35rem 0.65rem;
        }
    }
</style>

<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-pray me-3"></i>
                    Presensi Sholat Berjamaah
                </h1>
                <p class="lead mb-0">Scan QR atau input manual untuk mencatat kehadiran sholat.</p>
            </div>
            <div class="col-md-4 text-end page-header__controls">
                <label class="form-label fw-bold mb-2">Pilih Jenis Sholat:</label>
                <select id="jenisSholatHeader" class="form-select form-select-lg">
                    @foreach($jenisSholatOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                <div class="mt-2">
                    <label class="form-label fw-bold mb-2">Tanggal Presensi:</label>
                        <input type="date" id="tanggalAbsen" class="form-control" value="{{ now('Asia/Jakarta')->format('Y-m-d') }}">
                </div>
                <form method="GET" action="{{ route('absensi.sholat') }}" class="mt-2">
                    <label class="form-label fw-bold mb-2">Data Perkelas:</label>
                    <select name="kelas" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach(['10', '11', '12'] as $kelasOption)
                            <option value="{{ $kelasOption }}" {{ (string) ($kelasFilter ?? '') === $kelasOption ? 'selected' : '' }}>
                                Kelas {{ $kelasOption }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card scanner-card">
                <div class="scanner-header">
                    <h4 class="mb-0">
                        <i class="fas fa-qrcode me-2"></i>
                        QR Code Scanner
                    </h4>
                </div>
                <div class="card-body p-4">
                    <div class="video-container mb-4">
                        <video id="preview" width="100%" height="400" style="display: block; background: #000; object-fit: contain;"></video>
                        <div id="videoOverlay" class="video-overlay" style="display: none;">
                            <i class="fas fa-camera fa-lg mb-2"></i>
                            <p class="mb-0">Memulai kamera...</p>
                        </div>
                    </div>

                    <div id="scannerStatus" class="alert alert-info status-card mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="statusText">Memulai kamera...</span>
                    </div>

                    <div class="small text-muted mb-4">
                        Arahkan kamera ke QR santri. Jika kamera belum aktif, gunakan tombol refresh scanner.
                    </div>

                    <div id="result" class="mb-4"></div>

                    <div class="card status-card">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-keyboard me-2"></i>
                                Input Manual
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="manualId" class="form-label">ID Santri</label>
                                    <input type="text" id="manualId" class="form-control" placeholder="Masukkan ID Santri (contoh: 231201)">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button id="refreshScanner" class="btn btn-info page-btn w-100">
                                        <i class="fas fa-sync me-2"></i>
                                        Refresh Scanner
                                    </button>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button id="manualSubmit" class="btn btn-primary page-btn">
                                    <i class="fas fa-check me-2"></i>
                                    Absen Manual
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card scanner-card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Data Santri ({{ count($santriList) }})
                        @if(!empty($kelasFilter))
                            - Kelas {{ $kelasFilter }}
                        @endif
                    </h5>
                </div>
                <div class="card-body santri-search p-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text"
                               id="searchSantriSholat"
                               class="form-control"
                               placeholder="Cari ID, nama, kelas, atau golongan...">
                    </div>
                    <small class="text-muted d-block mt-2">
                        Menampilkan <span id="santriSholatResultCount">{{ count($santriList) }}</span> dari {{ count($santriList) }} data
                    </small>
                </div>
                <div class="card-body p-0">
                    <div class="santri-list">
                        @foreach($santriList as $santri)
                            <div class="santri-item p-3"
                                 data-id="{{ strtolower($santri->id_santri) }}"
                                 data-santri-id="{{ $santri->id_santri }}"
                                 data-nama="{{ strtolower($santri->nama) }}"
                                 data-kelas="{{ strtolower($santri->kelas) }}"
                                 data-golongan="{{ strtolower($santri->golongan) }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $santri->nama }}</h6>
                                        <small class="text-muted">{{ $santri->kelas }} - {{ $santri->golongan }}</small>
                                    </div>
                                    <span class="qr-badge">{{ $santri->id_santri }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const santriLookup = @json(collect($santriList)->mapWithKeys(function ($santri) {
        return [(string) $santri->id_santri => (string) $santri->nama];
    })->all());
    const storeSholatUrl = '{{ route("absensi.storeSholat") }}';
    const csrfToken = '{{ csrf_token() }}';

    let qrScanner = null;
    let zxingReader = null;
    let mediaStream = null;
    let barcodeDetector = null;
    let scanAnimationFrame = null;
    let isScanning = false;
    let currentCamera = 'environment';
    let lastScanAt = 0;
    let fallbackLibrariesLoaded = false;
    let highlightedSantriItem = null;
    const scanCooldownMs = 2500;
    const santriItemMap = new Map();

    document.addEventListener('DOMContentLoaded', () => {
        setupEventListeners();
        initializeScanners();
    });

    async function initializeScanners() {
        try {
            barcodeDetector = await createBarcodeDetector();

            if (!barcodeDetector) {
                await ensureFallbackScannerLibraries();
            }

            await startCamera();
        } catch (error) {
            console.error('Failed to initialize scanner:', error);
            updateStatus(error.message || 'Gagal inisialisasi scanner.', 'danger');
            showVideoOverlay('Scanner belum aktif. Pastikan izin kamera diberikan lalu tekan Refresh Scanner.');
        }
    }

    async function ensureFallbackScannerLibraries() {
        if (fallbackLibrariesLoaded) {
            return;
        }

        await loadScript('https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js');
        await loadScript('https://unpkg.com/qr-scanner@1.4.2/qr-scanner.umd.min.js');
        fallbackLibrariesLoaded = true;
    }

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            const existingScript = document.querySelector(`script[src="${src}"]`);
            if (existingScript) {
                if (existingScript.dataset.loaded === 'true') {
                    resolve();
                    return;
                }

                existingScript.addEventListener('load', () => resolve(), { once: true });
                existingScript.addEventListener('error', () => reject(new Error(`Gagal memuat script: ${src}`)), { once: true });
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.addEventListener('load', () => {
                script.dataset.loaded = 'true';
                resolve();
            }, { once: true });
            script.addEventListener('error', () => reject(new Error(`Gagal memuat script: ${src}`)), { once: true });
            document.body.appendChild(script);
        });
    }

    function debounce(callback, delay = 150) {
        let timeoutId = null;

        return (...args) => {
            window.clearTimeout(timeoutId);
            timeoutId = window.setTimeout(() => callback(...args), delay);
        };
    }

    async function createBarcodeDetector() {
        if (!('BarcodeDetector' in window)) {
            return null;
        }

        try {
            const formats = await window.BarcodeDetector.getSupportedFormats();
            if (!formats.includes('qr_code')) {
                return null;
            }

            return new window.BarcodeDetector({ formats: ['qr_code'] });
        } catch (error) {
            console.warn('BarcodeDetector unavailable:', error);
            return null;
        }
    }

    function setupEventListeners() {
        document.getElementById('manualSubmit').addEventListener('click', () => {
            const manualId = document.getElementById('manualId').value.trim();

            if (!manualId) {
                showResult('Masukkan ID santri untuk presensi manual.', 'warning');
                return;
            }

            processQRResult(manualId);
            document.getElementById('manualId').value = '';
        });

        document.getElementById('manualId').addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                document.getElementById('manualSubmit').click();
            }
        });

        document.getElementById('refreshScanner').addEventListener('click', () => {
            restartCamera();
        });

        const searchInput = document.getElementById('searchSantriSholat');
        const resultCount = document.getElementById('santriSholatResultCount');
        const santriItems = Array.from(document.querySelectorAll('.santri-item'));

        santriItems.forEach((item) => {
            const santriId = item.getAttribute('data-santri-id');
            if (santriId) {
                santriItemMap.set(santriId, item);
            }
        });

        if (searchInput) {
            const applySearch = function () {
                const searchTerm = this.value.toLowerCase().trim();
                let visibleCount = 0;

                santriItems.forEach((item) => {
                    const id = item.getAttribute('data-id') || '';
                    const nama = item.getAttribute('data-nama') || '';
                    const kelas = item.getAttribute('data-kelas') || '';
                    const golongan = item.getAttribute('data-golongan') || '';

                    const matches = !searchTerm
                        || id.includes(searchTerm)
                        || nama.includes(searchTerm)
                        || kelas.includes(searchTerm)
                        || golongan.includes(searchTerm);

                    item.style.display = matches ? '' : 'none';

                    if (matches) {
                        visibleCount++;
                    }
                });

                if (resultCount) {
                    resultCount.textContent = visibleCount;
                }
            };

            searchInput.addEventListener('input', debounce(applySearch.bind(searchInput), 120));
        }
    }

    async function startCamera() {
        const video = document.getElementById('preview');

        try {
            stopCamera({ silent: true });
            showVideoOverlay('Meminta izin kamera...');
            updateStatus('Memulai kamera...', 'info');

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('Browser ini belum mendukung akses kamera.');
            }

            const hostname = window.location.hostname;
            const isLocalhost = ['localhost', '127.0.0.1'].includes(hostname);
            if (!window.isSecureContext && !isLocalhost) {
                throw new Error('Kamera hanya bisa dipakai di HTTPS atau localhost.');
            }

            mediaStream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: {
                    facingMode: { ideal: currentCamera },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            });

            video.srcObject = mediaStream;
            video.setAttribute('playsinline', '');
            video.setAttribute('autoplay', '');
            video.setAttribute('muted', '');
            await video.play();

            isScanning = true;
            hideVideoOverlay();

            if (barcodeDetector) {
                updateStatus('Kamera aktif. Scanner QR siap digunakan.', 'success');
                startNativeScanLoop();
                return;
            }

            if (typeof ZXing !== 'undefined' && ZXing.BrowserMultiFormatReader) {
                await startZXingScanner(video);
                return;
            }

            if (typeof QrScanner !== 'undefined') {
                await startQrScanner(video);
                return;
            }

            updateStatus('Kamera aktif, tetapi scanner QR otomatis tidak tersedia. Gunakan input manual.', 'warning');
        } catch (error) {
            console.error('Camera start error:', error);
            stopCamera({ silent: true });
            showVideoOverlay('Kamera tidak dapat diakses. Pastikan izin kamera diberikan lalu tekan Refresh Scanner.');
            throw error;
        }
    }

    function startNativeScanLoop() {
        cancelNativeScanLoop();

        const scan = async () => {
            if (!isScanning || !barcodeDetector) {
                return;
            }

            const video = document.getElementById('preview');
            const readyState = video.readyState >= HTMLMediaElement.HAVE_ENOUGH_DATA;

            if (readyState) {
                try {
                    const codes = await barcodeDetector.detect(video);
                    if (codes.length > 0) {
                        handleDetectedCode(codes[0].rawValue || '');
                    }
                } catch (error) {
                    console.warn('Native QR detect warning:', error);
                }
            }

            if (isScanning) {
                scanAnimationFrame = window.requestAnimationFrame(scan);
            }
        };

        scanAnimationFrame = window.requestAnimationFrame(scan);
    }

    async function startZXingScanner(video) {
        zxingReader = new ZXing.BrowserMultiFormatReader();
        const devices = await navigator.mediaDevices.enumerateDevices();
        const videoInputs = devices.filter((device) => device.kind === 'videoinput');

        let deviceId;
        const preferredDevice = videoInputs.find((device) => {
            const label = (device.label || '').toLowerCase();
            return label.includes('back') || label.includes('rear') || label.includes('environment');
        });

        if (preferredDevice) {
            deviceId = preferredDevice.deviceId;
        } else if (videoInputs[0]) {
            deviceId = videoInputs[0].deviceId;
        }

        await zxingReader.decodeFromVideoDevice(deviceId, video, (result, error) => {
            if (result) {
                handleDetectedCode(result.getText());
                return;
            }

            if (error && !(error instanceof ZXing.NotFoundException)) {
                console.warn('ZXing scan warning:', error);
            }
        });

        updateStatus('Kamera aktif. Scanner QR siap digunakan.', 'success');
    }

    async function startQrScanner(video) {
        qrScanner = new QrScanner(
            video,
            (result) => {
                const qrValue = typeof result === 'string'
                    ? result
                    : (result?.data || result?.text || '');
                handleDetectedCode(qrValue);
            },
            {
                preferredCamera: currentCamera,
                highlightScanRegion: true,
                highlightCodeOutline: true,
                maxScansPerSecond: 8,
            }
        );

        await qrScanner.start();
        updateStatus('Kamera aktif. Scanner QR siap digunakan.', 'success');
    }

    function handleDetectedCode(qrValue) {
        const now = Date.now();
        if (!qrValue || now - lastScanAt < scanCooldownMs) {
            return;
        }

        lastScanAt = now;
        processQRResult(qrValue);
    }

    function processQRResult(qrData) {
        if (!qrData) {
            showResult('Data QR Code tidak valid.', 'danger');
            return;
        }

        let santriId = String(qrData).trim();

        if (santriId.includes(' - ')) {
            santriId = santriId.split(' - ')[0].trim();
        } else if (santriId.includes('|')) {
            santriId = santriId.split('|')[0].trim();
        } else if (santriId.includes(',')) {
            santriId = santriId.split(',')[0].trim();
        }

        santriId = santriId.replace(/[^0-9]/g, '');

        if (!santriId || santriId.length < 3) {
            showResult('Format ID santri dari QR tidak valid.', 'danger');
            return;
        }

        const santriName = santriLookup[santriId];

        if (!santriName) {
            showResult(`Santri dengan ID "${santriId}" tidak ditemukan dalam daftar.`, 'warning');
            return;
        }

        highlightSantriInList(santriId);
        submitAttendance(santriId, santriName || 'Santri');
    }

    function highlightSantriInList(santriId) {
        if (highlightedSantriItem) {
            highlightedSantriItem.classList.remove('highlighted');
        }

        const targetItem = santriItemMap.get(santriId);
        if (!targetItem) {
            highlightedSantriItem = null;
            return;
        }

        targetItem.classList.add('highlighted');
        targetItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        highlightedSantriItem = targetItem;
    }

    function submitAttendance(santriId, santriName) {
        const jenisSholat = document.getElementById('jenisSholatHeader').value;
        const tanggal = document.getElementById('tanggalAbsen').value;
        const formData = new FormData();

        formData.append('_token', csrfToken);
        formData.append('id_santri', santriId);
        formData.append('jenis_sholat', jenisSholat);
        if (tanggal) {
            formData.append('tanggal', tanggal);
        }

        fetch(storeSholatUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
            .then(async (response) => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.error || data.message || `HTTP ${response.status}`);
                }

                return data;
            })
            .then((data) => {
                showResult(`Presensi berhasil: ${santriName} untuk sholat ${jenisSholat}.`, 'success');
                if (data.message) {
                    updateStatus(data.message, 'success');
                }
            })
            .catch((error) => {
                console.error('Attendance submission error:', error);
                showResult(`Gagal menyimpan presensi: ${error.message}`, 'danger');
            });
    }

    function showResult(message, type) {
        const resultDiv = document.getElementById('result');
        resultDiv.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'times-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }

    function updateStatus(message, type) {
        const statusDiv = document.getElementById('scannerStatus');
        const statusText = document.getElementById('statusText');

        if (!statusDiv || !statusText) {
            return;
        }

        statusDiv.className = `alert alert-${type} status-card`;
        statusText.textContent = message;
        statusDiv.style.display = 'block';
    }

    function showVideoOverlay(message) {
        const overlay = document.getElementById('videoOverlay');
        if (!overlay) {
            return;
        }

        overlay.innerHTML = `
            <i class="fas fa-camera fa-lg mb-2"></i>
            <p class="mb-0">${message}</p>
        `;
        overlay.style.display = 'block';
    }

    function hideVideoOverlay() {
        const overlay = document.getElementById('videoOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    function cancelNativeScanLoop() {
        if (scanAnimationFrame) {
            window.cancelAnimationFrame(scanAnimationFrame);
            scanAnimationFrame = null;
        }
    }

    function restartCamera() {
        updateStatus('Merestart kamera...', 'info');
        startCamera().catch((error) => {
            updateStatus(error.message || 'Gagal merestart kamera.', 'danger');
        });
    }

    function stopCamera(options = {}) {
        const silent = options.silent === true;

        cancelNativeScanLoop();

        if (qrScanner) {
            qrScanner.stop().catch(() => {});
            qrScanner.destroy();
            qrScanner = null;
        }

        if (zxingReader) {
            zxingReader.reset();
            zxingReader = null;
        }

        if (mediaStream) {
            mediaStream.getTracks().forEach((track) => track.stop());
            mediaStream = null;
        }

        const video = document.getElementById('preview');
        if (video) {
            video.pause();
            video.srcObject = null;
        }

        isScanning = false;

        if (!silent) {
            updateStatus('Kamera dihentikan.', 'secondary');
        }
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopCamera({ silent: true });
            return;
        }

        startCamera().catch((error) => {
            updateStatus(error.message || 'Gagal menyalakan kamera kembali.', 'danger');
        });
    });

    window.addEventListener('beforeunload', () => {
        stopCamera({ silent: true });
    });
</script>
@endsection


