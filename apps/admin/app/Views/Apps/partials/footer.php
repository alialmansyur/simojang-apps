</div>
<script src="<?= asset_url('apps/assets/js/bootstrap.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/app.js') ?>"></script>
<script src="<?= asset_url('apps/assets/extensions/select2/select2.min.js') ?>"></script>
<script>
window.APP_SESSION = Object.assign({}, window.APP_SESSION || {}, {
    jwtExpiresAt: <?= json_encode((int) (session()->get('jwt_expired_at') ?? 0)) ?>,
    loginUrl: <?= json_encode(base_url('/login')) ?>
});
</script>
<script src="<?= asset_url('apps/assets/js/custom/initGlobal.js') ?>"></script>
</body>
</html>
