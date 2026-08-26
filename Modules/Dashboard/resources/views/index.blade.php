@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/dashboard.css') }}">
<style>
    .dashboard-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        max-width: 1050px;
        margin-left: auto;
        margin-right: auto;
    }

    .dashboard-toolbar p {
        margin: 0;
        color: var(--text-muted, #94a3b8);
        font-size: 0.95rem;
    }

    .portal-card-wrapper {
        position: relative;
    }

    .portal-card-hide-btn {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        z-index: 10;
        background: rgba(15, 23, 42, 0.75);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: var(--text-muted);
        border-radius: 8px;
        padding: 0.25rem 0.55rem;
        font-size: 0.725rem;
        cursor: pointer;
        transition: all 0.2s ease;
        opacity: 0;
    }

    .portal-card-wrapper:hover .portal-card-hide-btn {
        opacity: 1;
    }

    .portal-card-hide-btn:hover {
        background: rgba(239, 68, 68, 0.25);
        color: #f87171;
        border-color: rgba(239, 68, 68, 0.4);
    }

    .hidden-modules-section {
        margin-top: 2.5rem;
        max-width: 1050px;
        margin-left: auto;
        margin-right: auto;
    }

    .hidden-modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1rem;
        margin-top: 0.75rem;
    }

    .hidden-module-card {
        background: rgba(15, 23, 42, 0.4);
        border: 1px dashed rgba(255, 255, 255, 0.12);
        border-radius: var(--radius-lg, 12px);
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .hidden-module-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .module-manage-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        background: rgba(15, 23, 42, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: var(--radius-md, 8px);
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-header">
        <h2>Selamat datang di MyHub</h2>
    </div>

    <div class="dashboard-toolbar">
        <p>Pilih portal yang ingin kamu akses</p>
        <button type="button" onclick="openManageModulesModal()" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
            ⚙️ Kelola Modul
        </button>
    </div>

    {{-- PORTAL GRID --}}
    <div id="portal-grid" class="portal-grid">
        {{-- TODO --}}
        <div class="portal-card-wrapper" data-module-key="todo">
            <button type="button" onclick="hideModule('todo')" class="portal-card-hide-btn" title="Sembunyikan modul Todo">
                👁️ Sembunyikan
            </button>
            <a href="{{ route('todo.index') }}" class="portal-card">
                <div class="portal-card-content">
                    <span class="portal-icon">
                        <img src="{{ asset('assets/images/todo_logo.png') }}" alt="Todo Portal Logo">
                    </span>
                    <h3 class="portal-title">Todo</h3>
                    <p class="portal-desc">Manajemen tugas & aktivitas harian</p>
                </div>
            </a>
        </div>

        {{-- KULIAH --}}
        <div class="portal-card-wrapper" data-module-key="kuliah">
            <button type="button" onclick="hideModule('kuliah')" class="portal-card-hide-btn" title="Sembunyikan modul Kuliah">
                👁️ Sembunyikan
            </button>
            <a href="{{ route('kuliah.jadwal') }}" class="portal-card">
                <div class="portal-card-content">
                    <span class="portal-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="color: #f8fafc;">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                        </svg>
                    </span>
                    <h3 class="portal-title">Kuliah</h3>
                    <p class="portal-desc">Mata kuliah, IPK & jadwal perkuliahan</p>
                </div>
            </a>
        </div>

        {{-- FREE FIRE --}}
        <div class="portal-card-wrapper" data-module-key="freefire">
            <button type="button" onclick="hideModule('freefire')" class="portal-card-hide-btn" title="Sembunyikan modul Free Fire">
                👁️ Sembunyikan
            </button>
            <a href="{{ route('freefire.index') }}" class="portal-card">
                <div class="portal-card-content">
                    <span class="portal-icon">
                        <img src="{{ asset('assets/images/ff_logo.webp') }}" alt="Free Fire Portal Logo">
                    </span>
                    <h3 class="portal-title">Free Fire</h3>
                    <p class="portal-desc">Tracker statistik & turnamen game</p>
                </div>
            </a>
        </div>
    </div>

    {{-- EMPTY STATE (IF ALL HIDDEN) --}}
    <div id="all-hidden-empty-state" class="empty-state" style="display: none; padding: 3rem 1rem; text-align: center; margin-top: 1rem;">
        <p style="font-size: 1.1rem; color: var(--text-primary); font-weight: 600; margin-bottom: 0.5rem;">Semua modul sedang disembunyikan</p>
        <p class="task-meta" style="margin-bottom: 1.25rem;">Kamu menyembunyikan seluruh portal di dashboard.</p>
        <button type="button" onclick="unhideAllModules()" class="btn btn-primary">Tampilkan Semua Modul</button>
    </div>

    {{-- HIDDEN MODULES SECTION --}}
    <div id="hidden-modules-section" class="hidden-modules-section" style="display: none;">
        <p class="task-meta" style="font-weight: 600; color: var(--text-muted); font-size: 0.85rem;">👁️‍🗨️ Modul Tersembunyi:</p>
        <div id="hidden-modules-grid" class="hidden-modules-grid"></div>
    </div>
</div>

{{-- MODAL KELOLA VISIBILITAS MODUL --}}
<div id="modal-manage-modules" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3>Kelola Visibilitas Modul</h3>
        <button onclick="closeManageModulesModal()" class="modal-close">&times;</button>
    </div>
    <div style="padding: 0.5rem 0;">
        <p class="task-meta" style="margin-bottom: 1rem;">Pilih modul yang ingin ditampilkan atau disembunyikan dari dashboard dan navigasi utama.</p>

        <div class="module-manage-item">
            <div class="hidden-module-info">
                <span class="task-title">📝 Todo</span>
                <span class="task-meta">(Tugas & Aktivitas Harian)</span>
            </div>
            <button type="button" id="btn-toggle-module-todo" onclick="toggleModule('todo')" class="btn btn-sm btn-secondary"></button>
        </div>

        <div class="module-manage-item">
            <div class="hidden-module-info">
                <span class="task-title">🎓 Kuliah</span>
                <span class="task-meta">(Mata Kuliah & Jadwal)</span>
            </div>
            <button type="button" id="btn-toggle-module-kuliah" onclick="toggleModule('kuliah')" class="btn btn-sm btn-secondary"></button>
        </div>

        <div class="module-manage-item">
            <div class="hidden-module-info">
                <span class="task-title">🎮 Free Fire</span>
                <span class="task-meta">(Tracker Game & Calculator)</span>
            </div>
            <button type="button" id="btn-toggle-module-freefire" onclick="toggleModule('freefire')" class="btn btn-sm btn-secondary"></button>
        </div>
    </div>

    <div class="form-actions" style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
        <button type="button" onclick="unhideAllModules()" class="btn btn-secondary btn-sm">Tampilkan Semua</button>
        <button type="button" onclick="closeManageModulesModal()" class="btn btn-primary">Selesai</button>
    </div>
</div>

<div id="modal-manage-overlay" class="modal-overlay" onclick="closeManageModulesModal()"></div>

@endsection

@push('scripts')
<script>
    var moduleDefs = window.moduleDefs || {
        todo: {
            name: 'Todo',
            icon: '📝',
            desc: 'Manajemen tugas & aktivitas harian'
        },
        kuliah: {
            name: 'Kuliah',
            icon: '🎓',
            desc: 'Mata kuliah, IPK & jadwal perkuliahan'
        },
        freefire: {
            name: 'Free Fire',
            icon: '🎮',
            desc: 'Tracker statistik & turnamen game'
        }
    };

    function getHiddenModules() {
        try {
            return JSON.parse(localStorage.getItem('myhub_hidden_modules') || '[]');
        } catch (e) {
            return [];
        }
    }

    function setHiddenModules(list) {
        localStorage.setItem('myhub_hidden_modules', JSON.stringify(list));
        updateDashboardModules();
        if (typeof syncGlobalNavbar === 'function') {
            syncGlobalNavbar();
        }
    }

    function hideModule(key) {
        const list = getHiddenModules();
        if (!list.includes(key)) {
            list.push(key);
            setHiddenModules(list);
            if (typeof showToast === 'function') {
                showToast(`Modul ${moduleDefs[key]?.name || key} disembunyikan`, 'success');
            }
        }
    }

    function unhideModule(key) {
        let list = getHiddenModules();
        list = list.filter(k => k !== key);
        setHiddenModules(list);
        if (typeof showToast === 'function') {
            showToast(`Modul ${moduleDefs[key]?.name || key} ditampilkan kembali`, 'success');
        }
    }

    function toggleModule(key) {
        const list = getHiddenModules();
        if (list.includes(key)) {
            unhideModule(key);
        } else {
            hideModule(key);
        }
        renderManageModalButtons();
    }

    function unhideAllModules() {
        setHiddenModules([]);
        renderManageModalButtons();
        if (typeof showToast === 'function') {
            showToast('Semua modul ditampilkan kembali', 'success');
        }
    }

    function updateDashboardModules() {
        const hidden = getHiddenModules();
        const cardWrappers = document.querySelectorAll('.portal-card-wrapper');
        let visibleCount = 0;

        cardWrappers.forEach(wrap => {
            const key = wrap.dataset.moduleKey;
            if (hidden.includes(key)) {
                wrap.style.display = 'none';
            } else {
                wrap.style.display = 'block';
                visibleCount++;
            }
        });

        // Empty state if all hidden
        const emptyState = document.getElementById('all-hidden-empty-state');
        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        // Hidden section grid
        const hiddenSection = document.getElementById('hidden-modules-section');
        const hiddenGrid = document.getElementById('hidden-modules-grid');

        if (hiddenSection && hiddenGrid) {
            if (hidden.length > 0) {
                hiddenSection.style.display = 'block';
                hiddenGrid.innerHTML = hidden.map(key => {
                    const def = moduleDefs[key] || {
                        name: key,
                        icon: '📦'
                    };
                    return `
                        <div class="hidden-module-card">
                            <div class="hidden-module-info">
                                <span>${def.icon}</span>
                                <div>
                                    <span class="task-title" style="font-size: 0.9rem;">${def.name}</span>
                                </div>
                            </div>
                            <button type="button" onclick="unhideModule('${key}')" class="btn btn-secondary btn-sm" style="font-size: 0.75rem;">
                                👁️ Tampilkan
                            </button>
                        </div>
                    `;
                }).join('');
            } else {
                hiddenSection.style.display = 'none';
                hiddenGrid.innerHTML = '';
            }
        }
    }

    function renderManageModalButtons() {
        const hidden = getHiddenModules();
        Object.keys(moduleDefs).forEach(key => {
            const btn = document.getElementById(`btn-toggle-module-${key}`);
            if (btn) {
                const isHidden = hidden.includes(key);
                btn.textContent = isHidden ? '👁️ Tampilkan' : '👁️‍🗨️ Sembunyikan';
                btn.className = isHidden ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-secondary';
            }
        });
    }

    function openManageModulesModal() {
        renderManageModalButtons();
        const m = document.getElementById('modal-manage-modules');
        const o = document.getElementById('modal-manage-overlay');
        if (m) {
            m.classList.add('show');
            m.style.display = 'block';
        }
        if (o) {
            o.classList.add('show');
            o.style.display = 'block';
        }
    }

    function closeManageModulesModal() {
        const m = document.getElementById('modal-manage-modules');
        const o = document.getElementById('modal-manage-overlay');
        if (m) {
            m.classList.remove('show');
            m.style.display = 'none';
        }
        if (o) {
            o.classList.remove('show');
            o.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateDashboardModules();
    });
</script>
@endpush