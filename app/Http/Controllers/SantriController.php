<?php

namespace App\Http\Controllers;

use App\Models\Santri;
use App\Models\User;
use App\Support\SpreadsheetImportReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\PngWriter;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SantriController extends Controller
{
    private const PEMBINA_CACHE_KEY = 'pembina_dropdown_list';
    private const SANTRI_IMPORT_TEMPLATE_HEADERS = ['id_santri', 'nama', 'jenis_kelamin', 'kelas', 'golongan', 'pembina'];

    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $search = trim((string) $request->query('q', ''));
            $kelasFilter = trim((string) $request->query('kelas', ''));
            $perPage = 10;
            $indexRouteName = $user->role === 'Admin' ? 'santri.index' : 'santri.view';

            $query = Santri::queryForUser($user)
                ->select(Santri::listColumns());

            if ($kelasFilter !== '' && in_array($kelasFilter, ['10', '11', '12'], true)) {
                $query->where('kelas', $kelasFilter);
            } else {
                $kelasFilter = '';
            }

            if ($search !== '') {
                $searchLike = '%' . $search . '%';
                $query->where(function ($builder) use ($searchLike) {
                    $builder->where('id_santri', 'like', $searchLike)
                        ->orWhere('nama', 'like', $searchLike)
                        ->orWhere('kelas', 'like', $searchLike)
                        ->orWhere('golongan', 'like', $searchLike)
                        ->orWhere('pembina', 'like', $searchLike);
                });
            }

            $santriList = $query
                ->orderBy('nama')
                ->paginate($perPage)
                ->withQueryString();

            $pembinaList = $user->role === 'Admin' ? $this->getPembinaList() : [];

            return view('santri.index', compact(
                'santriList',
                'search',
                'perPage',
                'kelasFilter',
                'indexRouteName',
                'pembinaList'
            ));
        } catch (\Exception $e) {
            \Log::error('Santri Index Error: ' . $e->getMessage(), [
                'user_id' => auth()->user()->id ?? null,
                'user_role' => auth()->user()->role ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return view('santri.index', [
                'santriList' => new LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url()]),
                'search' => '',
                'perPage' => 10,
                'kelasFilter' => '',
                'indexRouteName' => auth()->user()->role === 'Admin' ? 'santri.index' : 'santri.view',
                'pembinaList' => auth()->user()?->role === 'Admin' ? $this->getPembinaList() : [],
            ])->with('error', 'Gagal load data santri: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $pembinaList = $this->getPembinaList();

        return view('santri.create', compact('pembinaList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kelas' => 'required|string|max:255',
            'golongan' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|max:255',
            'pembina' => 'required|string|max:255',
        ]);

        $idSantri = $this->generateUniqueSantriId();

        Santri::create([
            'id_santri' => $idSantri,
            'nama' => $request->nama,
            'jenis_kelamin' => $request->jenis_kelamin,
            'kelas' => $request->kelas,
            'golongan' => $request->golongan,
            'pembina' => $request->pembina,
        ]);

        $this->clearSantriCaches();

        return redirect()->route('santri.index')->with('success', 'Santri ditambahkan dengan ID: ' . $idSantri);
    }

    public function import(Request $request, SpreadsheetImportReader $reader)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ], [
            'import_file.required' => 'File impor wajib dipilih.',
            'import_file.mimes' => 'Format file harus xlsx, xls, atau csv.',
            'import_file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'santriImport');
        }

        try {
            $rows = $reader->read($request->file('import_file'));
        } catch (\Throwable $e) {
            Log::error('Santri Import Read Error: ' . $e->getMessage());

            return back()->withErrors([
                'import_file' => 'File tidak bisa dibaca. Pastikan format Excel/CSV sesuai template.',
            ], 'santriImport');
        }

        if (empty($rows)) {
            return back()->withErrors([
                'import_file' => 'File impor tidak berisi data santri.',
            ], 'santriImport');
        }

        $preparedRows = [];
        $rowErrors = [];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2;
            $payload = [
                'id_santri' => $this->normalizeImportedValue($row, ['id_santri', 'id']),
                'nama' => $this->normalizeImportedValue($row, ['nama', 'nama_santri']),
                'jenis_kelamin' => $this->normalizeImportedGender($this->normalizeImportedValue($row, ['jenis_kelamin', 'jk'])),
                'kelas' => $this->normalizeImportedValue($row, ['kelas']),
                'golongan' => $this->normalizeImportedGolongan($this->normalizeImportedValue($row, ['golongan'])),
                'pembina' => $this->normalizeImportedValue($row, ['pembina']),
            ];

            $validator = Validator::make($payload, [
                'id_santri' => ['nullable', 'string', 'max:255'],
                'nama' => ['required', 'string', 'max:255'],
                'jenis_kelamin' => ['required', 'in:Putra,Putri'],
                'kelas' => ['required', 'string', 'max:255'],
                'golongan' => ['required', 'string', 'max:255'],
                'pembina' => ['required', 'string', 'max:255'],
            ], [
                'nama.required' => 'nama wajib diisi.',
                'jenis_kelamin.required' => 'jenis kelamin wajib diisi.',
                'jenis_kelamin.in' => 'jenis kelamin harus Putra atau Putri.',
                'kelas.required' => 'kelas wajib diisi.',
                'golongan.required' => 'golongan wajib diisi.',
                'pembina.required' => 'pembina wajib diisi.',
            ]);

            if ($validator->fails()) {
                $rowErrors[] = 'Baris ' . $lineNumber . ': ' . implode(' ', $validator->errors()->all());
                continue;
            }

            $preparedRows[] = $payload;
        }

        if (!empty($rowErrors)) {
            return back()->withErrors([
                'import_file' => implode(' ', array_slice($rowErrors, 0, 5)),
            ], 'santriImport');
        }

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($preparedRows, &$created, &$updated) {
            foreach ($preparedRows as $payload) {
                $idSantri = $payload['id_santri'] ?: $this->generateUniqueSantriId();

                $santri = Santri::query()->where('id_santri', $idSantri)->first();

                if ($santri) {
                    $santri->update([
                        'nama' => $payload['nama'],
                        'jenis_kelamin' => $payload['jenis_kelamin'],
                        'kelas' => $payload['kelas'],
                        'golongan' => $payload['golongan'],
                        'pembina' => $payload['pembina'],
                    ]);
                    $updated++;
                    continue;
                }

                Santri::create([
                    'id_santri' => $idSantri,
                    'nama' => $payload['nama'],
                    'jenis_kelamin' => $payload['jenis_kelamin'],
                    'kelas' => $payload['kelas'],
                    'golongan' => $payload['golongan'],
                    'pembina' => $payload['pembina'],
                ]);
                $created++;
            }
        });

        $this->clearSantriCaches();

        return redirect()->route('santri.index')->with(
            'success',
            $created . ' santri berhasil ditambahkan dan ' . $updated . ' santri berhasil diperbarui dari file impor.'
        );
    }

    public function downloadImportTemplate(): StreamedResponse
    {
        $rows = [
            self::SANTRI_IMPORT_TEMPLATE_HEADERS,
            ['', 'Ahmad Fauzi', 'Putra', '10', 'BILINGUAL', 'Ust. Agus Salim, S.Pd.I'],
            ['', 'Siti Aminah', 'Putri', '11', 'TAHFIDZ', 'Ustzh. Nisa Rahmawati, S.Pd'],
        ];

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'template-import-santri.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show($id)
    {
        try {
            $santri = Santri::findById($id);
            if (!$santri) {
                abort(404, 'Santri dengan ID ' . $id . ' tidak ditemukan.');
            }
            return view('santri.show', compact('santri'));
        } catch (\Exception $e) {
            Log::error('Santri Show Error: ' . $e->getMessage());
            abort(404);
        }
    }

    public function edit($id)
    {
        try {
            $santri = Santri::findById($id);
            if (!$santri) {
                abort(404);
            }
            
            $pembinaList = $this->getPembinaList($santri->pembina ?? null);
            
            return view('santri.edit', compact('santri', 'pembinaList'));
        } catch (\Exception $e) {
            Log::error('Santri Edit Error: ' . $e->getMessage());
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kelas' => 'required|string|max:255',
            'golongan' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|max:255',
            'pembina' => 'required|string|max:255',
        ]);

        try {
            $santri = Santri::findById($id);
            if (!$santri) {
                abort(404);
            }

            $santri->update([
                'nama' => $request->nama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'kelas' => $request->kelas,
                'golongan' => $request->golongan,
                'pembina' => $request->pembina,
            ]);

            $this->clearSantriCaches();
            Cache::forget('santri_' . $id);

            return redirect()->route('santri.index')->with('success', 'Santri diupdate!');
        } catch (\Exception $e) {
            Log::error('Santri Update Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal update: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $santri = Santri::findById($id);
            if (!$santri) {
                abort(404);
            }

            $santri->delete();

            $this->clearSantriCaches();
            Cache::forget('santri_' . $id);

            return redirect()->route('santri.index')->with('success', 'Santri dihapus!');
        } catch (\Exception $e) {
            Log::error('Santri Destroy Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal hapus: ' . $e->getMessage()]);
        }
    }

    private function getPembinaList(?string $selectedPembina = null): array
    {
        $pembinaList = Cache::remember(self::PEMBINA_CACHE_KEY, 300, function () {
            return User::query()
                ->where('role', 'Pembina')
                ->get(['name', 'nama_lengkap'])
                ->map(function (User $user): string {
                    return trim((string) ($user->nama_lengkap ?: $user->name));
                })
                ->unique()
                ->sort(SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->toArray();
        });

        if ($selectedPembina && !in_array($selectedPembina, $pembinaList, true)) {
            $pembinaList[] = $selectedPembina;
            sort($pembinaList);
        }

        return $pembinaList;
    }

    private function normalizeImportedValue(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function normalizeImportedGender(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $lookup = strtoupper($value);

        return match ($lookup) {
            'PUTRA', 'LAKI-LAKI', 'LAKI LAKI', 'L', 'MALE' => 'Putra',
            'PUTRI', 'PEREMPUAN', 'P', 'FEMALE' => 'Putri',
            default => $value,
        };
    }

    private function normalizeImportedGolongan(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : strtoupper($value);
    }

    private function clearSantriCaches(): void
    {
        Cache::forget(self::PEMBINA_CACHE_KEY);
        Cache::forget('santri_list_all_by_class_source');

        User::query()
            ->select('id', 'role')
            ->get()
            ->each(function (User $user): void {
                Santri::clearCache($user);
            });
    }

    private function generateUniqueSantriId(): string
    {
        do {
            $idSantri = 'S' . now('Asia/Jakarta')->format('ymdHis') . str_pad((string) random_int(10, 99), 2, '0', STR_PAD_LEFT);
        } while (Santri::query()->where('id_santri', $idSantri)->exists());

        return $idSantri;
    }

    public function showQr($id, Request $request)
    {
        Log::info('QR Request for ID: ' . $id);

        try {
            $santri = Santri::findById($id);
            if (!$santri) {
                Log::error('Santri not found for ID: ' . $id);
                abort(404, 'Santri dengan ID ' . $id . ' tidak ditemukan.');
            }

            $qrText = $id . ' - ' . ($santri->nama ?? 'Unknown');
            
            $qrCode = new QrCode(
                data: $qrText,
                errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::High,
                size: 300,
                margin: 10
            );

            $format = $request->get('format', 'svg');
            $download = $request->get('download', false);

            if ($format === 'pdf') {
                return $this->generateQrPdf($santri, $qrCode, $download);
            }

            $writer = new SvgWriter();
            $result = $writer->write($qrCode);

            $disposition = $download ? 'attachment' : 'inline';
            $filename = 'qr-' . $id . '.svg';

            return response($result->getString())
                ->header('Content-Type', 'image/svg+xml')
                ->header('Content-Disposition', $disposition . '; filename="' . $filename . '"')
                ->header('Cache-Control', 'public, max-age=3600'); 
        } catch (\Exception $e) {
            Log::error('QR Generation Error: ' . $e->getMessage());
            abort(500, 'Gagal generate QR: ' . $e->getMessage());
        }
    }

    private function generateQrPdf($santri, $qrCode, $download = false)
    {
        try {
            Log::info('Starting PDF generation for santri: ' . $santri->id_santri);
            
            if (!extension_loaded('gd')) {
                throw new \Exception('GD extension is not loaded. Please enable GD extension in PHP configuration.');
            }
            
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $qrImageBase64 = base64_encode($result->getString());
            
            Log::info('QR code generated successfully, size: ' . strlen($qrImageBase64) . ' characters');

            $data = [
                'santri' => $santri,
                'qrImage' => $qrImageBase64,
                'qrText' => $santri->id_santri . ' - ' . ($santri->nama ?? 'Unknown'),
                'generatedAt' => now()->format('d/m/Y H:i:s')
            ];

            Log::info('Loading PDF view with data');
            
            $options = new Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            
            $dompdf = new Dompdf($options);
            $html = view('santri.qr-pdf', $data)->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'QR-Santri-' . $santri->id_santri . '.pdf';
            
            Log::info('PDF generated successfully, filename: ' . $filename);

            if ($download) {
                return response($dompdf->output(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                ]);
            } else {
                return response($dompdf->output(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            abort(500, 'Gagal generate PDF: ' . $e->getMessage());
        }
    }
}


