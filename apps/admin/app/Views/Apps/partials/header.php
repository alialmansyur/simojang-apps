<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Simojang | Kanreg Tilu</title>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="shortcut icon" href="<?= asset_url('apps/assets/images/logo/favicon.svg') ?>" type="image/x-icon">
	<link rel="shortcut icon" href="<?= asset_url('apps/assets/images/logo/favicon.png') ?>" type="image/png">
	<link rel="stylesheet" href="<?= asset_url('apps/assets/css/main/app.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('apps/assets/css/main/app-dark.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('apps/assets/css/main/custom.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('apps/assets/extensions/bootstrap-icons/font/bootstrap-icons.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('apps/assets/extensions/toastify-js/src/toastify.css') ?>">

	<link rel="stylesheet" href="<?= asset_url('apps/assets/extensions/sweetalert2/sweetalert2.min.css') ?>">

	<link rel="stylesheet" href="<?= asset_url('apps/assets/extensions/select2/select2.min.css') ?>" />
	<link rel="stylesheet" href="<?= asset_url('apps/assets/extensions/select2/select2-bootstrap-5-theme.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('apps/assets/extensions/@dataTablesBund/jquery.dataTables.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('apps/assets/extensions/@dataTablesBund/buttons.dataTables.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('apps/assets/extensions/@dataTablesBund/responsive.dataTables.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/datatable-theme.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('apps/assets/extensions/apexcharts/apexcharts.css') ?>">
	<script src="<?= asset_url('apps/assets/js/custom/config.js') ?>"></script>
	<script src="<?= asset_url('apps/assets/extensions/sweetalert2/sweetalert2.all.min.js') ?>"></script>
	<script src="<?= asset_url('apps/assets/extensions/jquery/jquery.min.js') ?>"></script>
	<script src="<?= asset_url('apps/assets/extensions/toastify-js/src/toastify.js') ?>"></script>
	<script src="<?= asset_url('apps/assets/js/custom/menuactive.js') ?>"></script>
	<script src="<?= asset_url('apps/assets/extensions/@dataTablesBund/jquery.dataTables.min.js') ?>"></script>
	<script src="<?= asset_url('apps/assets/extensions/@dataTablesBund/dataTables.responsive.min.js') ?>"></script>
	<script src="<?= asset_url('apps/assets/extensions/@dataTablesBund/dataTables.buttons.min.js') ?>"></script>
	<script src="<?= asset_url('apps/assets/extensions/@dataTablesBund/buttons.html5.min.js') ?>"></script>
	<script src="<?= asset_url('apps/assets/extensions/@dataTablesBund/buttons.print.min.js') ?>"></script>
	<script src="<?= asset_url('apps/assets/extensions/@dataTablesBund/jszip.min.js') ?>"></script>
	<script src="<?= asset_url('apps/assets/extensions/@dataTablesBund/pdfmake.min.js') ?>"></script>
	<script src="<?= asset_url('apps/assets/extensions/@dataTablesBund/vfs_fonts.js') ?>"></script>
	<script src="<?= asset_url('apps/assets/extensions/select2/select2.min.js') ?>"></script>

</head>

<body>
    <!-- Initial Page Loader -->
    <div id="appGlobalLoadingOverlay" class="app-global-loading is-show" aria-live="polite" aria-hidden="false">
        <div class="app-global-loading-box">
            <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>
            <span id="appGlobalLoadingText">Memuat halaman...</span>
        </div>
    </div>
	<div id="app">
