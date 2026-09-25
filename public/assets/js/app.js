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
    backdrop: false, // Pastikan tidak ada overlay backdrop gelap saat toast aktif
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

// ============================================================================
// Komponen Utama: PaperSelect (Custom Select Bertema Paper Card)
// ============================================================================
class PaperSelect {
    constructor(selectElement) {
        if (!selectElement || selectElement.dataset.paperSelectInitialized === 'true') {
            return;
        }

        this.select = selectElement;
        this.select.dataset.paperSelectInitialized = 'true';
        this.isOpen = false;
        this.focusedIndex = -1;

        this.init();
    }

    init() {
        this.buildDOM();
        this.bindEvents();
        this.syncWithSelect();
    }

    buildDOM() {
        // Wrapper container
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'paper-select-wrapper';

        // Warisi class ukuran dan layout dari select asli
        if (this.select.classList.contains('form-select-sm') || this.select.classList.contains('paper-select-sm')) {
            this.wrapper.classList.add('paper-select-sm');
        }
        if (this.select.classList.contains('form-select-lg') || this.select.classList.contains('paper-select-lg')) {
            this.wrapper.classList.add('paper-select-lg');
        }
        if (this.select.classList.contains('w-auto')) {
            this.wrapper.classList.add('w-auto');
        }
        if (this.select.classList.contains('w-100')) {
            this.wrapper.classList.add('w-100');
        }

        // Trigger Button
        this.trigger = document.createElement('button');
        this.trigger.type = 'button';
        this.trigger.className = 'paper-select-trigger';
        this.trigger.setAttribute('aria-haspopup', 'listbox');
        this.trigger.setAttribute('aria-expanded', 'false');
        if (this.select.disabled) {
            this.trigger.disabled = true;
        }

        this.label = document.createElement('span');
        this.label.className = 'paper-select-label';

        const caret = document.createElement('i');
        caret.className = 'bi bi-chevron-down paper-select-caret';

        this.trigger.appendChild(this.label);
        this.trigger.appendChild(caret);

        // Dropdown Menu Container
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'paper-select-dropdown';
        this.dropdown.setAttribute('role', 'listbox');

        // Isi pilihan opsi
        this.renderOptions();

        // Rangkai elemen
        this.wrapper.appendChild(this.trigger);
        this.wrapper.appendChild(this.dropdown);

        // Sembunyikan select asli dan pasang wrapper custom tepat setelahnya
        this.select.classList.add('paper-select-initialized');
        this.select.parentNode.insertBefore(this.wrapper, this.select.nextSibling);
    }

    renderOptions() {
        this.dropdown.innerHTML = '';
        this.optionsData = [];

        Array.from(this.select.options).forEach((opt, idx) => {
            const optEl = document.createElement('div');
            optEl.className = 'paper-select-option';
            optEl.setAttribute('role', 'option');
            optEl.dataset.value = opt.value;
            optEl.dataset.index = idx;

            if (opt.selected) {
                optEl.classList.add('selected');
                optEl.setAttribute('aria-selected', 'true');
                this.label.textContent = opt.textContent;
            }
            if (opt.disabled) {
                optEl.classList.add('disabled');
                optEl.setAttribute('aria-disabled', 'true');
            }

            const textSpan = document.createElement('span');
            textSpan.className = 'paper-select-option-text';
            textSpan.textContent = opt.textContent;

            const checkIcon = document.createElement('i');
            checkIcon.className = 'bi bi-check2 paper-select-check';

            optEl.appendChild(textSpan);
            optEl.appendChild(checkIcon);

            this.dropdown.appendChild(optEl);
            this.optionsData.push({ element: optEl, value: opt.value, text: opt.textContent, disabled: opt.disabled });
        });

        // Label default jika belum ada opsi terpilih
        if (!this.label.textContent && this.optionsData.length > 0) {
            const firstValid = this.optionsData.find(o => !o.disabled) || this.optionsData[0];
            this.label.textContent = firstValid.text;
        }
    }

    bindEvents() {
        // Klik tombol trigger untuk buka/tutup
        this.trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            if (this.select.disabled) return;
            this.toggle();
        });

        // Klik salah satu opsi dalam dropdown
        this.dropdown.addEventListener('click', (e) => {
            const optEl = e.target.closest('.paper-select-option');
            if (!optEl || optEl.classList.contains('disabled')) return;

            const val = optEl.dataset.value;
            this.selectValue(val);
        });

        // Navigasi Keyboard Aksesibel
        this.trigger.addEventListener('keydown', (e) => {
            if (this.select.disabled) return;

            if (e.key === 'ArrowDown' || e.key === 'Down') {
                e.preventDefault();
                if (!this.isOpen) {
                    this.open();
                } else {
                    this.focusNextOption();
                }
            } else if (e.key === 'ArrowUp' || e.key === 'Up') {
                e.preventDefault();
                if (!this.isOpen) {
                    this.open();
                } else {
                    this.focusPrevOption();
                }
            } else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (this.isOpen && this.focusedIndex >= 0) {
                    const opt = this.optionsData[this.focusedIndex];
                    if (opt && !opt.disabled) {
                        this.selectValue(opt.value);
                    }
                } else {
                    this.toggle();
                }
            } else if (e.key === 'Escape' || e.key === 'Esc') {
                if (this.isOpen) {
                    e.preventDefault();
                    this.close();
                }
            } else if (e.key === 'Tab') {
                if (this.isOpen) {
                    this.close();
                }
            }
        });

        // Tutup dropdown jika klik di luar elemen
        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.wrapper.contains(e.target)) {
                this.close();
            }
        });

        // Sinkronisasi jika select asli diubah nilainya via script atau event change
        this.select.addEventListener('change', () => {
            this.syncWithSelect();
        });

        if (this.select.form) {
            this.select.form.addEventListener('reset', () => {
                setTimeout(() => this.syncWithSelect(), 20);
            });
        }

        // Pantau mutasi jika opsi ditambahkan secara dinamis via JavaScript
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver(() => {
                this.renderOptions();
                this.syncWithSelect();
            });
            observer.observe(this.select, { childList: true, subtree: true, attributes: true });
        }
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        PaperSelect.closeAll(this);

        this.adjustPosition();
        this.wrapper.classList.add('is-open');
        this.trigger.setAttribute('aria-expanded', 'true');
        this.isOpen = true;

        const currentVal = this.select.value;
        const selectedIdx = this.optionsData.findIndex(o => o.value === currentVal);
        this.setFocusedIndex(selectedIdx >= 0 ? selectedIdx : 0);
    }

    close() {
        this.wrapper.classList.remove('is-open');
        this.trigger.setAttribute('aria-expanded', 'false');
        this.isOpen = false;
        this.clearFocus();
    }

    adjustPosition() {
        const rect = this.trigger.getBoundingClientRect();
        const dropdownHeight = 240;
        const spaceBelow = window.innerHeight - rect.bottom;
        const spaceAbove = rect.top;

        if (spaceBelow < dropdownHeight && spaceAbove > dropdownHeight) {
            this.wrapper.classList.add('is-dropup');
        } else {
            this.wrapper.classList.remove('is-dropup');
        }

        if (rect.left + 220 > window.innerWidth && rect.right <= window.innerWidth) {
            this.dropdown.style.left = 'auto';
            this.dropdown.style.right = '0';
        } else {
            this.dropdown.style.left = '0';
            this.dropdown.style.right = 'auto';
        }
    }

    selectValue(val) {
        const changed = this.select.value !== val;
        this.select.value = val;
        this.syncWithSelect();
        this.close();
        this.trigger.focus();

        if (changed) {
            // Trigger native input & change events agar form.submit() dan event listener bekerja
            this.select.dispatchEvent(new Event('input', { bubbles: true }));
            this.select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    syncWithSelect() {
        const currentVal = this.select.value;
        const selectedOpt = this.select.options[this.select.selectedIndex];
        if (selectedOpt) {
            this.label.textContent = selectedOpt.textContent;
        }

        this.dropdown.querySelectorAll('.paper-select-option').forEach(el => {
            const isSelected = el.dataset.value === currentVal;
            el.classList.toggle('selected', isSelected);
            el.setAttribute('aria-selected', isSelected ? 'true' : 'false');
        });

        if (this.select.disabled) {
            this.trigger.disabled = true;
            this.wrapper.classList.add('disabled');
        } else {
            this.trigger.disabled = false;
            this.wrapper.classList.remove('disabled');
        }
    }

    setFocusedIndex(idx) {
        this.clearFocus();
        if (idx >= 0 && idx < this.optionsData.length) {
            this.focusedIndex = idx;
            const el = this.optionsData[idx].element;
            el.classList.add('is-focused');
            el.scrollIntoView({ block: 'nearest' });
        }
    }

    focusNextOption() {
        let next = this.focusedIndex + 1;
        while (next < this.optionsData.length && this.optionsData[next].disabled) {
            next++;
        }
        if (next < this.optionsData.length) {
            this.setFocusedIndex(next);
        }
    }

    focusPrevOption() {
        let prev = this.focusedIndex - 1;
        while (prev >= 0 && this.optionsData[prev].disabled) {
            prev--;
        }
        if (prev >= 0) {
            this.setFocusedIndex(prev);
        }
    }

    clearFocus() {
        this.dropdown.querySelectorAll('.paper-select-option.is-focused').forEach(el => {
            el.classList.remove('is-focused');
        });
        this.focusedIndex = -1;
    }

    static closeAll(exceptInstance = null) {
        document.querySelectorAll('.paper-select-wrapper.is-open').forEach(wrapper => {
            if (!exceptInstance || wrapper !== exceptInstance.wrapper) {
                wrapper.classList.remove('is-open');
                const trigger = wrapper.querySelector('.paper-select-trigger');
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    static init(el) {
        if (!el || el.dataset.paperSelectInitialized === 'true') return null;
        return new PaperSelect(el);
    }

    static initAll(root = document) {
        const selectors = [
            'select.form-select-paper',
            'select.form-control-paper',
            'select.paper-select',
            'select[data-paper-select]',
            '.paper-card select:not([data-no-paper-select])'
        ];
        root.querySelectorAll(selectors.join(', ')).forEach(select => {
            PaperSelect.init(select);
        });
    }
}

window.PaperSelect = PaperSelect;

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

    // 5. Inisialisasi Otomatis Komponen Utama PaperSelect pada semua elemen Select bertema Paper
    if (window.PaperSelect) {
        window.PaperSelect.initAll();
    }
});

// ==========================================================================
// 6. Seamless In-Page Navigation (PJAX) & Persistent True Fullscreen Engine
// ==========================================================================
window.toggleEvotingFullscreen = function() {
    const isFS = !!(
        document.fullscreenElement || 
        document.webkitFullscreenElement || 
        document.body.classList.contains('monitoring-fs-active') || 
        document.body.classList.contains('results-fs-active')
    );

    if (!isFS) {
        const root = document.documentElement;
        if (root.requestFullscreen) {
            root.requestFullscreen().catch(() => {});
        } else if (root.webkitRequestFullscreen) {
            root.webkitRequestFullscreen();
        }
        window.setEvotingFullscreenUI(true);
    } else {
        if (document.fullscreenElement || document.webkitFullscreenElement) {
            if (document.exitFullscreen) {
                document.exitFullscreen().catch(() => {});
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
        }
        window.setEvotingFullscreenUI(false);
    }
};

window.setEvotingFullscreenUI = function(isFS) {
    const path = window.location.pathname;
    const isMonitoring = path.includes('/admin/monitoring');
    const isResults = path.includes('/admin/results');

    const fsIcon = document.getElementById('fsIcon');
    const fsText = document.getElementById('fsText');
    const btnFullscreen = document.getElementById('btnToggleFullscreen');

    if (isFS) {
        try { sessionStorage.setItem('evoting_fullscreen', '1'); } catch (e) {}

        if (isMonitoring) {
            document.body.classList.remove('results-fs-active');
            document.body.classList.add('monitoring-fs-active');
        } else if (isResults) {
            document.body.classList.remove('monitoring-fs-active');
            document.body.classList.add('results-fs-active');
        }

        if (fsIcon) fsIcon.className = 'bi bi-fullscreen-exit me-1';
        if (fsText) fsText.textContent = 'Keluar Layar';
        if (btnFullscreen) {
            btnFullscreen.classList.replace('btn-paper-primary', 'btn-paper-secondary');
        }
    } else {
        try { sessionStorage.removeItem('evoting_fullscreen'); } catch (e) {}

        document.body.classList.remove('monitoring-fs-active', 'results-fs-active');

        if (fsIcon) fsIcon.className = 'bi bi-arrows-fullscreen me-1';
        if (fsText) fsText.textContent = 'Layar Penuh';
        if (btnFullscreen) {
            btnFullscreen.classList.replace('btn-paper-secondary', 'btn-paper-primary');
        }

        if (window.location.search.includes('fs=1')) {
            const url = new URL(window.location);
            url.searchParams.delete('fs');
            window.history.replaceState({}, '', url.pathname + (url.search ? url.search : ''));
        }
    }

    // Trigger chart resize if chart exists
    setTimeout(() => {
        if (window._monitoringChart) {
            window._monitoringChart.resize();
        }
    }, 150);
};

// Global Listeners for Fullscreen Changes (e.g. ESC key)
document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement) {
        window.setEvotingFullscreenUI(false);
    } else {
        window.setEvotingFullscreenUI(true);
    }
});
document.addEventListener('webkitfullscreenchange', () => {
    if (!document.webkitFullscreenElement) {
        window.setEvotingFullscreenUI(false);
    } else {
        window.setEvotingFullscreenUI(true);
    }
});

// Popstate (Back/Forward) handler during fullscreen
window.addEventListener('popstate', (e) => {
    const isFS = !!(document.fullscreenElement || document.webkitFullscreenElement || sessionStorage.getItem('evoting_fullscreen') === '1');
    if (isFS && (location.pathname.includes('/admin/monitoring') || location.pathname.includes('/admin/results'))) {
        window.seamlessNavigate(location.pathname);
    }
});

// Seamless Page Navigation Helper without Reloading Document
window.seamlessNavigate = async function(targetUrl) {
    try {
        const response = await fetch(targetUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!response.ok) {
            window.location.href = targetUrl;
            return;
        }

        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        if (doc.title) {
            document.title = doc.title;
        }

        const cleanUrl = targetUrl.replace(/[?&]fs=1/, '');
        window.history.pushState({ url: cleanUrl }, '', cleanUrl);

        const currentMain = document.querySelector('main');
        const newMain = doc.querySelector('main');

        if (currentMain && newMain) {
            // Cleanup current view timers/charts
            if (typeof window._cleanupCurrentView === 'function') {
                window._cleanupCurrentView();
                window._cleanupCurrentView = null;
            }

            // Replace main container HTML
            currentMain.innerHTML = newMain.innerHTML;

            const isFS = !!(
                document.fullscreenElement || 
                document.webkitFullscreenElement || 
                sessionStorage.getItem('evoting_fullscreen') === '1'
            );

            if (targetUrl.includes('/admin/results')) {
                document.body.classList.remove('monitoring-fs-active');
                if (isFS) document.body.classList.add('results-fs-active');

                // Execute inserted script elements
                executeInsertedScripts(currentMain);

                if (typeof window.initResultsView === 'function') {
                    window.initResultsView();
                }
            } else if (targetUrl.includes('/admin/monitoring')) {
                document.body.classList.remove('results-fs-active');
                if (isFS) document.body.classList.add('monitoring-fs-active');

                // Execute inserted script elements
                executeInsertedScripts(currentMain);

                if (typeof window.initMonitoringView === 'function') {
                    window.initMonitoringView();
                }
            }
        } else {
            window.location.href = targetUrl;
        }
    } catch (err) {
        console.error('Seamless transition error:', err);
        window.location.href = targetUrl;
    }
};

function executeInsertedScripts(container) {
    const scripts = Array.from(container.querySelectorAll('script'));
    scripts.forEach(oldScript => {
        const newScript = document.createElement('script');
        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
        newScript.textContent = oldScript.textContent;
        oldScript.parentNode.replaceChild(newScript, oldScript);
    });
}

