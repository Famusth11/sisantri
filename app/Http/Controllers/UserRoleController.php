<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\SpreadsheetImportReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserRoleController extends Controller
{
    private const PEMBINA_CACHE_KEY = 'pembina_dropdown_list';
    private const DEFAULT_IMPORTED_USER_PASSWORD = 'password123';
    private const USER_IMPORT_TEMPLATE_HEADERS = ['name', 'email', 'role', 'nama_lengkap', 'kelas_kitab_hendel', 'password'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $roleFilter = trim((string) $request->query('role', ''));
        $perPage = (int) $request->query('per_page', 10);
        $allowedPerPage = [10, 25, 50];
        $indexRouteName = auth()->user()->role === 'Admin' ? 'user_roles.index' : 'user_roles.view';

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $query = User::query()
            ->select('id', 'name', 'email', 'role', 'nama_lengkap', 'created_at', 'updated_at', 'last_login_at');

        if ($roleFilter !== '' && in_array($roleFilter, ['Admin', 'Pembina', 'Ustadz Pengajar'], true)) {
            $query->where('role', $roleFilter);
        } else {
            $roleFilter = '';
        }

        if ($search !== '') {
            $searchLike = '%' . $search . '%';
            $query->where(function ($builder) use ($searchLike) {
                $builder->where('name', 'like', $searchLike)
                    ->orWhere('email', 'like', $searchLike)
                    ->orWhere('role', 'like', $searchLike)
                    ->orWhere('nama_lengkap', 'like', $searchLike);
            });
        }

        $users = $query
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('user_roles.index', compact('users', 'search', 'roleFilter', 'perPage', 'indexRouteName'));
    }

    public function create()
    {
        return view('user_roles.create', [
            'accessOptionsByRole' => $this->accessOptionsByRole(),
            'roleOptions' => $this->roleOptions(),
            'hasPrimaryAdmin' => $this->hasPrimaryAdmin(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:Admin,Pembina,Ustadz Pengajar',
            'nama_lengkap' => 'required|string|max:255',
            'kelas_kitab_hendel' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->role === 'Admin' && $this->hasPrimaryAdmin()) {
            return back()
                ->withErrors(['role' => 'Admin utama sudah ada. Sistem hanya mengizinkan satu akun admin.'])
                ->withInput();
        }

        $hashedPassword = Hash::make($request->password);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $hashedPassword,
                'role' => $request->role,
                'nama_lengkap' => $request->nama_lengkap,
                'kelas_kitab_hendel' => $request->kelas_kitab_hendel,
            ]);

            Cache::forget('users_list');
            Cache::forget(self::PEMBINA_CACHE_KEY);

            return redirect()->route('user_roles.index')->with('success', 'User berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('User  Store Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal simpan user: ' . $e->getMessage()])->withInput();
        }
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
            return back()->withErrors($validator, 'userImport');
        }

        try {
            $rows = $reader->read($request->file('import_file'));
        } catch (\Throwable $e) {
            Log::error('User Import Read Error: ' . $e->getMessage());

            return back()->withErrors([
                'import_file' => 'File tidak bisa dibaca. Pastikan format Excel/CSV sesuai template.',
            ], 'userImport');
        }

        if (empty($rows)) {
            return back()->withErrors([
                'import_file' => 'File impor tidak berisi data user.',
            ], 'userImport');
        }

        $preparedRows = [];
        $rowErrors = [];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2;
            $payload = [
                'name' => $this->normalizeImportedValue($row, ['name', 'username', 'nama']),
                'email' => $this->normalizeImportedEmail($this->normalizeImportedValue($row, ['email'])),
                'role' => $this->normalizeImportedRole($this->normalizeImportedValue($row, ['role'])),
                'nama_lengkap' => $this->normalizeImportedValue($row, ['nama_lengkap', 'nama_lengkap_user', 'nama_lengkap_pengguna']),
                'kelas_kitab_hendel' => $this->normalizeImportedValue($row, ['kelas_kitab_hendel', 'kelas_hendel', 'kelas_kitab_handle']),
                'password' => $this->normalizeImportedValue($row, ['password']),
            ];

            $validator = Validator::make($payload, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'role' => ['required', Rule::in(['Pembina', 'Ustadz Pengajar'])],
                'nama_lengkap' => ['required', 'string', 'max:255'],
                'kelas_kitab_hendel' => ['nullable', 'string', 'max:255'],
                'password' => ['nullable', 'string', 'min:8'],
            ], [
                'name.required' => 'nama login wajib diisi.',
                'email.required' => 'email wajib diisi.',
                'email.email' => 'format email tidak valid.',
                'role.required' => 'role wajib diisi.',
                'role.in' => 'role impor hanya boleh Pembina atau Ustadz Pengajar.',
                'nama_lengkap.required' => 'nama lengkap wajib diisi.',
                'password.min' => 'password minimal 8 karakter.',
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
            ], 'userImport');
        }

        $created = 0;
        $updated = 0;
        $defaultPasswordCount = 0;

        DB::transaction(function () use ($preparedRows, &$created, &$updated, &$defaultPasswordCount) {
            foreach ($preparedRows as $payload) {
                $user = User::query()->where('email', $payload['email'])->first();

                if ($user) {
                    $updateData = [
                        'name' => $payload['name'],
                        'role' => $payload['role'],
                        'nama_lengkap' => $payload['nama_lengkap'],
                        'kelas_kitab_hendel' => $payload['kelas_kitab_hendel'],
                    ];

                    if (!empty($payload['password'])) {
                        $updateData['password'] = Hash::make($payload['password']);
                    }

                    $user->update($updateData);
                    $updated++;
                    continue;
                }

                $plainPassword = $payload['password'] ?: self::DEFAULT_IMPORTED_USER_PASSWORD;
                if (empty($payload['password'])) {
                    $defaultPasswordCount++;
                }

                User::create([
                    'name' => $payload['name'],
                    'email' => $payload['email'],
                    'password' => Hash::make($plainPassword),
                    'role' => $payload['role'],
                    'nama_lengkap' => $payload['nama_lengkap'],
                    'kelas_kitab_hendel' => $payload['kelas_kitab_hendel'],
                ]);
                $created++;
            }
        });

        Cache::forget('users_list');
        Cache::forget(self::PEMBINA_CACHE_KEY);

        $message = $created . ' user berhasil ditambahkan dan ' . $updated . ' user berhasil diperbarui dari file impor.';
        if ($defaultPasswordCount > 0) {
            $message .= ' ' . $defaultPasswordCount . ' user baru memakai password default `' . self::DEFAULT_IMPORTED_USER_PASSWORD . '`.';
        }

        return redirect()->route('user_roles.index')->with('success', $message);
    }

    public function downloadImportTemplate(): StreamedResponse
    {
        $rows = [
            self::USER_IMPORT_TEMPLATE_HEADERS,
            ['nisa.pembina', 'nisa.pembina@example.com', 'Pembina', 'Nisa Rahma', 'PUTRI KELAS 10', 'password123'],
            ['ahmad.ustadz', 'ahmad.ustadz@example.com', 'Ustadz Pengajar', 'Ahmad Robihan', 'BILINGUAL', 'password123'],
        ];

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'template-import-user.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show(User $user)
    {
        return view('user_roles.show', compact('user'));
    }

    public function edit(User $user)
    {
        if ($this->isProtectedAdmin($user)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Akun admin utama dikelola dari halaman profil, bukan dari manajemen user.');
        }

        return view('user_roles.edit', [
            'user' => $user,
            'accessOptionsByRole' => $this->accessOptionsByRole($user->kelas_kitab_hendel),
            'roleOptions' => $this->roleOptions($user),
        ]);
    }

    public function update(Request $request, User $user)
    {
        if ($this->isProtectedAdmin($user)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Akun admin utama tidak bisa diubah dari manajemen user.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:Admin,Pembina,Ustadz Pengajar',
            'nama_lengkap' => 'required|string|max:255',
            'kelas_kitab_hendel' => ['nullable', 'string', 'max:255'],
            'password' => 'nullable|min:8|confirmed',
        ]);

        if ($request->role === 'Admin' && $this->hasPrimaryAdmin($user->id)) {
            return back()
                ->withErrors(['role' => 'Admin utama sudah ada. User lain tidak bisa diubah menjadi admin.'])
                ->withInput();
        }

        $data = $request->only(['name', 'email', 'role', 'nama_lengkap', 'kelas_kitab_hendel']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        try {
            $user->update($data);

            Cache::forget('users_list');
            Cache::forget(self::PEMBINA_CACHE_KEY);

            return redirect()->route('user_roles.index')->with('success', 'User berhasil diupdate!');
        } catch (\Exception $e) {
            Log::error('User  Update Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal update user: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(User $user)
    {
        if ($this->isProtectedAdmin($user)) {
            return redirect()->route('user_roles.show', $user->id)
                ->with('error', 'Akun admin utama tidak bisa dihapus.');
        }

        try {
            $user->delete();

            Cache::forget('users_list');
            Cache::forget(self::PEMBINA_CACHE_KEY);

            return redirect()->route('user_roles.index')->with('success', 'User berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('User  Destroy Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal hapus user: ' . $e->getMessage()]);
        }
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

    private function normalizeImportedEmail(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : strtolower($value);
    }

    private function normalizeImportedRole(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $lookup = strtoupper(str_replace(['-', '_'], ' ', $value));
        $lookup = preg_replace('/\s+/', ' ', $lookup) ?? $lookup;

        return match ($lookup) {
            'ADMIN' => 'Admin',
            'PEMBINA' => 'Pembina',
            'USTADZ', 'USTADZ PENGAJAR', 'USTAZ PENGAJAR', 'USTAZD PENGAJAR', 'USTAD PENGAJAR' => 'Ustadz Pengajar',
            default => $value,
        };
    }

    private function accessOptionsByRole(?string $currentValue = null): array
    {
        $options = [
            'Admin' => [],
            'Pembina' => [
                'PUTRA KELAS 10',
                'PUTRA KELAS 11',
                'PUTRA KELAS 12',
                'PUTRI KELAS 10',
                'PUTRI KELAS 11',
                'PUTRI KELAS 12',
            ],
            'Ustadz Pengajar' => [
                'BILINGUAL',
                'TAHFIDZ',
            ],
        ];

        $currentValue = trim((string) $currentValue);
        if ($currentValue !== '') {
            $matchedRole = null;

            foreach ($options as $role => $roleOptions) {
                if (in_array($currentValue, $roleOptions, true)) {
                    $matchedRole = $role;
                    break;
                }
            }

            if ($matchedRole === null) {
                $matchedRole = str_contains(strtoupper($currentValue), 'KELAS') ? 'Pembina' : 'Ustadz Pengajar';
            }

            $options[$matchedRole][] = $currentValue;
            $options[$matchedRole] = array_values(array_unique($options[$matchedRole]));
            sort($options[$matchedRole], SORT_NATURAL | SORT_FLAG_CASE);
        }

        return $options;
    }

    private function roleOptions(?User $user = null): array
    {
        $roles = ['Pembina', 'Ustadz Pengajar'];

        if (!$this->hasPrimaryAdmin($user?->id)) {
            array_unshift($roles, 'Admin');
        }

        if ($user && $user->role === 'Admin' && !in_array('Admin', $roles, true)) {
            array_unshift($roles, 'Admin');
        }

        return $roles;
    }

    private function hasPrimaryAdmin(?int $ignoreUserId = null): bool
    {
        $query = User::query()->where('role', 'Admin');

        if ($ignoreUserId !== null) {
            $query->where('id', '!=', $ignoreUserId);
        }

        return $query->exists();
    }

    private function isProtectedAdmin(User $user): bool
    {
        return $user->role === 'Admin';
    }

    public function updatePassword(Request $request, User $user)
    {
        if (auth()->user()->role !== 'Admin') {
            abort(403, 'Unauthorized action.');
        }

        if ($this->isProtectedAdmin($user)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Password admin utama diubah dari halaman profil.');
        }

        $validated = $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        try {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            Cache::forget('users_list');
            Cache::forget(self::PEMBINA_CACHE_KEY);

            Log::info('User password updated by Admin: ' . auth()->user()->email . ' for user: ' . $user->email);

            return redirect()->route('user_roles.show', $user->id)
                ->with('success', "Password user {$user->name} berhasil diubah!");
        } catch (\Exception $e) {
            Log::error('Update Password Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal mengubah password: ' . $e->getMessage()])->withInput();
        }
    }

}

