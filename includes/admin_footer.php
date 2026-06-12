        </div><!-- end content-area -->
    </div><!-- end main-content -->
</div><!-- end wrapper -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/**
 * MODAL RELOCATION FIX
 * Move all modal elements to <body> level so they escape any CSS stacking
 * context (overflow:hidden on .card-admin, transform on .stat-widget, etc.)
 * that prevents Bootstrap modals from rendering and interacting correctly.
 *
 * Also cleans up any orphaned .modal-backdrop that can block mouse clicks.
 */
document.addEventListener('DOMContentLoaded', function () {
    // Relocate every .modal to direct child of <body>
    document.querySelectorAll('.modal').forEach(function (modal) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });

    // Cleanup: remove stale backdrop and restore scroll/pointer-events if any
    // modal fires the 'hidden.bs.modal' event.
    document.addEventListener('hidden.bs.modal', function () {
        // Small delay to let Bootstrap finish its own cleanup
        setTimeout(function () {
            var openModals = document.querySelectorAll('.modal.show');
            if (openModals.length === 0) {
                // Remove any leftover backdrop elements
                document.querySelectorAll('.modal-backdrop').forEach(function (el) {
                    el.remove();
                });
                // Ensure body class & style are clean
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }
        }, 150);
    });
});
</script>
</body>
</html>
<?php ob_end_flush(); ?>
