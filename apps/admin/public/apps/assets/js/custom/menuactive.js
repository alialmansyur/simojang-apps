/**
 * Sidebar Active Menu & Submenu Controller
 * SIMOJANG Apps
 */

(function () {
    // 1. Capture-phase wheel listener on window
    // This executes BEFORE any library and guarantees 100% reliable mouse wheel scroll up and down
    window.addEventListener('wheel', function (e) {
        const sidebar = document.getElementById('sidebar');
        if (sidebar && sidebar.contains(e.target)) {
            const menu = sidebar.querySelector('.sidebar-menu');
            if (menu) {
                // Ensure wrapper itself is never offset
                const wrapper = sidebar.querySelector('.sidebar-wrapper');
                if (wrapper && wrapper.scrollTop !== 0) {
                    wrapper.scrollTop = 0;
                }

                // Directly scroll the sidebar menu up and down
                menu.scrollTop += e.deltaY;
                e.preventDefault();
                e.stopPropagation();
            }
        }
    }, { passive: false, capture: true });

    function initSidebar() {
        const wrapper = document.querySelector('.sidebar-wrapper');
        if (wrapper) {
            // Keep wrapper locked at top 0
            wrapper.scrollTop = 0;
            wrapper.classList.remove('ps', 'ps--active-y', 'ps--active-x');
            if (wrapper._ps) {
                try { wrapper._ps.destroy(); } catch (e) { }
                wrapper._ps = null;
            }
            wrapper.querySelectorAll('.ps__rail-x, .ps__rail-y').forEach(function (el) {
                el.remove();
            });
        }

        // Clone links to strip any conflicting vanilla event listeners from third-party scripts
        document.querySelectorAll('.sidebar-item.has-sub > .sidebar-link').forEach(function (link) {
            const freshLink = link.cloneNode(true);
            link.parentNode.replaceChild(freshLink, link);
        });

        // Load Active Menu from LocalStorage or Current URL
        loadActiveMenu();
        updateSidebarSectionVisibility();
    }

    function loadActiveMenu() {
        let activeSubMenuId = localStorage.getItem('active_submenu');
        let activeMenuId = localStorage.getItem('active_menu');

        $('.sidebar-item, .submenu, .submenu-item').removeClass('active open');
        $('.submenu').hide();

        let matched = false;

        if (activeSubMenuId) {
            const $sub = $('#submenu' + activeSubMenuId);
            if ($sub.length) {
                $sub.addClass('active');
                const $parent = $sub.closest('.sidebar-item.has-sub');
                $parent.addClass('active open');
                $parent.children('.submenu').addClass('active').show();
                matched = true;
            }
        }

        if (activeMenuId && !matched) {
            let menuElement = $('#menu' + activeMenuId);
            if (menuElement.length) {
                menuElement.addClass('active');
                if (menuElement.hasClass('has-sub')) {
                    menuElement.addClass('open');
                    menuElement.children('.submenu').addClass('active').show();
                }
                matched = true;
            }
        }

        // Fallback: Synchronize active menu from current URL path
        if (!matched) {
            const pathSegments = window.location.pathname.replace(/^\/+/, '').split('/');
            const currentLeaf = pathSegments[pathSegments.length - 1] || '';
            const fullPath = window.location.pathname.replace(/^\/+/, '');
            if (fullPath) {
                $('.sidebar-menu a.menu-link').each(function () {
                    const href = $(this).attr('href') || '';
                    const linkPath = href.replace(/^https?:\/\/[^\/]+\//, '').replace(/^\/+/, '');
                    const linkSegments = linkPath.split('/');
                    const linkLeaf = linkSegments[linkSegments.length - 1] || '';

                    const isExactMatch = linkPath && linkPath !== '#' && (fullPath === linkPath || fullPath.startsWith(linkPath + '/'));
                    const isDashboardHomeMatch = (currentLeaf === 'dashboard' || currentLeaf === 'home') && (linkLeaf === 'dashboard' || linkLeaf === 'home');

                    if (isExactMatch || isDashboardHomeMatch) {
                        const $subItem = $(this).closest('.submenu-item');
                        if ($subItem.length) {
                            const $parentItem = $subItem.closest('.sidebar-item.has-sub');
                            $subItem.addClass('active');
                            $parentItem.addClass('active open');
                            $parentItem.children('.submenu').addClass('active').show();
                        } else {
                            $(this).closest('.sidebar-item').addClass('active');
                        }
                    }
                });
            }
        }
    }

    // Submenu Click Toggle Handler (Single Source of Truth)
    $(document).off('click', '.sidebar-item.has-sub > .sidebar-link').on('click', '.sidebar-item.has-sub > .sidebar-link', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $link = $(this);
        const $item = $link.closest('.sidebar-item.has-sub');
        const $submenu = $item.children('.submenu');

        const isCurrentlyOpen = $submenu.is(':visible') && $submenu.hasClass('active');

        if (isCurrentlyOpen) {
            $submenu.stop(true, true).slideUp(180, function () {
                $submenu.removeClass('active').removeAttr('style');
                $item.removeClass('open active');
            });
        } else {
            $submenu.stop(true, true).slideDown(180, function () {
                $submenu.addClass('active').show();
                $item.addClass('open active');
            });
        }
    });

    // Section Title Visibility Helper for Search
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

    // Reset Sidebar Search State
    function resetSidebarSearchState() {
        $('.sidebar-item, .submenu-item, .sidebar-title').removeClass('sidebar-search-hidden sidebar-search-match');
        loadActiveMenu();
    }

    // Live Search Menu Logic
    function applySidebarSearch(rawKeyword) {
        const keyword = String(rawKeyword || '').trim().toLowerCase();

        if (!keyword) {
            resetSidebarSearchState();
            return;
        }

        $('.sidebar-item, .submenu-item, .sidebar-title').removeClass('sidebar-search-hidden sidebar-search-match');

        $('.sidebar-item').each(function () {
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
            if (shouldShowParent) {
                $submenu.addClass('active').show();
                $item.addClass('open');
            } else {
                $submenu.removeClass('active').hide();
                $item.removeClass('open');
            }
        });

        updateSidebarSectionVisibility();
    }

    function focusSidebarSearch() {
        const $search = $('#sidebarMenuSearch');
        if (!$search.length) return;
        $search.trigger('focus');
        $search.trigger('select');
    }

    $(document).on('input', '#sidebarMenuSearch', function () {
        applySidebarSearch($(this).val());
    });

    $(document).on('click', '#sidebarMenuSearchShortcut', function () {
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
    };

    // Run on DOM Ready and window load
    $(document).ready(initSidebar);
    $(window).on('load', initSidebar);
})();

function logout() {
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('active_menu');
    localStorage.removeItem('active_menus');
    localStorage.removeItem('active_submenu');
    localStorage.clear();
    window.location.href = AppConfig.initGlobal + "logout";
}
