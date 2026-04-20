@extends('layouts.app')

@section('content')
<style>
    .santri-detail-page {
        max-width: 880px;
        margin: 0 auto;
    }
    .santri-detail-card {
        border: 1px solid #ddd5c3;
        border-radius: 1rem;
        box-shadow: 0 10px 24px rgba(65, 77, 67, 0.1);
        overflow: hidden;
    }
    .santri-detail-card .card-header {
        background: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
        color: #fff;
        border-bottom: none;
        padding: 1rem 1.15rem;
    }
    .santri-detail-list dt,
    .santri-detail-list dd {
        padding-top: 0.35rem;
        padding-bottom: 0.35rem;
        margin-bottom: 0;
    }
    @media (max-width: 767.98px) {
        .santri-detail-page {
            padding-left: 0.15rem;
            padding-right: 0.15rem;
        }
        .santri-detail-card .card-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.75rem;
        }
        .santri-detail-card .card-header .btn {
            width: 100%;
        }
    }
</style>
<div class="container mt-4">
    <div class="row justify-content-center santri-detail-page">
        <div class="col-12">
            <div class="card santri-detail-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Detail Santri</h5>
                    <a href="{{ auth()->user()->role === 'Admin' ? route('santri.index') : route('santri.view') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    <dl class="row santri-detail-list">
                        <dt class="col-sm-4">ID</dt>
                        <dd class="col-sm-8">{{ $santri->id_santri ?? '-' }}</dd>

                        <dt class="col-sm-4">Nama</dt>
                        <dd class="col-sm-8">{{ $santri->nama ?? '-' }}</dd>

                        <dt class="col-sm-4">Jenis Kelamin</dt>
                        <dd class="col-sm-8">{{ $santri->jenis_kelamin ?? '-' }}</dd>

                        <dt class="col-sm-4">Kelas</dt>
                        <dd class="col-sm-8">{{ $santri->kelas ?? '-' }}</dd>

                        <dt class="col-sm-4">Golongan</dt>
                        <dd class="col-sm-8">{{ $santri->golongan ?? '-' }}</dd>

                        <dt class="col-sm-4">Pembina</dt>
                        <dd class="col-sm-8">{{ $santri->pembina ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



