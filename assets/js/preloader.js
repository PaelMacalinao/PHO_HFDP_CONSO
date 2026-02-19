/**
 * Full-screen page preloader: 2s delay then fade-out.
 * Ensures the loader is visible long enough for a polished feel.
 */
(function() {
    var PRELOAD_DELAY_MS = 300;
    var FADEOUT_DURATION_MS = 500;

    function run() {
        var loader = document.getElementById('page-preloader');
        if (!loader) return;

        setTimeout(function() {
            loader.classList.add('loaded');
            setTimeout(function() {
                loader.style.display = 'none';
            }, FADEOUT_DURATION_MS);
        }, PRELOAD_DELAY_MS);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
