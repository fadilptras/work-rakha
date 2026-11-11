{{-- Komponen Generik UI Confirm & Delete Dialog / Toast
     File: resources/views/components/ui/confirm.blade.php
     
     Menyeragamkan seluruh dialog & toast konfirmasi hapus / pembatalan di aplikasi.
     Style default identik dengan standar Warehouse Operations (SweetAlert2 Light):
     - Icon: warning (lingkaran tanda seru oranye)
     - Confirm button: #ef4444 (Ya, batalkan!)
     - Cancel button: #94a3b8 (Batal)
     
     Cara Pakai:
     1. JavaScript (Promise / Callback):
        uiConfirmDelete({
            title: 'Batalkan baris ini?',
            text: 'Stok akan dikembalikan via reversal. Jejak audit tetap tersimpan.',
            confirmText: 'Ya, batalkan!',
            onConfirm: () => { ... }
        });
        
        // atau pakai await:
        if (await uiConfirmDelete('Batalkan baris ini?')) { ... }
        
     2. Tombol HTML Otomatis (tanpa nulis JS):
        <button type="button"
            data-confirm-delete
            data-title="Batalkan baris ini?"
            data-text="Stok akan dikembalikan via reversal. Jejak audit tetap tersimpan."
            data-form="id-form-del">
            Hapus
        </button>
--}}

@once
<script>
(function () {
    if (window.__uiConfirmInit) return;
    window.__uiConfirmInit = true;

    /**
     * Konfirmasi generik dengan SweetAlert2
     * @param {Object} options 
     * @returns {Promise<boolean>}
     */
    window.uiConfirm = function (options) {
        options = options || {};
        var title = options.title || 'Konfirmasi';
        var text = options.text || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        var icon = options.icon || 'warning';
        var confirmText = options.confirmText || 'Ya, Lanjutkan';
        var cancelText = options.cancelText || 'Batal';
        var confirmColor = options.confirmColor || '#ef4444';
        var cancelColor = options.cancelColor || '#94a3b8';
        var onConfirm = typeof options.onConfirm === 'function' ? options.onConfirm : null;
        var onCancel = typeof options.onCancel === 'function' ? options.onCancel : null;

        if (typeof Swal === 'undefined') {
            var ok = confirm((title ? title + '\n' : '') + text);
            if (ok && onConfirm) onConfirm();
            if (!ok && onCancel) onCancel();
            return Promise.resolve(ok);
        }

        return Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: cancelColor,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: false,
            focusCancel: true,
            customClass: {
                popup: '!rounded-2xl !p-6 !shadow-2xl'
            }
        }).then(function (result) {
            var confirmed = Boolean(result.isConfirmed);
            if (confirmed && onConfirm) onConfirm();
            if (!confirmed && onCancel) onCancel();
            return confirmed;
        });
    };

    /**
     * Shortcut seragam khusus Konfirmasi Hapus / Pembatalan Baris (Reversal)
     * Style & warna identik dengan standar Warehouse Operations:
     * - icon: warning
     * - confirm: '#ef4444' (Ya, batalkan!)
     * - cancel: '#94a3b8' (Batal)
     * 
     * @param {Object|string} optionsOrTitle 
     * @param {string} [optionalText] 
     * @returns {Promise<boolean>}
     */
    window.uiConfirmDelete = function (optionsOrTitle, optionalText) {
        var opts = {};
        if (typeof optionsOrTitle === 'string') {
            opts.title = optionsOrTitle;
            if (optionalText) opts.text = optionalText;
        } else if (typeof optionsOrTitle === 'object' && optionsOrTitle !== null) {
            opts = Object.assign({}, optionsOrTitle);
        }

        return window.uiConfirm({
            title: opts.title || 'Batalkan baris ini?',
            text: opts.text || 'Stok akan dikembalikan via reversal. Jejak audit tetap tersimpan.',
            icon: opts.icon || 'warning',
            confirmButtonColor: opts.confirmColor || '#ef4444',
            cancelButtonColor: opts.cancelColor || '#94a3b8',
            confirmButtonText: opts.confirmText || 'Ya, batalkan!',
            cancelButtonText: opts.cancelText || 'Batal',
            onConfirm: opts.onConfirm,
            onCancel: opts.onCancel
        });
    };

    /**
     * Helper onsubmit form generik (menggantikan confirmSubmit konvensional)
     */
    window.uiConfirmSubmit = function (e, message, title) {
        if (e && e.preventDefault) e.preventDefault();
        var el = e ? (e.target || e.currentTarget) : null;
        var form = el && el.tagName === 'FORM' ? el : (el && el.closest ? el.closest('form') : null);

        return window.uiConfirm({
            title: title || 'Konfirmasi Tindakan',
            text: message || 'Apakah Anda yakin ingin memproses formulir ini?',
            icon: 'warning',
            confirmColor: '#2563eb',
            cancelColor: '#94a3b8',
            confirmText: 'Ya, Lanjutkan',
            cancelText: 'Batal',
            onConfirm: function () {
                if (form && typeof form.submit === 'function') {
                    form.submit();
                }
            }
        });
    };

    // Delegasi klik otomatis untuk elemen ber-atribut [data-confirm-delete] atau [data-confirm]
    document.addEventListener('click', function (e) {
        if (!e.target || !e.target.closest) return;

        var btn = e.target.closest('[data-confirm-delete]');
        if (btn) {
            e.preventDefault();
            var title = btn.dataset.title || btn.getAttribute('title') || 'Batalkan baris ini?';
            var text = btn.dataset.text || 'Stok akan dikembalikan via reversal. Jejak audit tetap tersimpan.';
            var formId = btn.dataset.form || btn.dataset.formId;
            var form = formId ? document.getElementById(formId) : btn.closest('form');

            window.uiConfirmDelete({
                title: title,
                text: text,
                confirmText: btn.dataset.confirmText || 'Ya, batalkan!',
                cancelText: btn.dataset.cancelText || 'Batal',
                onConfirm: function () {
                    if (form && typeof form.submit === 'function') {
                        form.submit();
                    } else if (btn.dataset.actionUrl) {
                        window.location.href = btn.dataset.actionUrl;
                    }
                }
            });
            return;
        }

        var btnGen = e.target.closest('[data-confirm]');
        if (btnGen) {
            e.preventDefault();
            var gTitle = btnGen.dataset.confirmTitle || 'Konfirmasi';
            var gText = btnGen.dataset.confirm || 'Apakah Anda yakin?';
            var gForm = btnGen.dataset.form ? document.getElementById(btnGen.dataset.form) : btnGen.closest('form');

            window.uiConfirm({
                title: gTitle,
                text: gText,
                confirmText: btnGen.dataset.confirmBtn || 'Ya, Lanjutkan',
                cancelText: btnGen.dataset.cancelBtn || 'Batal',
                confirmColor: btnGen.dataset.confirmColor || '#2563eb',
                onConfirm: function () {
                    if (gForm && typeof gForm.submit === 'function') {
                        gForm.submit();
                    }
                }
            });
        }
    });
})();
</script>
@endonce

