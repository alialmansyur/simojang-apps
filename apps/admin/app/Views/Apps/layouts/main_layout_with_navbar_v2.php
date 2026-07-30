<?= view('Apps/partials/header'); ?>
<?= $this->renderSection('style'); ?>
<?= view('Apps/partials/sidebar'); ?>
<div id="main" class="mt-0 p-0 main-bg">
    <header class="topbar-header d-flex align-items-center justify-content-between px-4 py-3">
        <div class="d-flex align-items-center gap-3">
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
            <!-- Page Title -->
            <h5 class="mb-0 fw-bold d-none d-md-block ms-2 topbar-title"><?= $this->renderSection('title') ?? 'Dashboard' ?></h5>
        </div>
        <div class="d-flex align-items-center gap-4">
            <!-- Realtime Clock -->
            <div class="realtime-clock d-none d-md-flex flex-column text-end">
                <span id="topbar-date" class="small fw-semibold">Memuat...</span>
                <span id="topbar-time" class="fw-bold">--:--:--</span>
            </div>
            
            <!-- Notifikasi (disembunyikan sementara) -->
            <div class="dropdown me-1 mt-1 d-none">
                <a href="#" class="position-relative d-flex align-items-center notification-btn" data-bs-toggle="dropdown" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                        0
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-3" aria-labelledby="dropdownMenuButton">
                    <li><h6 class="dropdown-header">Notifikasi</h6></li>
                    <li><a class="dropdown-item" href="#">Tidak ada untuk saat ini</a></li>
                </ul>
            </div>
            
            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-menu d-flex">
                        <div class="user-img d-flex align-items-center">
                            <div class="avatar avatar-md border border-2 border-primary">
                                <img id="profileImage" src="<?= asset_url('apps/assets/images/faces/1.jpg') ?>">
                            </div>
                        </div>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 border-0 p-2 mt-2 app-dropdown-menu">
                    <li><h6 class="dropdown-header">Hello, <?= $seslog['username'] ?? 'User' ?> !</h6></li>
                    <li><a href="<?= base_url('/profil') ?>" class="dropdown-item"><i class="icon-mid bi bi-person me-2"></i>Lihat Profil</a></li>
                    <li><a class="dropdown-item app-clickable" data-bs-toggle="modal" data-bs-target="#change-password"><i class="icon-mid bi bi-gear me-2"></i>Ubah Kata Sandi</a></li>
                    <li><a class="dropdown-item" href="#" onclick="logout()"><i class="icon-mid bi bi-box-arrow-left me-2 text-danger"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>
    <div id="main-content-inner">
        <?= $this->renderSection('content'); ?>
    </div>
</div>
<?= view('Apps/partials/modal/changepass'); ?>
<?= view('Apps/partials/footer'); ?>
<script src="<?= base_url('apps/assets/js/custom/change-password.js?v=3'); ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-ui-global.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-topbar-actions.js') ?>"></script>
<?= $this->renderSection('scripts'); ?>

<script>
// Force Sidebar Toggle for Desktop/Mobile
document.addEventListener('DOMContentLoaded', function() {
    const burgerBtns = document.querySelectorAll('.burger-btn');
    
    if (burgerBtns.length > 0) {
        burgerBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                document.body.classList.toggle('sidebar-hidden');
            });
        });
    }

    // Submenu Toggle Logic
    const sidebarItems = document.querySelectorAll('.sidebar-item.has-sub');
    sidebarItems.forEach(item => {
        const link = item.querySelector('.sidebar-link');
        const submenu = item.querySelector('.submenu');
        if (link && submenu) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                submenu.classList.toggle('active');
                item.classList.toggle('active'); // Optional: keep parent active highlight
            });
        }
    });
});

// Real-time Clock for Topbar
function updateTopbarClock() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateStr = now.toLocaleDateString('id-ID', options);
    
    let hours = String(now.getHours()).padStart(2, '0');
    let minutes = String(now.getMinutes()).padStart(2, '0');
    let seconds = String(now.getSeconds()).padStart(2, '0');
    
    if(document.getElementById('topbar-date')) {
        document.getElementById('topbar-date').textContent = dateStr;
        document.getElementById('topbar-time').textContent = `${hours}:${minutes}:${seconds}`;
    }
}

if(document.getElementById('topbar-time')) {
    updateTopbarClock();
    setInterval(updateTopbarClock, 1000);
}
</script>


