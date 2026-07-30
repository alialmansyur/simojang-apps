<div class="modal fade" role="dialog" id="change-password">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <a href="#" class="close" data-dismiss="modal"><em class="icon ni ni-cross-sm"></em></a>
            <div class="modal-body modal-body-xl m-4">
                <h5 class="title">Perbaharui Kata Sandi</h5>
                <p>Pengaturan ini membantu Anda menjaga keamanan akun Anda.</p>
                <form method="POST" id="changePasswordForm">
                    <div class="form-group mb-3">
                        <div class="form-control-wrap position-relative">
                            <input type="password" class="form-control form-control-lg rounded" name="o_password1"
                                id="password1" required placeholder="Kata sandi lama" autocomplete="new-password" style="padding-right: 2.5rem;">
                            <a tabindex="-1" href="#" class="position-absolute passcode-switch text-muted"
                                data-target="password1" style="right: 15px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                <i class="bi bi-eye icon-show"></i>
                                <i class="bi bi-eye-slash icon-hide d-none"></i>
                            </a>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0 fw-bold" style="color: #495057;">Kata Sandi Baru</label>
                            <a href="#" id="btnGeneratePassword" class="text-primary fw-bold text-decoration-none" style="font-size: 0.85rem;">
                                Buat Sandi Kuat
                            </a>
                        </div>
                        
                        <div class="form-control-wrap position-relative mb-2">
                            <input type="password" class="form-control form-control-lg rounded" name="o_password2"
                                id="password2" required placeholder="Kata sandi baru" autocomplete="new-password" style="padding-right: 2.5rem;">
                            <a tabindex="-1" href="#" class="position-absolute passcode-switch text-muted"
                                data-target="password2" style="right: 15px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                <i class="bi bi-eye icon-show"></i>
                                <i class="bi bi-eye-slash icon-hide d-none"></i>
                            </a>
                        </div>
                        
                        <div class="password-strength w-100 mb-3" style="display: none;" id="pwStrengthBox">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span style="font-size: 0.75rem;" class="text-muted">Kekuatan Sandi</span>
                                <span id="pwStrengthText" class="fw-bold text-danger" style="font-size: 0.75rem;">Sangat Lemah</span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 4px;">
                                <div id="pwStrengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%; transition: width 0.3s ease, background-color 0.3s ease;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <div class="form-control-wrap position-relative">
                            <input type="password" class="form-control form-control-lg rounded" name="o_password3"
                                id="password3" required placeholder="Masukan ulang kata sandi"
                                autocomplete="new-password" style="padding-right: 2.5rem;">
                            <a tabindex="-1" href="#" class="position-absolute passcode-switch text-muted"
                                data-target="password3" style="right: 15px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                <i class="bi bi-eye icon-show"></i>
                                <i class="bi bi-eye-slash icon-hide d-none"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary ml-1 btn-form-submit">Ubah Kata Sandi</button>
            </div>
        </div>
    </div>
</div>