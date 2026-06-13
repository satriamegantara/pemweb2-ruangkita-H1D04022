<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Akses Ditolak — <?php echo e(config('app.name')); ?></title>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <div class="flex min-h-screen flex-col">
            <?php echo $__env->make('layouts.partials.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <main class="flex flex-1 items-center justify-center px-4 py-20 sm:px-6">
                <div class="max-w-lg text-center">
                    <p class="text-5xl font-bold text-teal-600">403</p>
                    <h1 class="mt-4 text-2xl font-bold text-slate-900">Akses Ditolak</h1>
                    <p class="mt-3 text-slate-600">
                        <?php echo e($exception->getMessage() ?: 'Anda tidak memiliki izin untuk mengakses halaman ini.'); ?>

                    </p>
                    <a
                        href="<?php echo e(auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard')) : route('landing')); ?>"
                        class="mt-8 inline-flex rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700"
                    >
                        Kembali ke Dashboard
                    </a>
                </div>
            </main>

            <?php echo $__env->make('layouts.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    </body>
</html>
<?php /**PATH D:\Informatika\Sem 4\Pemrograman Web II\ruangkita\resources\views/errors/403.blade.php ENDPATH**/ ?>