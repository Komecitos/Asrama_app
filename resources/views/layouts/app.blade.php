<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AsramaApp - Sistem Manajemen Asrama</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/images/logo asrama.jpeg') }}">

    {{-- Theme --}}
    <link rel="stylesheet" href="{{ asset('css/theme/color.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme/typography.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme/spacing.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme/variable.css') }}">

    {{-- Layout --}}
    <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">

    {{-- Components --}}
    <link rel="stylesheet" href="{{ asset('css/components/badge_button.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/card.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/table.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/toast.css') }}">

    {{-- Page specific --}}
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}">
    @stack('styles')
</head>

{{-- TOAST CONTAINER --}}
<div id="toast-container" class="toast-container"></div>

<script>
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icon = type === 'success' ? '✓' : '✕';

        toast.innerHTML = `
        <span class="toast-icon">${icon}</span>
        <span class="toast-message">${message}</span>
        <button class="toast-close" onclick="dismissToast(this.parentElement)">✕</button>
    `;

        container.appendChild(toast);

        setTimeout(() => dismissToast(toast), 4000);
    }

    function dismissToast(toast) {
        if (!toast || !toast.parentElement) return;
        toast.style.animation = 'toast-out 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }

    function openDocsModal() {
        document.getElementById('modal-docs').classList.add('show');
        document.getElementById('modal-docs-overlay').classList.add('show');
    }

    function closeDocsModal() {
        document.getElementById('modal-docs').classList.remove('show');
        document.getElementById('modal-docs-overlay').classList.remove('show');
    }
</script>

@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const portalNavBtns = document.querySelectorAll('.portal-nav-btn');
        portalNavBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                portalNavBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    });
</script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast("{{ session('success') }}", 'success');
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast("{{ session('error') }}", 'error');
    });
</script>
@endif

@if(isset($errors) && $errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast("{{ $errors->first() }}", 'error');
    });
</script>
@endif

<body>

    {{-- Global Header --}}
    <header class="global-header">
        <a href="{{ route('dashboard') }}" class="app-name">
            <img src="{{ asset('assets/images/logo asrama.jpeg') }}" alt="AsramaApp Logo" style="height: 32px; width: auto; object-fit: contain; border-radius: 6px;">
            <span>AsramaApp</span>
        </a>
        <nav class="portal-nav">
            <a href="{{ route('dashboard') }}" class="portal-nav-btn {{ Request::is('/') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('asrama.data') }}" class="portal-nav-btn {{ Request::is('asrama/data*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Data Penghuni & Kamar</span>
            </a>
            <a href="{{ route('asrama.keuangan') }}" class="portal-nav-btn {{ Request::is('asrama/keuangan') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                    <line x1="1" y1="10" x2="23" y2="10"></line>
                </svg>
                <span>Transaksi Kas</span>
            </a>
            <a href="{{ route('asrama.keuangan.matriks') }}" class="portal-nav-btn {{ Request::is('asrama/keuangan/matriks*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Matriks Iuran</span>
            </a>
        </nav>
    </header>

    {{-- Main Content --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- Bottom Bar --}}
    <footer class="bottom-bar">
        <div class="bottom-bar-inner" onclick="openDocsModal()" style="cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-size: 0.82rem;">
            <span>🏢 AsramaApp &middot; Sistem Informasi Asrama &copy; {{ date('Y') }}</span>
            <span style="opacity: 0.4;">|</span>
            <span style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.35); color: #38bdf8; font-size: 0.72rem; padding: 1px 7px; border-radius: 4px; font-weight: 700; letter-spacing: 0.4px;">v{{ config('nativephp.version', '1.0.0') }}</span>
            <span style="opacity: 0.4;">|</span>
            <span style="color: var(--accent-primary); text-decoration: underline; font-weight: 600;">Tentang Aplikasi</span>
        </div>
    </footer>

    {{-- MODAL DOKUMENTASI --}}
    <div id="modal-docs-overlay" class="modal-overlay" onclick="closeDocsModal()"></div>
    <div id="modal-docs" class="modal modal-create" aria-hidden="true" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Tentang AsramaApp</h3>
            <button onclick="closeDocsModal()" class="modal-close">&times;</button>
        </div>

        <div style="max-height: 65vh; overflow-y: auto;">
            <div style="text-align: center; margin: 10px 0 20px;">
                <img src="{{ asset('assets/images/logo asrama.jpeg') }}" alt="AsramaApp Logo" style="height: 64px; width: auto; object-fit: contain; margin-bottom: 8px; border-radius: 12px;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin: 0;">AsramaApp Desktop</h2>
                <div style="margin-top: 4px; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <span style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.4); color: #38bdf8; font-size: 0.75rem; padding: 2px 8px; border-radius: 6px; font-weight: 700;">Versi {{ config('nativephp.version', '1.0.0') }}</span>
                </div>
                <p style="color: #94a3b8; font-size: 0.85rem; margin-top: 6px;">Sistem Informasi & Manajemen Penghuni, Kamar, dan Keuangan Asrama</p>
            </div>
            <div class="docs-info-row">
                <span class="task-meta">Nama Aplikasi</span>
                <span class="task-title">AsramaApp (Aplikasi Desktop)</span>
            </div>
            <div class="docs-info-row">
                <span class="task-meta">Versi Sistem</span>
                <span class="task-title" style="color: #38bdf8; font-weight: 700;">v{{ config('nativephp.version', '1.0.0') }} (Desktop Build)</span>
            </div>
            <div class="docs-info-row">
                <span class="task-meta">Tujuan & Fungsi</span>
                <span class="task-title">Aplikasi desktop mandiri untuk manajemen data kamar, pencatatan biodata penghuni asrama Kabupaten Mahakam Ulu, pembukuan kas masuk/keluar, visualisasi matriks iuran bulanan, distribusi akses WiFi via WhatsApp, serta ekspor laporan PDF & Excel.</span>
            </div>
            <div class="docs-info-row">
                <span class="task-meta">Developer</span>
                <span class="task-title">Irga Prayoga</span>
            </div>
            <div class="docs-info-row">
                <span class="task-meta">Tahun Rilis</span>
                <span class="task-title">2026</span>
            </div>
            <div class="docs-info-row">
                <span class="task-meta">Tech Stack</span>
                <span class="task-title">Laravel 12 (PHP 8.3), NativePHP (Electron), SQLite Database, DomPDF</span>
            </div>
            <div class="docs-info-row">
                <span class="task-meta">Repository</span>
                <span class="task-title"><a href="https://github.com/Komecitos/Asrama_app" target="_blank" style="color: var(--accent-primary);">github.com/Komecitos/Asrama_app</a></span>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" onclick="closeDocsModal()" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
    {{-- Page specific scripts --}}
    @stack('scripts')


</body>

</html>