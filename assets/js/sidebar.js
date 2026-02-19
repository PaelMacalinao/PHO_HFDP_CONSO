/**
 * Sidebar: hamburger toggles sidebar visibility.
 * - Open: sidebar visible, main content has left margin.
 * - Closed: sidebar slides off-screen left, main content full width.
 * Smooth transition on both desktop and mobile.
 */
(function() {
    function init() {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        var toggle = document.getElementById('sidebar-toggle');
        if (!sidebar || !toggle) return;

        function open() {
            sidebar.classList.add('open');
            toggle.setAttribute('aria-expanded', 'true');
            if (overlay) {
                overlay.classList.add('is-visible');
                overlay.setAttribute('aria-hidden', 'false');
            }
            if (window.innerWidth < 769) document.body.style.overflow = 'hidden';
        }

        function close() {
            sidebar.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
            if (overlay) {
                overlay.classList.remove('is-visible');
                overlay.setAttribute('aria-hidden', 'true');
            }
            document.body.style.overflow = '';
        }

        function toggleSidebar() {
            if (sidebar.classList.contains('open')) close();
            else open();
        }

        // Only the hamburger button toggles the sidebar; header nav links (Dashboard, Add New Record) must not trigger it
        function handleToggleClick(e) {
            if (e.target.closest('a')) return;
            if (!toggle.contains(e.target)) return;
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        }
        toggle.addEventListener('click', handleToggleClick);

        if (overlay) overlay.addEventListener('click', close);

        // Start collapsed on every page load (desktop and mobile) to reduce visual clutter
        sidebar.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 769) {
                document.body.style.overflow = '';
                if (overlay) overlay.classList.remove('is-visible');
            } else if (!sidebar.classList.contains('open')) {
                if (overlay) overlay.classList.remove('is-visible');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
