<?= view('Apps/partials/header'); ?>
<?= $this->renderSection('style'); ?>
<?= view('Apps/partials/sidebar'); ?>
<div id="main" class="mt-0 pl-3 pr-3 pt-0 bg-pattern">
    <header>
        <nav class="navbar navbar-expand navbar-light navbar-top px-2 py-0 mt-4">
            <div class="container-fluid p-0">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-lg-0">
                        <li class="nav-item dropdown me-1">
                            <button type="button" class="btn btn-primary dropdown-toggle me-2 ps-4"
                                data-bs-toggle="dropdown">
                                Notifikasi <span class="badge bg-transparent">0</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                <li>
                                    <h6 class="dropdown-header">Notifikasi</h6>
                                </li>
                                <li><a class="dropdown-item" href="#">Tidak ada untuk saat ini</a></li>
                            </ul>
                        </li>
                    </ul>
                    <div class="dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-menu d-flex">
                                <div class="user-img d-flex align-items-center">
                                    <div class="avatar avatar-lg">
                                        <img id="profileImage"
                                            src="<?= asset_url('apps/assets/images/faces/1.jpg') ?>">
                                    </div>
                                </div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 border-0 p-2 mt-2 app-dropdown-menu"
                            aria-labelledby="dropdownMenuButton">
                            <li>
                                <h6 class="dropdown-header">Hello, <?= $seslog['username'] ?> !</h6>
                            </li>
                            <li><a href="<?= base_url('/profil') ?>" class="dropdown-item"><i
                                        class="icon-mid bi bi-person me-2"></i>
                                    Lihat Profil</a></li>
                            <li><a class="dropdown-item app-clickable" data-bs-toggle="modal" data-bs-target="#change-password"><i class="icon-mid bi bi-gear me-2"></i>
                                    Ubah Kata Sandi</a></li>
                            <li><a class="dropdown-item" href="#" onclick="logout()"><i
                                        class="icon-mid bi bi-box-arrow-left me-2 text-danger"></i>
                                    Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="section px-2">
        <div class="profile-card mb-3">
            <h3 class="fw-bold mt-3 text-white app-hero-title"><strong> Halo,
                    <br><?= $seslog['fullname']; ?></strong>
            </h3>
            <h4 class="text-white app-hero-subtitle">Apa yang akan kamu kerjakan hari ini ?</h4>
        </div>
    </div>

    <div id="main-content-inner">
        <?= $this->renderSection('content'); ?>
    </div>
</div>
<?= view('Apps/partials/modal/changepass'); ?>
<?= view('Apps/partials/footer'); ?>
<script src="<?= base_url('apps/assets/js/custom/change-password.js?v=3'); ?>"></script>
<?= $this->renderSection('scripts'); ?>
