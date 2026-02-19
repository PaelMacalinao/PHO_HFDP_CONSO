/**
 * Admin user dropdown: toggle on trigger click, close on outside click.
 * Uses .is-open class so dropdown is never clipped and Sign out stays visible.
 */
document.addEventListener('DOMContentLoaded', function() {
    var trigger = document.getElementById('admin-trigger');
    var dropdown = document.getElementById('admin-dropdown');
    if (!trigger || !dropdown) return;

    function open() {
        dropdown.classList.add('is-open');
        dropdown.setAttribute('aria-hidden', 'false');
        trigger.setAttribute('aria-expanded', 'true');
        document.addEventListener('click', closeOnOutside);
    }

    function close() {
        dropdown.classList.remove('is-open');
        dropdown.setAttribute('aria-hidden', 'true');
        trigger.setAttribute('aria-expanded', 'false');
        document.removeEventListener('click', closeOnOutside);
    }

    function closeOnOutside(e) {
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            close();
        }
    }

    trigger.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (dropdown.classList.contains('is-open')) {
            close();
        } else {
            open();
        }
    });
});
