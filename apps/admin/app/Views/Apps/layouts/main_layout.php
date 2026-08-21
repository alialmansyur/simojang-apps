<?= view('Apps/partials/header'); ?>
<?= $this->renderSection('style'); ?>
<?= view('Apps/partials/sidebar'); ?>
<div id="main" class="mt-0 p-0 main-bg">
    <header class="topbar-header d-flex align-items-center justify-content-between px-4 py-3">
        <div class="d-flex align-items-center gap-3">
            <a href="#" class="burger-btn d-block d-xl-none" style="color: #2c3e50 !important;">
                <i class="bi bi-justify fs-3"></i>
            </a>
            <!-- Breadcrumbs or simple title can go here. For now, empty or dynamic -->
            <h5 class="mb-0 fw-bold d-none d-md-block" style="color: #2c3e50 !important;"><?= $this->renderSection('title') ?? 'Dashboard' ?></h5>
        </div>
        <div class="d-flex align-items-center gap-4">
            <!-- Realtime Clock -->
            <div class="realtime-clock d-none d-md-flex flex-column text-end">
                <span id="topbar-date" class="small fw-semibold" style="color: #6c757d !important;">Memuat...</span>
                <span id="topbar-time" class="fw-bold" style="color: #2c3e50 !important; font-size: 1.1rem;">--:--:--</span>
            </div>
            
            <!-- Notifikasi -->
            <div class="dropdown me-1 mt-1">
                <a href="#" class="text-dark position-relative" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell fs-4"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; padding: 0.25em 0.4em;">
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
                                <img id="profileImage" src="<?= asset_url('apps/assets/images/faces/1.jpg') ?>" onerror="this.src='<?= asset_url('apps/assets/images/faces/1.jpg') ?>'">
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
<script src="<?= asset_url('apps/assets/js/custom/change-password.js?v=99'); ?>"></script>
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
});

// Real-time Clock for Topbar
function updateTopbarClock() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateStr = now.toLocaleDateString('id-ID', options);
    
    let hours = String(now.getHours()).padStart(2, '0');
    let minutes = String(now.getMinutes()).padStart(2, '0');
    let seconds = String(now.getSeconds()).padStart(2, '0');
    
    document.getElementById('topbar-date').textContent = dateStr;
    document.getElementById('topbar-time').textContent = `${hours}:${minutes}:${seconds}`;
}

if(document.getElementById('topbar-time')) {
    updateTopbarClock();
    setInterval(updateTopbarClock, 1000);
}
</script>


