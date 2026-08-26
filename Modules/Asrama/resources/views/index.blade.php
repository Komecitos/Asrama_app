@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/asrama.css') }}">
@endpush

@section('topbar')
<a href="{{ route('asrama.index') }}" class="btn btn-primary">Dashboard Asrama</a>
@endsection

@section('content')

<div class="page-header">
    <h2 class="title">Modul Asrama</h2>
</div>

<div class="asrama-wrapper">
    <div class="asrama-stats-grid">
        <div class="asrama-stat-card">
            <p class="task-meta">Total Penghuni</p>
            <h3 style="color: var(--text-primary); margin: 0.25rem 0; font-size: 1.8rem;">0 Orang</h3>
            <p class="task-meta" style="font-size: 0.75rem;">Aktif tinggal di asrama</p>
        </div>
        <div class="asrama-stat-card">
            <p class="task-meta">Total Kamar</p>
            <h3 style="color: #6ee7b7; margin: 0.25rem 0; font-size: 1.8rem;">0 Kamar</h3>
            <p class="task-meta" style="font-size: 0.75rem;">Tersedia & terisi</p>
        </div>
        <div class="asrama-stat-card">
            <p class="task-meta">Fasilitas & Aset</p>
            <h3 style="color: #a5b4fc; margin: 0.25rem 0; font-size: 1.8rem;">0 Item</h3>
            <p class="task-meta" style="font-size: 0.75rem;">Inventaris asrama</p>
        </div>
    </div>

    <div class="widget-card" style="padding: 2rem; text-align: center;">
        <span style="font-size: 3rem; display: block; margin-bottom: 0.5rem;">🏢</span>
        <h3 style="margin-bottom: 0.5rem; color: var(--text-primary);">Selamat Datang di Portal Asrama</h3>
        <p class="task-meta" style="max-width: 500px; margin: 0 auto 1.5rem;">
            Modul pengelolaan asrama baru saja dibuat. Siap dikembangkan untuk manajemen penghuni, kamar, iuran, dan inventaris fasilitas.
        </p>
    </div>
</div>

@endsection