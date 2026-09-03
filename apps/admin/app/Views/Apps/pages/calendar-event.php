<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css?v=' . time()) ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=' . time()) ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/activity-gallery.css?v=' . time()) ?>">

<!-- FullCalendar & Custom Page CSS -->
<style>
    /* KPI Cards Styling (Adopted from apps-cat) */
    .twx-card-container {
        background-color: #ffffff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px;
        transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), border-color 0.2s ease !important;
        cursor: pointer;
        overflow: hidden;
        min-height: 108px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .twx-card-container:hover {
        transform: translateY(-4px) !important;
        border-color: var(--bs-primary) !important;
    }
    .twx-bg-icon-wrapper {
        position: absolute;
        left: -20px;
        bottom: -20px;
        opacity: 0.03;
        transform: rotate(-15deg);
        pointer-events: none;
        transition: transform 0.4s ease, opacity 0.4s ease;
    }
    .twx-bg-icon-svg {
        width: 140px;
        height: 140px;
        color: var(--twx-text);
    }
    .twx-card-container:hover .twx-bg-icon-wrapper {
        transform: scale(1.1) rotate(5deg) !important;
        opacity: 0.08 !important;
    }
    .twx-main-icon-container {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: var(--twx-bg);
        color: var(--twx-text);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .twx-main-icon-svg-wrapper {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .twx-main-icon-svg-wrapper svg {
        width: 100%;
        height: 100%;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .twx-card-container:hover .twx-main-icon-svg-wrapper svg {
        transform: scale(1.15) rotate(-10deg);
    }
    .kpi-title {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 0.35rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        line-height: 1.2;
    }
    .kpi-value {
        font-size: 2.35rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
        letter-spacing: -0.03em;
        display: flex;
        align-items: center;
    }
    
    /* Calendar Container */
    #calendar-container {
        background: transparent;
        padding: 1.5rem;
        min-height: 600px;
        overflow-x: auto;
        width: 100%;
    }
    #calendar {
        min-width: 700px;
    }
    .fc-theme-standard .fc-scrollgrid {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }
    .fc .fc-toolbar-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
    }
    .fc .fc-button-primary {
        background-color: #0ea5e9;
        border-color: #0ea5e9;
        box-shadow: none;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active, 
    .fc .fc-button-primary:not(:disabled):active {
        background-color: #0284c7;
        border-color: #0284c7;
    }
    .fc .fc-button-primary:hover {
        background-color: #0284c7;
        border-color: #0284c7;
    }
    .fc-event {
        cursor: pointer;
        border-radius: 4px;
        padding: 2px 4px;
        font-size: 0.85em;
        font-weight: 500;
        border: none !important;
        transition: transform 0.1s ease;
    }
    .fc-event:hover {
        transform: scale(1.02);
        z-index: 5;
    }
    .fc-daygrid-event-dot {
        border-color: transparent !important;
        background-color: currentColor;
    }
    
    /* Agenda Panel */
    .agenda-panel {
        height: 100%;
        max-height: 800px;
        overflow-y: auto;
    }
    /* New Agenda Styling */
    .agenda-header-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1a202c;
        margin-bottom: 0.2rem;
    }
    .agenda-header-subtitle {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
    }
    
    /* Highlight Card */
    .agenda-hl-card {
        background: var(--bs-primary);
        background: linear-gradient(135deg, var(--bs-primary), rgba(var(--bs-primary-rgb), 0.85));
        border-radius: 16px;
        padding: 1.5rem;
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 15px -3px rgba(var(--bs-primary-rgb), 0.3);
        cursor: pointer;
        transition: transform 0.2s;
    }
    .agenda-hl-card:hover {
        transform: translateY(-2px);
    }
    .agenda-hl-bg-icon {
        position: absolute;
        right: -10%;
        top: -10%;
        font-size: 8rem;
        color: rgba(255, 255, 255, 0.1);
        transform: rotate(-15deg);
        pointer-events: none;
    }
    .agenda-hl-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 0.75rem;
    }
    .pulse-dot {
        width: 10px;
        height: 10px;
        background-color: #22c55e;
        border-radius: 50%;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
        animation: pulse-green 2s infinite;
    }
    .pulse-dot.upcoming {
        background-color: #f59e0b;
        box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
        animation: pulse-yellow 2s infinite;
    }
    @keyframes pulse-green {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    @keyframes pulse-yellow {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
    }
    .agenda-hl-time {
        font-size: 2.2rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 0.5rem;
    }
    
    /* Timeline List */
    .agenda-timeline {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .agenda-tl-item {
        display: flex;
        gap: 1rem;
        cursor: pointer;
        transition: transform 0.2s;
        border-radius: 8px;
        padding: 0.5rem;
    }
    .agenda-tl-item:hover {
        background: #f8fafc;
        transform: translateX(4px);
    }
    .agenda-tl-time {
        width: 45px;
        flex-shrink: 0;
        text-align: right;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    .agenda-tl-time-start {
        font-weight: 700;
        color: #334155;
        font-size: 0.85rem;
        line-height: 1.2;
    }
    .agenda-tl-time-end {
        color: #94a3b8;
        font-size: 0.75rem;
    }
    .agenda-tl-node {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 0.2rem;
    }
    .agenda-tl-circle {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: rgba(var(--bs-primary-rgb), 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .agenda-tl-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--bs-primary);
    }
    .agenda-tl-content {
        flex-grow: 1;
        padding-top: 0.1rem;
    }
    .agenda-tl-title {
        color: var(--bs-primary);
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 0.2rem;
        line-height: 1.3;
    }
    .agenda-tl-location {
        color: #64748b;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    
    /* Empty State */
    .empty-state-img {
        transition: transform 0.3s ease;
    }
    .empty-state-img:hover {
        transform: scale(1.05) rotate(2deg);
    }
    
    /* Toolbar Filters */
    .filter-toolbar {
        margin-bottom: 1.5rem;
    }
    
    /* Force Modal Event Detail Rounded Styling */
    #modalEventDetail,
    #modalEventDetail .modal-dialog,
    #modalEventDetail .modal-content,
    #modalEventDetail.modal .modal-content,
    #modalEventDetail.modal-force-rounded .modal-content {
        border-radius: 1.25em !important;
        -webkit-border-radius: 1.25em !important;
        -moz-border-radius: 1.25em !important;
        --bs-modal-border-radius: 1.25em !important;
        --bs-modal-inner-border-radius: 1.25em !important;
    }
    #modalEventDetail .modal-content {
        border-radius: 1.25em !important;
        -webkit-border-radius: 1.25em !important;
        -moz-border-radius: 1.25em !important;
        border: 1px solid #e2e8f0 !important;
        overflow: hidden !important;
        background-color: #ffffff !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    }
    #modalEventDetail .modal-header {
        border-top-left-radius: 1.25em !important;
        border-top-right-radius: 1.25em !important;
        background-color: #ffffff !important;
    }
    #modalEventDetail .modal-body {
        background-color: #ffffff !important;
    }
    #modalEventDetail .modal-footer {
        border-bottom-left-radius: 1.25em !important;
        border-bottom-right-radius: 1.25em !important;
        background-color: #ffffff !important;
    }
    #modalEventDetail .text-dark {
        color: #1e293b !important;
    }
    #modalEventDetail .text-secondary {
        color: #64748b !important;
    }
    #modalEventDetail .text-muted {
        color: #94a3b8 !important;
    }
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="calendarPageTitle">
    <div class="text-start tw-wrap">
        
        <!-- Welcome Header matching /activity-gallery & /timkerja -->
        <div class="tw-welcome text-start mt-3 mb-2" role="banner">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h1 class="tw-title lh-1" id="calendarPageTitle" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                        Kalender Kegiatan
                    </h1>
                    <div style="max-width: 580px; margin: 0; line-height: 1.6;">
                        <p class="tw-subtitle text-secondary mb-0" style="font-size: 1.05rem; font-weight: 500;">
                            <strong>Pantau dan kelola seluruh jadwal kegiatan instansi.</strong> Jelajahi agenda, rapat, sosialisasi, dan kegiatan lainnya.
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <a href="<?= base_url('timkerja') ?>" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2 px-3" style="height: 42px; border-radius: 8px;">
                        <i class="bi bi-chevron-left" style="display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; line-height: 1;"></i>
                        <span class="fw-bold" style="font-size: 0.95rem; line-height: 1;">Kembali</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="tw-head row align-items-center mt-4 mb-3" role="toolbar" aria-label="Aksi kalender kegiatan">
            <div class="col-12 d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-3">
                <div class="flex-grow-1" style="max-width: 420px;">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute text-muted" style="left: 1.2rem; top: 50%; transform: translateY(-50%); margin-top: -1px; line-height: 1; pointer-events: none;"></i>
                        <input type="text" id="searchEvent" class="form-control tw-search-input" placeholder="Cari kegiatan, lokasi..." style="padding-left: 2.8rem; padding-top: 0.65rem; padding-bottom: 0.65rem;">
                    </div>
                </div>
                <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center gap-2">
                    <select id="filterCategory" class="form-select fw-bold flex-grow-1 flex-sm-grow-0" style="width: auto; height: 42px; color: #1a202c !important; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                        <option value="">Semua Kategori</option>
                        <option value="Rapat">Rapat</option>
                        <option value="Sosialisasi">Sosialisasi</option>
                        <option value="Bimtek">Bimtek</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <select id="filterStatus" class="form-select fw-bold flex-grow-1 flex-sm-grow-0" style="width: auto; height: 42px; color: #1a202c !important; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                        <option value="">Semua Status</option>
                        <option value="Belum Mulai">Belum Mulai</option>
                        <option value="Berlangsung">Berlangsung</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row g-3 mb-4" id="kpiCardsContainer">
            <!-- Total Kegiatan -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm position-relative twx-card-container h-100 p-3 px-3 px-xl-4" style="--twx-bg: #e0f2fe; --twx-text: #0284c7; --twx-border: #bae6fd;">
                    <div class="twx-bg-icon-wrapper">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="twx-bg-icon-svg"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <div class="d-flex justify-content-between align-items-center position-relative my-auto" style="z-index: 1;">
                        <div class="d-flex flex-column justify-content-center">
                            <div class="kpi-title">Total Kegiatan</div>
                            <div class="kpi-value" id="kpiTotal">-</div>
                        </div>
                        <div class="twx-main-icon-container">
                            <span class="twx-main-icon-svg-wrapper">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Kegiatan Selesai -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm position-relative twx-card-container h-100 p-3 px-3 px-xl-4" style="--twx-bg: #dcfce7; --twx-text: #16a34a; --twx-border: #bbf7d0;">
                    <div class="twx-bg-icon-wrapper">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="twx-bg-icon-svg"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div class="d-flex justify-content-between align-items-center position-relative my-auto" style="z-index: 1;">
                        <div class="d-flex flex-column justify-content-center">
                            <div class="kpi-title">Kegiatan Selesai</div>
                            <div class="kpi-value" id="kpiCompleted">-</div>
                        </div>
                        <div class="twx-main-icon-container">
                            <span class="twx-main-icon-svg-wrapper">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Akan Datang -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm position-relative twx-card-container h-100 p-3 px-3 px-xl-4" style="--twx-bg: #fef9c3; --twx-text: #ca8a04; --twx-border: #fef08a;">
                    <div class="twx-bg-icon-wrapper">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="twx-bg-icon-svg"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div class="d-flex justify-content-between align-items-center position-relative my-auto" style="z-index: 1;">
                        <div class="d-flex flex-column justify-content-center">
                            <div class="kpi-title">Akan Datang</div>
                            <div class="kpi-value" id="kpiUpcoming">-</div>
                        </div>
                        <div class="twx-main-icon-container">
                            <span class="twx-main-icon-svg-wrapper">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Kegiatan Hari Ini -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm position-relative twx-card-container h-100 p-3 px-3 px-xl-4" style="--twx-bg: #fee2e2; --twx-text: #dc2626; --twx-border: #fecaca;">
                    <div class="twx-bg-icon-wrapper">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="twx-bg-icon-svg"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <div class="d-flex justify-content-between align-items-center position-relative my-auto" style="z-index: 1;">
                        <div class="d-flex flex-column justify-content-center">
                            <div class="kpi-title">Kegiatan Hari Ini</div>
                            <div class="kpi-value" id="kpiToday">-</div>
                        </div>
                        <div class="twx-main-icon-container">
                            <span class="twx-main-icon-svg-wrapper">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card mb-5 border-0" style="border-radius: 16px; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);">
            <div class="card-body p-3 p-xl-4">
                <div class="row g-4">
                    <!-- Calendar (col-8) -->
                    <div class="col-12 col-xl-8">
                        <div id="calendar-container" style="padding: 0;">
                            <div id="calendar"></div>
                        </div>
                    </div>
                    <!-- Agenda (col-4) -->
                    <div class="col-12 col-xl-4">
                        <div class="agenda-panel h-100">
                            <h4 class="agenda-header-title">Agenda Hari Ini</h4>
                            <p class="agenda-header-subtitle" id="agendaSubtitle">Anda memiliki 0 agenda dijadwalkan</p>
                            <div id="todayAgendaContainer">
                            <!-- Agenda items injected here -->
                        </div>
                        <div id="agendaEmptyState" class="d-none">
                            <div class="d-flex flex-column align-items-center justify-content-center text-center mt-4 mb-4 pb-4 tw-animate-entry">
                                <img src="<?= asset_url('apps/assets/images/empty-content-profile.png') ?>" alt="Tidak Ada Agenda" class="empty-state-img" style="max-width: 200px; margin-bottom: 1.5rem;">
                                <h5 class="fw-bold" style="color: #1a202c; font-size: 1.2rem;">Belum Ada Agenda Hari Ini</h5>
                                <p class="text-muted mb-0" style="font-size: 0.95rem; max-width: 300px; margin: 0 auto; line-height: 1.6;">
                                    Anda tidak memiliki jadwal kegiatan yang aktif hari ini.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- Modal Event Detail -->
<div class="modal fade modal-force-rounded" id="modalEventDetail" tabindex="-1" aria-labelledby="modalEventDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="border-radius: 1.25em !important;">
        <div class="modal-content" style="border-radius: 1.25em !important; -webkit-border-radius: 1.25em !important; -moz-border-radius: 1.25em !important; border: 1px solid #e2e8f0 !important; overflow: hidden !important; background-color: #ffffff !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important;">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 align-items-center" style="border-top-left-radius: 1.25em !important; border-top-right-radius: 1.25em !important; background-color: #ffffff !important;">
                <div class="w-100 pe-4">
                    <div class="text-uppercase fw-bold text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">DETAIL AGENDA</div>
                    <h5 class="modal-title fw-bold" id="detailEventTitle" style="color: #1a202c; font-size: 1.25rem;">Judul Kegiatan</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pt-3 pb-4" style="background-color: #ffffff !important;">
                <div class="row gy-3">
                    <!-- Row: Deskripsi -->
                    <div class="col-12 d-flex justify-content-between align-items-start border-bottom pb-2">
                        <div class="fw-bold" style="width: 35%; color: #1a202c; font-size: 0.95rem;">Deskripsi</div>
                        <div class="text-end flex-grow-1" id="detailDescription" style="color: #1e293b; font-size: 0.95rem;">-</div>
                    </div>
                    
                    <!-- Row: Status -->
                    <div class="col-12 d-flex justify-content-between align-items-center border-bottom pb-2">
                        <div class="fw-bold" style="width: 35%; color: #1a202c; font-size: 0.95rem;">Status</div>
                        <div class="text-end flex-grow-1">
                            <span class="badge rounded-pill px-3 py-2" id="detailStatusBadge" style="font-weight: 600;">Status</span>
                        </div>
                    </div>
                    
                    <!-- Row: Kategori -->
                    <div class="col-12 d-flex justify-content-between align-items-center border-bottom pb-2">
                        <div class="fw-bold" style="width: 35%; color: #1a202c; font-size: 0.95rem;">Kategori</div>
                        <div class="text-end flex-grow-1">
                            <span class="badge rounded-pill px-3 py-2" id="detailCategoryBadge" style="background-color: #f1f5f9; color: #475569; font-weight: 600;">Kategori</span>
                        </div>
                    </div>

                    <!-- Row: Waktu Pelaksanaan -->
                    <div class="col-12 d-flex justify-content-between align-items-center border-bottom pb-2">
                        <div class="fw-bold" style="width: 35%; color: #1a202c; font-size: 0.95rem;">Waktu Pelaksanaan</div>
                        <div class="text-end flex-grow-1" style="color: #1e293b; font-size: 0.95rem;">
                            <i class="bi bi-calendar-event text-primary me-1"></i> <span id="detailTimeText">-</span>
                        </div>
                    </div>

                    <!-- Row: Lokasi -->
                    <div class="col-12 d-flex justify-content-between align-items-center border-bottom pb-2">
                        <div class="fw-bold" style="width: 35%; color: #1a202c; font-size: 0.95rem;">Lokasi</div>
                        <div class="text-end flex-grow-1" style="color: #1e293b; font-size: 0.95rem;">
                            <i class="bi bi-geo-alt text-primary me-1"></i> <span id="detailLocation">-</span>
                        </div>
                    </div>

                    <!-- Row: No Surat Tugas -->
                    <div class="col-12 d-flex justify-content-between align-items-center border-bottom pb-2">
                        <div class="fw-bold" style="width: 35%; color: #1a202c; font-size: 0.95rem;">No Surat Tugas</div>
                        <div class="text-end flex-grow-1" id="detailLetter" style="color: #1e293b; font-size: 0.95rem;">-</div>
                    </div>

                    <!-- Row: Tim Kerja -->
                    <div class="col-12 d-flex justify-content-between align-items-center border-bottom pb-2">
                        <div class="fw-bold" style="width: 35%; color: #1a202c; font-size: 0.95rem;">Tim Kerja</div>
                        <div class="text-end flex-grow-1" id="detailTeam" style="color: #1e293b; font-size: 0.95rem;">-</div>
                    </div>

                    <!-- Row: Partisipan -->
                    <div class="col-12 d-flex justify-content-between align-items-center pb-2">
                        <div class="fw-bold" style="width: 35%; color: #1a202c; font-size: 0.95rem;">Partisipan</div>
                        <div class="text-end flex-grow-1" id="detailStaffContainer" style="color: #1e293b; font-size: 0.95rem;">
                            -
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4 justify-content-end" style="border-bottom-left-radius: 1.25em !important; border-bottom-right-radius: 1.25em !important; background-color: #ffffff !important;">
                <button type="button" class="btn btn-light fw-bold px-4 flat-btn-light" style="border-radius: 10px !important;" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary fw-bold px-4 flat-btn-primary" style="background-color: #059669; border-color: #059669; border-radius: 10px !important;">
                    <i class="bi bi-calendar-plus me-1"></i> Simpan (.ics)
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<!-- FullCalendar Script -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<!-- Custom Page Logic -->
<script src="<?= asset_url('apps/assets/js/custom/pages/calendar-event.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>
