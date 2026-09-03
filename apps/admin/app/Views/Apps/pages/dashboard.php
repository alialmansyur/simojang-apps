<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css?v=' . time()) ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=' . time()) ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/dashboard.css?v=' . time()) ?>">
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="dashboardPageTitle">
    <div class="text-start tw-wrap">
        <div class="dash-split-container">
            
            <!-- Ambient Backdrop Soft Glows -->
            <div class="dash-ambient-backdrop" aria-hidden="true">
                <div class="ambient-glow-1"></div>
                <div class="ambient-glow-2"></div>
            </div>

            <div class="row align-items-center w-100 g-4 g-xl-5">
                
                <!-- Left Section: Content & Interaction -->
                <div class="col-12 col-lg-6">
                    <div class="dash-left-content">
                        
                        <!-- 1. Large Dynamic Time Icon -->
                        <div class="dash-time-icon-box time-theme-siang fade-in-entry" id="dashBigTimeIconBox" aria-hidden="true">
                            <i class="bi bi-sun-fill" id="dashBigTimeIcon"></i>
                        </div>

                        <!-- Greeting Below Icon -->
                        <div class="dash-greeting-line fade-in-entry fade-delay-1">
                            <span id="dashGreetingText">Selamat Datang</span>, <span class="dash-greeting-user"><?= esc($user_name); ?></span>
                        </div>

                        <!-- 2. Main Narrative Title -->
                        <h1 class="dash-main-title fade-in-entry fade-delay-1" id="dashboardPageTitle">
                            <span class="dash-brand-gradient">SIMOJANG</span> Pusat Tata Kelola &amp; Layanan Kanreg
                        </h1>

                        <!-- Dynamic Rotating Feature Text with Blur Effect -->
                        <div class="dash-dynamic-feature-container fade-in-entry fade-delay-2">
                            <div class="dash-feature-rotator">
                                <i class="bi bi-people-fill dash-rotator-icon" id="dynamicFeatureIcon"></i>
                                <span class="dash-rotator-text blur-in" id="dynamicFeatureText">Manajemen Tim Kerja &amp; Layanan Kepegawaian ASN</span>
                            </div>
                        </div>

                        <!-- 3. Symmetrical Shortcuts (Icon + Text Vertically Centered, Moderate Radius, No Shadow) -->
                        <div class="dash-shortcuts-grid fade-in-entry fade-delay-3" role="navigation" aria-label="Akses Cepat Modul">
                            <a href="<?= base_url('timkerja') ?>" class="dash-shortcut-item is-primary">
                                <i class="bi bi-grid-fill"></i>
                                <span>Tim Kerja</span>
                            </a>
                            <a href="<?= base_url('activity-gallery') ?>" class="dash-shortcut-item">
                                <i class="bi bi-images"></i>
                                <span>Galeri Kegiatan</span>
                            </a>
                            <a href="<?= base_url('calendar-event') ?>" class="dash-shortcut-item">
                                <i class="bi bi-calendar-event"></i>
                                <span>Agenda</span>
                            </a>
                            <a href="<?= base_url('apps-cat') ?>" class="dash-shortcut-item">
                                <i class="bi bi-display"></i>
                                <span>Layanan CAT</span>
                            </a>
                            <a href="<?= base_url('apps-pnbp') ?>" class="dash-shortcut-item">
                                <i class="bi bi-file-earmark-text-fill"></i>
                                <span>Dokumen PNBP</span>
                            </a>
                            <a href="<?= base_url('apps-dms') ?>" class="dash-shortcut-item">
                                <i class="bi bi-folder2-open"></i>
                                <span>Tata Naskah</span>
                            </a>
                        </div>

                    </div>
                </div>

                <!-- Right Section: Visual Illustration -->
                <div class="col-12 col-lg-6">
                    <div class="dash-right-illustration fade-in-entry fade-delay-2">
                        <div class="dash-illus-wrapper">
                            <img src="<?= asset_url('apps/assets/images/illustration-data.png') ?>" alt="SIMOJANG Data Illustration" class="dash-illus-img img-fluid">
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</main>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/dashboard.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>
