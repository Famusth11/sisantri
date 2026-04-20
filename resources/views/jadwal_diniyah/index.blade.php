@extends('layouts.app')

@section('content')
<style>
    .schedule-page {
        display: grid;
        gap: 1rem;
    }
    .schedule-card {
        border: 1px solid rgba(202, 191, 168, 0.72);
        border-radius: 1rem;
        background: #fffdf8;
        box-shadow: 0 10px 24px rgba(32, 53, 47, 0.08);
        overflow: hidden;
    }
    .schedule-card .card-header {
        background: #f5f0e5;
        border-bottom: 1px solid rgba(202, 191, 168, 0.62);
    }
    .schedule-subtle {
        color: #607066;
        font-size: 0.9rem;
    }
    .schedule-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        justify-content: space-between;
    }
    .schedule-toolbar__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .schedule-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: #f7fbf9;
        border: 1px solid rgba(207, 229, 220, 0.92);
        color: #0f5c4d;
        font-size: 0.84rem;
        font-weight: 600;
    }
    .schedule-table td,
    .schedule-table th {
        vertical-align: middle;
    }
    .schedule-table td {
        font-size: 0.94rem;
    }
    .schedule-panel {
        border-top: 1px solid rgba(202, 191, 168, 0.45);
        background: #fffaf0;
    }
    .schedule-pagination {
        border-top: 1px solid rgba(202, 191, 168, 0.45);
        background: #fffaf6;
    }
    .schedule-pagination .page-link {
        color: #0f5c4d;
    }
    .schedule-pagination .page-item.active .page-link {
        background-color: #0f5c4d;
        border-color: #0f5c4d;
        color: #fff;
    }
    @media (max-width: 767.98px) {
        .schedule-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .schedule-toolbar__actions {
            width: 100%;
        }
        .schedule-toolbar__actions .btn {
            flex: 1 1 auto;
        }
    }
</style>

<div class="schedule-page">
    <section class="card schedule-card">
        <div class="card-header">
            <div class="schedule-toolbar">
                <div>
                    <h1 class="h5 mb-1">Daftar Jadwal {{ $tahunAjaran }} - {{ $semester }}</h1>
                    <div class="schedule-subtle">
                        Jadwal aktif: {{ $activePeriod?->tahun_ajaran ?? '-' }} {{ $activePeriod?->semester ?? '' }}.
                        {{ $jadwalList->total() }} jadwal ditemukan.
                    </div>
                </div>
                <div class="schedule-toolbar__actions">
                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#jadwalFilters" aria-expanded="false" aria-controls="jadwalFilters">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#jadwalAddModal">
                        <i class="fas fa-plus me-2"></i>Tambah Jadwal
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body border-bottom">
            <div class="d-flex flex-wrap gap-2">
                <span class="schedule-chip">
                    <i class="fas fa-calendar-alt"></i>
                    Periode: {{ $tahunAjaran }} - {{ $semester }}
                </span>
                <span class="schedule-chip">
                    <i class="fas fa-layer-group"></i>
                    Total periode ini: {{ (int) ($periodSummary->total_jadwal ?? 0) }}
                </span>
                <span class="schedule-chip">
                    <i class="fas fa-check-circle"></i>
                    Aktif periode ini: {{ (int) ($periodSummary->total_active ?? 0) }}
                </span>
                <span class="schedule-chip">
                    <i class="fas fa-book"></i>
                    Kitab master: {{ $kitabOptions->count() }}
                </span>
            </div>
        </div>

        <div class="collapse schedule-panel" id="jadwalFilters">
            <div class="card-body">
                <form method="GET" action="{{ route('jadwal_diniyah.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="filter_tahun_ajaran" class="form-label fw-bold">Tahun Ajaran</label>
                        <select name="tahun_ajaran" id="filter_tahun_ajaran" class="form-select">
                            @foreach($availableYears as $yearOption)
                                <option value="{{ $yearOption }}" {{ $tahunAjaran === $yearOption ? 'selected' : '' }}>
                                    {{ $yearOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter_semester" class="form-label fw-bold">Semester</label>
                        <select name="semester" id="filter_semester" class="form-select">
                            @foreach($availableSemesters as $semesterOption)
                                <option value="{{ $semesterOption }}" {{ $semester === $semesterOption ? 'selected' : '' }}>
                                    {{ $semesterOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Tampilkan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 schedule-table">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Kegiatan</th>
                            <th>Kelas</th>
                            <th>Golongan</th>
                            <th>Pengampu</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwalList as $jadwal)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $jadwal->nama_kegiatan }}</div>
                                    <div class="schedule-subtle">
                                        {{ $jadwal->kitab?->master_label ? 'Master: ' . $jadwal->kitab->master_label : 'Tanpa tautan master kitab' }}
                                    </div>
                                </td>
                                <td>{{ $jadwal->kelas ?? 'Semua' }}</td>
                                <td>{{ $jadwal->golongan ?? 'Semua' }}</td>
                                <td>{{ $jadwal->pengampu ?? '-' }}</td>
                                <td>{{ $jadwal->formatted_jam ?? '-' }}</td>
                                <td>
                                    @if($jadwal->is_active)
                                        <span class="badge text-bg-success">Aktif</span>
                                    @else
                                        <span class="badge text-bg-secondary">Tersimpan</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <a href="{{ route('jadwal_diniyah.edit', ['jadwalDiniyah' => $jadwal->id, 'tahun_ajaran' => $tahunAjaran, 'semester' => $semester, 'page' => $jadwalList->currentPage()]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-pen me-1"></i>Edit
                                        </a>
                                        <form method="POST" action="{{ route('jadwal_diniyah.destroy', $jadwal->id) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
                                            <input type="hidden" name="semester" value="{{ $semester }}">
                                            <input type="hidden" name="page" value="{{ $jadwalList->currentPage() }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash me-1"></i>Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="schedule-subtle">Belum ada jadwal pada periode ini. Tambahkan jadwal baru atau salin dari periode sebelumnya.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($jadwalList->hasPages())
            <div class="card-body schedule-pagination">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="schedule-subtle">
                        Menampilkan {{ $jadwalList->firstItem() }}-{{ $jadwalList->lastItem() }} dari {{ $jadwalList->total() }} jadwal.
                    </div>
                    <div>
                        {{ $jadwalList->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif
    </section>
</div>

<div class="modal fade" id="jadwalAddModal" tabindex="-1" aria-labelledby="jadwalAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="jadwalAddModalLabel">Tambah Jadwal Baru</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                @include('jadwal_diniyah.partials.form', [
                    'formAction' => route('jadwal_diniyah.store'),
                    'formMethod' => 'POST',
                    'submitLabel' => 'Simpan Jadwal',
                    'formContext' => 'add_schedule',
                ])
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
            var hasErrors = {{ $errors->any() ? 'true' : 'false' }};
            if (!hasErrors) {
                return;
            }

            var formContext = @json(old('form_context'));

            if (formContext === 'add_schedule') {
                var addModal = document.getElementById('jadwalAddModal');
                if (addModal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(addModal).show();
                }
                return;
            }

            var filters = document.getElementById('jadwalFilters');
            if (filters) {
                filters.classList.add('show');
            }
        });
    })();
</script>
@endsection
