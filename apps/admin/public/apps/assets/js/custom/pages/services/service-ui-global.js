(function (window, document) {
    'use strict';

    if (!window || !document) return;

    const floatingMenus = new Set();

    function resolveDropdownRoot(target) {
        if (!target) return null;
        if (target.classList && target.classList.contains('dropdown')) return target;
        return target.closest ? target.closest('.dropdown') : null;
    }

    function normalizeTopbars() {
        const topbars = document.querySelectorAll('.service-ui-topbar.service-ui-static-topbar');
        topbars.forEach(function (topbar) {
            const first = topbar.firstElementChild;
            if (first && first.classList.contains('service-ui-topbar-row')) return;

            const children = Array.from(topbar.children);
            if (!children.length) return;

            const row = document.createElement('div');
            row.className = 'row g-2 align-items-center w-100 service-ui-topbar-row';

            children.forEach(function (node, index) {
                const col = document.createElement('div');
                col.className = 'col-12 col-lg-auto service-ui-topbar-col';
                if (index === children.length - 1 && children.length > 1) {
                    col.className += ' ms-lg-auto text-lg-end';
                }
                row.appendChild(col);
                col.appendChild(node);
            });

            topbar.appendChild(row);
        });
    }

    function placeFloatingMenu(toggle, menu) {
        const rect = toggle.getBoundingClientRect();
        const alignRight = menu.classList.contains('dropdown-menu-end');
        const width = Math.max(rect.width, menu.offsetWidth, 180);
        const viewportPadding = 8;

        let left = alignRight ? (rect.right - width) : rect.left;
        left = Math.max(viewportPadding, Math.min(left, window.innerWidth - width - viewportPadding));
        const top = rect.bottom + 6;

        menu.style.position = 'absolute';
        menu.style.top = (top + window.scrollY) + 'px';
        menu.style.left = (left + window.scrollX) + 'px';
        menu.style.width = width + 'px';
        menu.style.zIndex = '2147483647';
        menu.style.pointerEvents = 'auto';
    }

    function floatDropdown(dropdownRoot) {
        if (!dropdownRoot) return;

        const toggle = dropdownRoot.querySelector('[data-bs-toggle="dropdown"]');
        const menu = dropdownRoot.querySelector('.dropdown-menu');
        if (!toggle || !menu) return;
        if (menu.dataset.floatingAttached === '1') {
            placeFloatingMenu(toggle, menu);
            return;
        }

        menu.dataset.floatingAttached = '1';
        menu.dataset.originParentSelector = '';
        menu.dataset.originNextId = '';

        const placeholder = document.createComment('service-ui-dropdown-placeholder');
        menu.parentNode.insertBefore(placeholder, menu);
        menu.__floatingPlaceholder = placeholder;
        menu.__floatingRoot = dropdownRoot;
        menu.classList.add('service-ui-dropdown-floating');
        document.body.appendChild(menu);

        placeFloatingMenu(toggle, menu);
        floatingMenus.add(menu);
    }

    function restoreDropdown(dropdownRoot) {
        if (!dropdownRoot) return;
        let targetMenu = null;
        floatingMenus.forEach(function (menu) {
            if (menu.__floatingRoot === dropdownRoot) {
                targetMenu = menu;
            }
        });
        if (!targetMenu) return;

        const placeholder = targetMenu.__floatingPlaceholder;
        if (placeholder && placeholder.parentNode) {
            placeholder.parentNode.insertBefore(targetMenu, placeholder);
            placeholder.parentNode.removeChild(placeholder);
        }

        targetMenu.classList.remove('service-ui-dropdown-floating');
        targetMenu.style.position = '';
        targetMenu.style.top = '';
        targetMenu.style.left = '';
        targetMenu.style.width = '';
        targetMenu.style.zIndex = '';
        targetMenu.dataset.floatingAttached = '0';
        targetMenu.__floatingPlaceholder = null;
        targetMenu.__floatingRoot = null;
        floatingMenus.delete(targetMenu);
    }

    function bindDropdownFloating() {
        document.addEventListener('shown.bs.dropdown', function (event) {
            const root = resolveDropdownRoot(event.target);
            if (!root || !root.closest('.service-ui-topbar')) return;
            root.classList.add('service-dropdown-container');
            floatDropdown(root);
        });

        document.addEventListener('hide.bs.dropdown', function (event) {
            const root = resolveDropdownRoot(event.target);
            if (!root || !root.closest('.service-ui-topbar')) return;
            restoreDropdown(root);
        });

        const reposition = function () {
            floatingMenus.forEach(function (menu) {
                const root = menu.__floatingRoot;
                if (!root) return;
                const toggle = root.querySelector('[data-bs-toggle="dropdown"]');
                if (!toggle) return;
                placeFloatingMenu(toggle, menu);
            });
        };

        window.addEventListener('resize', reposition, { passive: true });
        window.addEventListener('scroll', reposition, { passive: true, capture: true });
        document.addEventListener('scroll', reposition, { passive: true, capture: true });
    }

    function initServiceUiGlobal() {
        normalizeTopbars();
        bindDropdownFloating();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initServiceUiGlobal);
    } else {
        initServiceUiGlobal();
    }
})(window, document);
