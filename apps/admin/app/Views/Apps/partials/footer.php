</div>
<script src="<?= asset_url('apps/assets/js/bootstrap.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/app.js?v=1.0.0') ?>"></script>
<script>
// Release any sidebar scroll lock from third-party scripts
(function() {
    const wrapper = document.querySelector('.sidebar-wrapper');
    if (wrapper) {
        wrapper.scrollTop = 0;
        if (wrapper._ps) {
            try { wrapper._ps.destroy(); } catch(e){}
            wrapper._ps = null;
        }
        wrapper.classList.remove('ps', 'ps--active-y', 'ps--active-x');
        wrapper.querySelectorAll('.ps__rail-x, .ps__rail-y').forEach(el => el.remove());
    }
})();
</script>
<script src="<?= asset_url('apps/assets/extensions/select2/select2.min.js') ?>"></script>
<script>
window.APP_SESSION = Object.assign({}, window.APP_SESSION || {}, {
    jwtExpiresAt: <?= json_encode((int) (session()->get('jwt_expired_at') ?? 0)) ?>,
    loginUrl: <?= json_encode(base_url('/login')) ?>
});
</script>
<script src="<?= asset_url('apps/assets/js/custom/initGlobal.js?v=' . time()) ?>"></script>
</body>
</html>
