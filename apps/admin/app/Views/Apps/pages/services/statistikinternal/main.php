<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/statistikinternal/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content p-2 p-md-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class="mt-3"><b>Statistik Kepegawaian Internal</b></h3>
                    <p class="text-subtitle text-muted">Simojang | Kantor Regional III Badan Kepegawaian Negara</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="service-page-inline-actions">
                        <button type="button" class="btn btn-outline-primary js-service-reload">
                            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
                        </button>
                        <a href="javascript:history.back()" class="btn btn-primary">
                            <i class="bi bi-chevron-left fs-6"></i> <strong>Kembali</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <section class="row mb-3">
            <div class="col-md-12">
                <div class="service-ui-topbar mb-3 service-ui-static-topbar">
                    <div class="dropdown">
                        <button
                            class="btn btn-outline-primary dropdown-toggle px-4 py-2 fw-semibold d-flex align-items-center gap-2"
                            type="button" id="dropdownFilterBtn" data-bs-toggle="dropdown">
                            Pilih Tim Kerja / Bidang
                        </button>

                        <ul class="dropdown-menu shadow rounded-3 border-0 p-3 mt-2 statistikinternal-filter-dropdown">

                            <li id="filterList"></li>

                            <li>
                                <hr class="dropdown-divider my-2">
                            </li>

                            <li>
                                <button class="btn btn-primary w-100 fw-semibold" id="applyFilter">
                                    <i class="bi bi-check-circle me-1"></i> Terapkan
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center justify-content-end">
                        <button type="button" id="btnTambahPegawai" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#DataModal">
                            <i class="bi bi-plus me-2"></i>Tambah Data
                        </button>
                    </div>
                </div>

                <div id="activeFilterContainer" class="active-filters-container my-3 align-items-center flex-wrap gap-2" style="display: none;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small me-2"><i class="bi bi-funnel me-1"></i>Filter Aktif:</span>
                        <div class="active-filters-list d-flex flex-wrap gap-2"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="row g-3 mb-3">

            <div id="bup-alert" class="mb-0"></div>

            <!-- NAV TAB FILTER -->
            <div class="col-md-9 mt-0">
                <div class="card border shadow-sm">
                    <div class="card-body py-2 mt-2">
                        <ul class="nav nav-pills gap-2 mt-3 mb-1" id="pegawaiTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-pegawai" data-bs-toggle="pill"
                                    data-bs-target="#pane-pegawai" data-mode="pegawai" type="button" role="tab"
                                    aria-selected="true">
                                    Data Pegawai
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-unit" data-bs-toggle="pill"
                                    data-bs-target="#pane-pegawai" data-mode="bup" type="button" role="tab"
                                    aria-selected="false">
                                    Data Pegawai Menjelang BUP
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-sk" data-bs-toggle="pill" data-bs-target="#pane-master"
                                    type="button" role="tab" aria-selected="false">
                                    Master Data
                                </button>
                            </li>

                        </ul>

                        <div class="tab-content mt-4">
                            <div class="tab-pane fade show active" id="pane-pegawai" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="dataTable" class="table table-bordered table-hover nowrap">
                                        <thead>
                                            <tr>
                                                <th><strong></strong></th>
                                                <th><strong>NIP</strong></th>
                                                <th><strong>Nama</strong></th>
                                                <th><strong>Gender</strong></th>
                                                <th><strong>Generasi</strong></th>
                                                <th><strong>Tanggal Lahir</strong></th>
                                                <th><strong>Unit Kerja</strong></th>
                                                <th><strong>Unit SK</strong></th>
                                                <th><strong>Jenis Jabatan</strong></th>
                                                <th><strong>Jabatan</strong></th>
                                                <th><strong>Menikah</strong></th>
                                                <th><strong>Agama</strong></th>
                                                <th><strong>Pendidikan</strong></th>
                                                <th><strong>Golongan</strong></th>
                                                <th><strong>Pangkat</strong></th>
                                                <th><strong>TMT</strong></th>
                                                <th><strong>Telp/HP</strong></th>
                                                <th><strong>Email</strong></th>
                                                <th><strong>Update At</strong></th>
                                                <th><strong></strong></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th><strong></strong></th>
                                                <th><strong>NIP</strong></th>
                                                <th><strong>Nama</strong></th>
                                                <th><strong>Gender</strong></th>
                                                <th><strong>Generasi</strong></th>
                                                <th><strong>Tanggal Lahir</strong></th>
                                                <th><strong>Unit Kerja</strong></th>
                                                <th><strong>Unit SK</strong></th>
                                                <th><strong>Jenis Jabatan</strong></th>
                                                <th><strong>Jabatan</strong></th>
                                                <th><strong>Menikah</strong></th>
                                                <th><strong>Agama</strong></th>
                                                <th><strong>Pendidikan</strong></th>
                                                <th><strong>Golongan</strong></th>
                                                <th><strong>Pangkat</strong></th>
                                                <th><strong>TMT</strong></th>
                                                <th><strong>Telp/HP</strong></th>
                                                <th><strong>Email</strong></th>
                                                <th><strong>Update At</strong></th>
                                                <th><strong></strong></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="pane-bup" role="tabpanel"></div>
                            <div class="tab-pane fade" id="pane-master" role="tabpanel">
                                <div class="row g-3 mt-4 px-4">
                                    <div class="col-md-3 col-sm-4 col-6">
                                        <div class="card master-card text-center border"
                                            data-type="data_pegawai_pendidikan">
                                            <div class="card-body">
                                                <i class="bi bi-mortarboard fs-1"></i>
                                                <div class="mt-2 small fw-semibold">Pendidikan</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-sm-4 col-6">
                                        <div class="card master-card text-center border"
                                            data-type="data_pegawai_unit_kerja">
                                            <div class="card-body">
                                                <i class="bi bi-building fs-1"></i>
                                                <div class="mt-2 small fw-semibold">Unit Kerja</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-sm-4 col-6">
                                        <div class="card master-card text-center border"
                                            data-type="data_pegawai_unit_sk">
                                            <div class="card-body">
                                                <i class="bi bi-file-earmark-text fs-1"></i>
                                                <div class="mt-2 small fw-semibold">Unit SK</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-sm-4 col-6">
                                        <div class="card master-card text-center border"
                                            data-type="data_pegawai_jenis_jabatan">
                                            <div class="card-body">
                                                <i class="bi bi-person-badge fs-1"></i>
                                                <div class="mt-2 small fw-semibold">Jenis Jabatan</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-sm-4 col-6 mt-0">
                                        <div class="card master-card text-center border"
                                            data-type="data_pegawai_jenis_pegawai">
                                            <div class="card-body">
                                                <i class="bi bi-people fs-1"></i>
                                                <div class="mt-2 small fw-semibold">Jenis Pegawai</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-sm-4 col-6 mt-0">
                                        <div class="card master-card text-center border"
                                            data-type="data_pegawai_jabatan">
                                            <div class="card-body">
                                                <i class="bi bi-award fs-1"></i>
                                                <div class="mt-2 small fw-semibold">Jabatan</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-sm-4 col-6 mt-0">
                                        <div class="card master-card text-center border" data-type="data_pegawai_golongan">
                                            <div class="card-body">
                                                <i class="bi bi-star-half fs-1"></i>
                                                <div class="mt-2 small fw-semibold">Golongan</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-sm-4 col-6 mt-0">
                                        <div class="card master-card text-center border" data-type="data_pegawai_pangkat">
                                            <div class="card-body">
                                                <i class="bi bi-stars fs-1"></i>
                                                <div class="mt-2 small fw-semibold">Pangkat</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- STATISTIK PEGAWAI -->
            <div class="col-md-3 mt-0">
                <div class="card border shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-1">
                            Statistik Pegawai
                        </h6>

                        <div class="mb-1">
                            <small class="text-muted">Total Pegawai</small>
                            <h4 class="fw-bold mb-0" id="totalPegawai">0</h4>
                        </div>

                        <!-- GENDER -->
                        <div class="mb-1">
                            <div id="genderChart" class="statistikinternal-gender-chart"></div>
                            <small class="text-muted">Gender</small>
                            <div class="d-flex justify-content-between">
                                <span>Pria</span>
                                <span id="maleStat">0 (0%)</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Wanita</span>
                                <span id="femaleStat">0 (0%)</span>
                            </div>
                        </div>

                        <hr class="my-2">

                        <!-- GENERASI -->
                        <div>
                            <small class="text-muted">Generasi</small>
                            <div class="d-flex justify-content-between">
                                <span>Baby Boomer</span>
                                <span id="boomerStat">0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Gen X</span>
                                <span id="genxStat">0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Gen Y</span>
                                <span id="genyStat">0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Gen Z</span>
                                <span id="genzStat">0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Gen Alpha</span>
                                <span id="alphaStat">0</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </section>

        <div class="modal fade modal-force-rounded" id="DataModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="DataModalLabel" aria-hidden="true">

            <div class="modal-dialog modal-xl modal-fullscreen-lg-down modal-dialog-centered">
                <div class="modal-content">

                    <!-- HEADER -->
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="DataModalLabel">Tambah Data Pegawai</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- FORM -->
                    <form id="form-usulan" autocomplete="off">
                        <input type="hidden" name="key">

                        <div class="modal-body">
                            <div class="row g-3">

                                <!-- ROW 1 -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">NIP</label>
                                    <input type="text" name="nip" class="form-control">
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Nama</label>
                                    <input type="text" name="nama" class="form-control" required>
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">- Pilih -</option>
                                        <option value="1">Pria</option>
                                        <option value="2">Wanita</option>
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date" name="tgl_lahir" class="form-control">
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Pernikahan</label>
                                    <select name="menikah" class="form-select">
                                        <option value="">- Pilih -</option>
                                        <option value="Menikah">Menikah</option>
                                        <option value="Belum Menikah">Belum Menikah</option>
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Status Pegawai</label> 
                                    <select name="status_pegawai"
                                            class="form-select rounded select2-dynamic"
                                            data-source="data_pegawai_jenis_pegawai">
                                        <option value="">- Pilih -</option>
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Agama</label>
                                    <select name="agama"
                                            class="form-select rounded select2-dynamic"
                                            data-source="data_pegawai_agama">
                                        <option value="">- Pilih -</option>
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Pendidikan</label>
                                    <select name="pendidikan"
                                            class="form-select rounded select2-dynamic"
                                            data-source="data_pegawai_pendidikan">
                                        <option value="">- Pilih -</option>
                                    </select>
                                </div>

                                <!-- ROW 4 -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Jabatan</label>
                                    <input type="text" name="jabatan" class="form-control">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Golongan</label>
                                    <select name="golongan"
                                            class="form-select rounded select2-dynamic"
                                            data-source="data_pegawai_golongan">
                                        <option value="">- Pilih -</option>
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Pangkat</label>
                                    <select name="pangkat"
                                            class="form-select rounded select2-dynamic"
                                            data-source="data_pegawai_pangkat">
                                        <option value="">- Pilih -</option>
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">TMT Gol</label>
                                    <input type="date" name="tmt_gol" class="form-control">
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Unit Kerja</label>
                                    <select name="unit_kerja[]"
                                            class="form-select rounded select2-dynamic"
                                            data-source="data_pegawai_unit_kerja" multiple="multiple">
                                    </select>
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Unit SK</label>
                                       <select name="unit_sk"
                                            class="form-select rounded select2-dynamic"
                                            data-source="data_pegawai_unit_sk">
                                        <option value="">- Pilih -</option>
                                    </select>
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Jenis Jabatan</label>
                                    <select name="jenis_jabatan"
                                            class="form-select rounded select2-dynamic"
                                            data-source="data_pegawai_jenis_jabatan">
                                        <option value="">- Pilih -</option>
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Telp / HP</label>
                                    <input type="text" name="phone" class="form-control">
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>

                            </div>
                        </div>

                        <!-- FOOTER -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-primary btn-submit-form">
                                Simpan Data
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- Master Data Modal -->
        <div class="modal fade" id="masterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="masterModalLabel">Master Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary btn-sm" id="btnAddMaster">
                                + Tambah Data
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="masterTable" class="table table-bordered table-hover nowrap">
                                <thead id="masterTableHead"></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="modal fade" id="formMasterModal" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h6 class="modal-title" id="formMasterTitle">Tambah Data</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form id="formMaster">
                        <div class="modal-body">
                            <input type="hidden" name="id" id="master_id">
                            <input type="hidden" name="type" id="master_type">

                            <div id="formMasterDynamicFields">
                                <!-- Fields will be generated dynamically by modal.js -->
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/extensions/apexcharts/apexcharts.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/statistikinternal/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/statistikinternal/modal.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/statistikinternal/filter.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/statistikinternal/fetch.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/statistikinternal/tablesPegawai.js') ?>">
</script>
<?= $this->endSection(); ?>
