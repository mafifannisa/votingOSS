// E-Voting OSIS - App Scripts with SweetAlert2 Integration

// 1. Inisialisasi Custom SweetAlert2 Mixin untuk Paper Card Theme
window.SwalPaper = typeof Swal !== 'undefined' ? Swal.mixin({
    customClass: {
        popup: 'paper-swal-popup',
        title: 'paper-swal-title',
        htmlContainer: 'paper-swal-html',
        confirmButton: 'swal2-confirm btn-paper-primary',
        cancelButton: 'swal2-cancel btn-paper-secondary'
    },
    buttonsStyling: true
}) : null;

// Toast SweetAlert2 Mixin
window.PaperToast = typeof Swal !== 'undefined' ? Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
}) : null;

// Helper Alert Global Terstandarisasi
window.PaperAlert = {
    success: function(message, title = 'Berhasil!') {
        if (!window.SwalPaper) return;
        return window.SwalPaper.fire({
            icon: 'success',
            title: title,
            text: message,
            confirmButtonText: 'Tutup'
        });
    },

    error: function(message, title = 'Terjadi Kesalahan') {
        if (!window.SwalPaper) return;
        return window.SwalPaper.fire({
            icon: 'error',
            title: title,
            text: message,
            confirmButtonText: 'Mengerti'
        });
    },

    warning: function(message, title = 'Perhatian') {
        if (!window.SwalPaper) return;
        return window.SwalPaper.fire({
            icon: 'warning',
            title: title,
            text: message,
            confirmButtonText: 'Mengerti'
        });
    },

    info: function(message, title = 'Informasi') {
        if (!window.SwalPaper) return;
        return window.SwalPaper.fire({
            icon: 'info',
            title: title,
            text: message,
            confirmButtonText: 'Tutup'
        });
    },

    toast: function(message, type = 'success') {
        if (!window.PaperToast) return;
        return window.PaperToast.fire({
            icon: type,
            title: message
        });
    },

    confirm: function(options) {
        if (!window.SwalPaper) {
            if (confirm(options.text || options.title || 'Lanjutkan tindakan ini?')) {
                if (typeof options.onConfirm === 'function') options.onConfirm();
                return Promise.resolve({ isConfirmed: true });
            }
            return Promise.resolve({ isConfirmed: false });
        }

        const {
            title = 'Konfirmasi Tindakan',
            text = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
            html = null,
            icon = 'warning',
            confirmText = 'Ya, Lanjutkan',
            cancelText = 'Batal',
            isDanger = false,
            onConfirm = null
        } = options;

        const customClasses = {
            popup: 'paper-swal-popup',
            title: 'paper-swal-title',
            htmlContainer: 'paper-swal-html',
            confirmButton: isDanger ? 'swal2-confirm paper-btn-danger' : 'swal2-confirm btn-paper-primary',
            cancelButton: 'swal2-cancel btn-paper-secondary'
        };

        const config = {
            title: title,
            icon: icon,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true,
            focusCancel: isDanger,
            customClass: customClasses
        };

        if (html) {
            config.html = html;
        } else {
            config.text = text;
        }

        return window.SwalPaper.fire(config).then((result) => {
            if (result.isConfirmed && typeof onConfirm === 'function') {
                onConfirm();
            }
            return result;
        });
    }
};

// Polyfill window.alert agar otomatis menggunakan SweetAlert2 bertema Paper Card
if (typeof window !== 'undefined') {
    window.alert = function(message) {
        if (window.PaperAlert) {
            return window.PaperAlert.info(message);
        }
    };
}

document.addEventListener('DOMContentLoaded', () => {
    // 1. Eksekusi Notifikasi Flash Message dari Sesi PHP via SweetAlert2
    const flashData = document.getElementById('flashData');
    if (flashData && window.SwalPaper) {
        const successMsg = flashData.getAttribute('data-success');
        const errorMsg = flashData.getAttribute('data-error');
        const infoMsg = flashData.getAttribute('data-info');

        if (successMsg && successMsg.trim() !== '') {
            // Jika notifikasi sukses setelah voting suara
            if (successMsg.includes('Suara Anda telah berhasil direkam')) {
                window.SwalPaper.fire({
                    icon: 'success',
                    title: 'Pemberian Suara Berhasil!',
                    html: `<p class="mb-0 fs-6 text-muted">${successMsg}</p>`,
                    confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Selesai',
                    timer: 6000,
                    timerProgressBar: true
                });
            } else {
                window.PaperToast.fire({
                    icon: 'success',
                    title: successMsg
                });
            }
        } else if (errorMsg && errorMsg.trim() !== '') {
            window.SwalPaper.fire({
                icon: 'error',
                title: 'Perhatian',
                text: errorMsg,
                confirmButtonText: 'Tutup'
            });
        } else if (infoMsg && infoMsg.trim() !== '') {
            window.PaperToast.fire({
                icon: 'info',
                title: infoMsg
            });
        }
    }

    // 2. Interceptor Konfirmasi Form Otomatis [data-confirm]
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', (e) => {
            if (form.dataset.confirmed === 'true') {
                return true;
            }

            e.preventDefault();

            const message = form.getAttribute('data-confirm');
            const title = form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
            const btnText = form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';
            const isDanger = form.getAttribute('data-confirm-danger') === 'true';
            const icon = form.getAttribute('data-confirm-icon') || (isDanger ? 'warning' : 'question');

            window.PaperAlert.confirm({
                title: title,
                text: message,
                icon: icon,
                confirmText: btnText,
                cancelText: 'Batal',
                isDanger: isDanger,
                onConfirm: () => {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        });
    });

    // 3. Interceptor Konfirmasi Link / Tombol [data-confirm]
    document.querySelectorAll('a[data-confirm], button[data-confirm]:not([type="submit"])').forEach(el => {
        el.addEventListener('click', (e) => {
            if (el.dataset.confirmed === 'true') {
                return true;
            }
            e.preventDefault();
            const message = el.getAttribute('data-confirm');
            const title = el.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
            const isDanger = el.getAttribute('data-confirm-danger') === 'true';
            const icon = el.getAttribute('data-confirm-icon') || (isDanger ? 'warning' : 'question');
            const btnText = el.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';

            window.PaperAlert.confirm({
                title: title,
                text: message,
                icon: icon,
                confirmText: btnText,
                cancelText: 'Batal',
                isDanger: isDanger,
                onConfirm: () => {
                    el.dataset.confirmed = 'true';
                    if (el.tagName.toLowerCase() === 'a') {
                        window.location.href = el.href;
                    } else {
                        el.click();
                    }
                }
            });
        });
    });

    // 4. Auto-dismiss untuk alert bootstrap jika masih ada yang dirender di halaman
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) {
                    bsAlert.close();
                }
            }
        }, 5000);
    });
});
