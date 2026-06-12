<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'timeout' => 3500,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'timeout' => 3500,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    data-toast-root
    class="pointer-events-none fixed inset-x-0 top-4 z-50 px-4 sm:top-6 sm:px-6"
>
    <div class="mx-auto flex max-w-6xl justify-end">
        <div
            data-toast
            class="pointer-events-auto hidden w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10 ring-1 ring-slate-900/5"
            role="status"
            aria-live="polite"
            aria-atomic="true"
        >
            <div class="flex items-start gap-4 p-4 sm:p-5">
                <div
                    data-toast-icon
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white"
                >
                    <span data-toast-icon-slot class="hidden"><?php echo e($successIcon ?? ''); ?></span>
                </div>

                <div class="min-w-0 flex-1">
                    <p data-toast-title class="text-sm font-semibold text-slate-900">Sukses</p>
                    <p data-toast-message class="mt-1 text-sm leading-6 text-slate-600"></p>
                </div>

                <button
                    type="button"
                    data-toast-close
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Tutup notifikasi"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l8 8M14 6l-8 8" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            const root = document.currentScript.closest('[data-toast-root]');

            if (!root) {
                return;
            }

            const toast = root.querySelector('[data-toast]');
            const iconBox = root.querySelector('[data-toast-icon]');
            const iconSlot = root.querySelector('[data-toast-icon-slot]');
            const titleNode = root.querySelector('[data-toast-title]');
            const messageNode = root.querySelector('[data-toast-message]');
            const closeButton = root.querySelector('[data-toast-close]');

            const icons = {
                success: <?php echo json_encode(trim((string) ($successIcon ?? '')), 15, 512) ?>,
                error: <?php echo json_encode(trim((string) ($errorIcon ?? '')), 15, 512) ?>,
                info: <?php echo json_encode(trim((string) ($infoIcon ?? '')), 15, 512) ?>,
                warning: <?php echo json_encode(trim((string) ($warningIcon ?? '')), 15, 512) ?>,
            };

            const variantClasses = {
                success: ['bg-emerald-600'],
                error: ['bg-rose-600'],
                info: ['bg-sky-600'],
                warning: ['bg-amber-500'],
            };

            let hideTimer = null;

            const hideToast = () => {
                toast.classList.add('hidden');
                toast.dataset.visible = 'false';
            };

            const showToast = ({ type = 'success', title = 'Notifikasi', message = '' }) => {
                const normalizedType = ['success', 'error', 'info', 'warning'].includes(type) ? type : 'success';

                titleNode.textContent = title;
                messageNode.textContent = message;

                iconBox.className = 'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white';
                iconBox.classList.add(...variantClasses[normalizedType]);

                const iconMarkup = icons[normalizedType] || icons.success;
                iconSlot.innerHTML = iconMarkup || '';

                toast.classList.remove('hidden');
                toast.dataset.visible = 'true';

                if (hideTimer) {
                    window.clearTimeout(hideTimer);
                }

                hideTimer = window.setTimeout(hideToast, <?php echo e((int) $timeout); ?>);
            };

            closeButton.addEventListener('click', hideToast);

            if (window.__ruangkitaToastCleanup) {
                window.__ruangkitaToastCleanup();
            }

            window.__ruangkitaToastCleanup = Livewire.on('notify', (event) => {
                const payload = event ?? {};

                showToast(payload);
            });
        });
    </script>
</div><?php /**PATH D:\Informatika\Sem 4\Pemrograman Web II\ruangkita\resources\views\components\toast.blade.php ENDPATH**/ ?>