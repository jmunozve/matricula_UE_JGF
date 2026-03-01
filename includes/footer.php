<script src="<?php echo $js_path; ?>jquery.min.js"></script>
    <script src="<?php echo $js_path; ?>bootstrap.bundle.min.js"></script>
    <script src="<?php echo $js_path; ?>sweetalert2.all.min.js"></script>

    <script>
        /**
         * Lógica Global del Sistema (Vanilla JS - Sin dependencias de $)
         */
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. --- Lógica de Sidebar (JS PURO) ---
            const menuToggle = document.getElementById('menu-toggle');
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    const elementsToToggle = ['.sidebar', '.header-fixed', '.breadcrumb-fixed', '.main-content'];
                    elementsToToggle.forEach(selector => {
                        const el = document.querySelector(selector);
                        if (el) el.classList.toggle('collapsed');
                    });
                });
            }

            // 2. --- MOTOR UNIVERSAL DE NOTIFICACIONES (SWEETALERT2) ---
            <?php if (isset($_SESSION['swal'])): ?>
                if (typeof Swal !== 'undefined') {
                    (function() {
                        const config = <?php echo json_encode($_SESSION['swal']); ?>;
                        
                        if (config.toast) {
                            Swal.fire({
                                icon: config.icon || 'info',
                                title: config.title || '',
                                html: config.html || config.text || '',
                                toast: true,
                                position: config.position || 'top-end',
                                showConfirmButton: false,
                                timer: config.timer || 3000,
                                timerProgressBar: true
                            });
                        } else {
                            Swal.fire({
                                icon: config.icon || 'info',
                                title: config.title || 'Aviso',
                                html: config.html || config.text || '',
                                confirmButtonColor: config.confirmButtonColor || '#0d6efd',
                                confirmButtonText: config.confirmButtonText || 'Entendido',
                                timer: config.timer || null,
                                showConfirmButton: config.showConfirmButton !== undefined ? config.showConfirmButton : true
                            });
                        }
                    })();
                    <?php unset($_SESSION['swal']); ?>
                }
            <?php endif; ?>
        });
    </script>