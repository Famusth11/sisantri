@extends('layouts.app')

@section('content')
<style>
    .schedule-edit-page {
        display: grid;
        gap: 1rem;
    }
    .schedule-edit-card {
        border: 1px solid rgba(202, 191, 168, 0.72);
        border-radius: 1rem;
        background: #fffdf8;
        box-shadow: 0 10px 24px rgba(32, 53, 47, 0.08);
    }
    .schedule-edit-hero {
        background: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
        color: #fff;
        border-radius: 1.25rem;
        padding: 1.4rem;
    }
</style>

<div class="schedule-edit-page">
    <section class="schedule-edit-hero">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
            <div>
                <h1 class="h3 mb-2">Edit Jadwal Diniyah</h1>
                <p class="mb-0 text-white-50">
                    Perbarui pengampu, jam, atau target kelas tanpa menyentuh histori presensi yang sudah lewat.
                </p>
            </div>
            <div>
                <a href="{{ route('jadwal_diniyah.index', $returnFilters) }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Jadwal
                </a>
            </div>
        </div>
    </section>

    <section class="card schedule-edit-card">
        <div class="card-header bg-light fw-bold">Form Edit Jadwal</div>
        <div class="card-body">
            @include('jadwal_diniyah.partials.form', [
                'jadwalDiniyah' => $jadwalDiniyah,
                'tahunAjaran' => $jadwalDiniyah->tahun_ajaran,
                'semester' => $jadwalDiniyah->semester,
                'formAction' => route('jadwal_diniyah.update', $jadwalDiniyah->id),
                'formMethod' => 'PUT',
                'submitLabel' => 'Simpan Perubahan',
                'secondaryActionUrl' => route('jadwal_diniyah.index', $returnFilters),
                'secondaryActionLabel' => 'Batal',
            ])
        </div>
    </section>
</div>
@endsection
