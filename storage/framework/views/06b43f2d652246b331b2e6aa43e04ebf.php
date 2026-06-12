<header class="border-b border-slate-200 bg-white/80 backdrop-blur-sm">
    <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
        <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-2 font-semibold text-slate-900">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-600 text-sm font-bold text-white">RK</span>
            <span><?php echo e(config('app.name')); ?></span>
        </a>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
            <div class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isAdmin()): ?>
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="transition hover:text-teal-700">Dashboard</a>
                    <a href="<?php echo e(route('admin.ruangan.index')); ?>" class="transition hover:text-teal-700">Ruangan</a>
                    <a href="<?php echo e(route('admin.pemohon.index')); ?>" class="transition hover:text-teal-700">Pemohon</a>
                    <a href="<?php echo e(route('admin.persetujuan.index')); ?>" class="transition hover:text-teal-700">Persetujuan</a>
                <?php else: ?>
                    <a href="<?php echo e(route('dashboard')); ?>" class="transition hover:text-teal-700">Dashboard</a>
                    <a href="<?php echo e(route('reservasi.index')); ?>" class="transition hover:text-teal-700">Reservasi</a>
                    <a href="<?php echo e(route('jadwal.harian')); ?>" class="transition hover:text-teal-700">Jadwal</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-slate-600 sm:inline">
                    <?php echo e(auth()->user()->name); ?>

                    <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                        <?php echo e(auth()->user()->isAdmin() ? 'Admin' : 'Pemohon'); ?>

                    </span>
                </span>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button
                        type="submit"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                    >
                        Keluar
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
                <a href="<?php echo e(url('/')); ?>" class="transition hover:text-teal-700">Beranda</a>
                <a href="<?php echo e(url('/#fitur')); ?>" class="transition hover:text-teal-700">Fitur</a>
                <a href="<?php echo e(url('/#cara-kerja')); ?>" class="transition hover:text-teal-700">Cara Kerja</a>
            </div>

            <div class="flex items-center gap-3">
                <a
                    href="<?php echo e(route('login')); ?>"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                >
                    Masuk
                </a>
                <a
                    href="<?php echo e(route('register')); ?>"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700"
                >
                    Daftar
                </a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </nav>
</header>
<?php /**PATH D:\Informatika\Sem 4\Pemrograman Web II\ruangkita\resources\views/layouts/partials/navbar.blade.php ENDPATH**/ ?>