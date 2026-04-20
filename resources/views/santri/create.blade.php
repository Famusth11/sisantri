@extends('layouts.app')

@section('content')
<style>
    .santri-form-page {
        max-width: 880px;
        margin: 0 auto;
    }
    .santri-form-card {
        border: 1px solid #ddd5c3;
        border-radius: 1rem;
        box-shadow: 0 10px 24px rgba(65, 77, 67, 0.1);
        overflow: hidden;
    }
    .santri-form-card .card-header {
        background: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
        color: #fff;
        border-bottom: none;
        padding: 1rem 1.15rem;
    }
    .santri-radio-group {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .santri-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    @media (max-width: 767.98px) {
        .santri-form-page {
            padding-left: 0.15rem;
            padding-right: 0.15rem;
        }
        .santri-form-card .card-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.75rem;
        }
        .santri-form-card .card-header .btn,
        .santri-form-actions .btn {
            width: 100%;
        }
    }
</style>
<div class="container mt-4">
    <div class="row justify-content-center santri-form-page">
        <div class="col-12">
            <div class="card santri-form-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Tambah Santri</h5>
                    <a href="{{ route('santri.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('santri.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" id="nama" name="nama" class="form-control" value="{{ old('nama') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="kelas" class="form-label">Kelas</label>
                            <select id="kelas" name="kelas" class="form-select" required>
                                <option value="" disabled {{ old('kelas') ? '' : 'selected' }}>Pilih</option>
                                <option value="10" {{ old('kelas') === '10' ? 'selected' : '' }}>10</option>
                                <option value="11" {{ old('kelas') === '11' ? 'selected' : '' }}>11</option>
                                <option value="12" {{ old('kelas') === '12' ? 'selected' : '' }}>12</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Golongan</label>
                            <div class="santri-radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="golongan" id="golongan_bilingual" value="BILINGUAL" {{ old('golongan') === 'BILINGUAL' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="golongan_bilingual">
                                        BILINGUAL
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="golongan" id="golongan_tahfidz" value="TAHFIDZ" {{ old('golongan') === 'TAHFIDZ' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="golongan_tahfidz">
                                        TAHFIDZ
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select id="jenis_kelamin" name="jenis_kelamin" class="form-select" required>
                                <option value="" disabled {{ old('jenis_kelamin') ? '' : 'selected' }}>Pilih</option>
                                <option value="Putra" {{ old('jenis_kelamin') === 'Putra' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Putri" {{ old('jenis_kelamin') === 'Putri' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="pembina" class="form-label">Pembina</label>
                            <select id="pembina" name="pembina" class="form-select" required>
                                <option value="" disabled {{ old('pembina') ? '' : 'selected' }}>Pilih Pembina</option>
                                @foreach($pembinaList as $pembina)
                                    <option value="{{ $pembina }}" {{ old('pembina') === $pembina ? 'selected' : '' }}>
                                        {{ $pembina }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="santri-form-actions">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="btn-text">
                                    <i class="fas fa-save me-2"></i>Simpan
                                </span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Menyimpan...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    
    form.addEventListener('submit', function(e) {
        
        submitBtn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');
        
        
        const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
        inputs.forEach(input => {
            if (input.tagName === 'INPUT' || input.tagName === 'TEXTAREA') {
                input.readOnly = true;
            }
        });
    });
});
</script>
@endsection



