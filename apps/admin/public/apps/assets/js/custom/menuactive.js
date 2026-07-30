$(document).ready(function () {
    function loadActiveMenu() {
        let activeSubMenuId = localStorage.getItem('active_submenu');
        let activeMenuId = localStorage.getItem('active_menu');

        $(".sidebar-item, .submenu, .submenu-item").removeClass("active");
        if (activeSubMenuId) {
            $("#submenu" + activeSubMenuId).addClass("active");
        }

        if (activeMenuId) {
            let menuElement = $("#menu" + activeMenuId);
            menuElement.addClass("active");

            if (menuElement.hasClass("has-sub")) {
                $("#submenu-parent" + activeMenuId).addClass("active");
            }
        }
    }

    function updateSidebarSectionVisibility() {
        $('.sidebar-title').each(function () {
            const $title = $(this);
            let hasVisibleItem = false;
            let $next = $title.next();

            while ($next.length && !$next.hasClass('sidebar-title')) {
                if ($next.is(':visible')) {
                    hasVisibleItem = true;
                    break;
                }
                $next = $next.next();
            }

            $title.toggleClass('sidebar-search-hidden', !hasVisibleItem);
        });
    }

    function resetSidebarSearchState() {
        $(".sidebar-item, .submenu-item, .sidebar-title").removeClass("sidebar-search-hidden sidebar-search-match");
        $(".submenu").removeClass("active");
        loadActiveMenu();
    }

    function applySidebarSearch(rawKeyword) {
        const keyword = String(rawKeyword || '').trim().toLowerCase();

        if (!keyword) {
            resetSidebarSearchState();
            return;
        }

        $(".sidebar-item, .submenu-item, .sidebar-title").removeClass("sidebar-search-hidden sidebar-search-match");

        $(".sidebar-item").each(function () {
            const $item = $(this);
            const $link = $item.children('.sidebar-link').first();
            const parentText = $link.text().replace(/\s+/g, ' ').trim().toLowerCase();
            const isParentMatch = parentText.includes(keyword);
            const $submenu = $item.children('.submenu').first();

            if (!$submenu.length) {
                $item.toggleClass('sidebar-search-hidden', !isParentMatch);
                $item.toggleClass('sidebar-search-match', isParentMatch);
                return;
            }

            let visibleChildren = 0;
            $submenu.children('.submenu-item').each(function () {
                const $subItem = $(this);
                const childText = $subItem.text().replace(/\s+/g, ' ').trim().toLowerCase();
                const isChildMatch = isParentMatch || childText.includes(keyword);
                $subItem.toggleClass('sidebar-search-hidden', !isChildMatch);
                $subItem.toggleClass('sidebar-search-match', isChildMatch && !isParentMatch);
                if (isChildMatch) {
                    visibleChildren += 1;
                }
            });

            const shouldShowParent = isParentMatch || visibleChildren > 0;
            $item.toggleClass('sidebar-search-hidden', !shouldShowParent);
            $item.toggleClass('sidebar-search-match', isParentMatch);
            $submenu.toggleClass('active', shouldShowParent);
        });

        updateSidebarSectionVisibility();
    }

    function focusSidebarSearch() {
        const $search = $('#sidebarMenuSearch');
        if (!$search.length) return;
        $search.trigger('focus');
        $search.trigger('select');
    }

    $('#sidebarMenuSearch').on('input', function () {
        applySidebarSearch($(this).val());
    });

    $('#sidebarMenuSearchShortcut').on('click', function () {
        focusSidebarSearch();
    });

    $(document).on('keydown', function (event) {
        const key = String(event.key || '').toLowerCase();
        const isEditableTarget = $(event.target).is('input, textarea, select, [contenteditable="true"], [contenteditable=""]');

        if ((event.ctrlKey || event.metaKey) && key === 'f' && !isEditableTarget) {
            event.preventDefault();
            focusSidebarSearch();
        }
    });

    window.updateActiveMenu = function (menuId, parentId) {
        localStorage.setItem('active_menu', menuId);
        if (parentId) {
            localStorage.setItem('active_submenu', menuId);
            localStorage.setItem('active_menu', parentId);
        } else {
            localStorage.removeItem('active_submenu');
        }
        loadActiveMenu();
    };

    loadActiveMenu();
    updateSidebarSectionVisibility();
});

function logout() {
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('active_menu');
    localStorage.removeItem('active_menus');
    localStorage.removeItem('active_submenu');
    localStorage.clear();
    window.location.href =  AppConfig.initGlobal + "logout";
}
