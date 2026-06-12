<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard Admin')] class extends Component
{
    //
};
?>

<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <div>
            <p class="text-sm font-medium text-teal-700">Dashboard Admin</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">
                Halo, <?php echo e(auth()->user()->name); ?>

            </h1>
            <p class="mt-2 text-slate-600">Kelola ruangan, pemohon, dan persetujuan reservasi dari sini.</p>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a href="<?php echo e(route('admin.ruangan.index')); ?>" class="rounded-xl border border-slate-200 p-5 transition hover:border-teal-300 hover:shadow-sm">
                <h2 class="font-semibold text-slate-900">Kelola Ruangan</h2>
                <p class="mt-2 text-sm text-slate-600">CRUD data ruang, kapasitas, dan fasilitas.</p>
            </a>

            <a href="<?php echo e(route('admin.pemohon.index')); ?>" class="rounded-xl border border-slate-200 p-5 transition hover:border-teal-300 hover:shadow-sm">
                <h2 class="font-semibold text-slate-900">Data Pemohon</h2>
                <p class="mt-2 text-sm text-slate-600">Lihat daftar dosen dan mahasiswa terdaftar.</p>
            </a>

            <a href="<?php echo e(route('admin.persetujuan.index')); ?>" class="rounded-xl border border-slate-200 p-5 transition hover:border-teal-300 hover:shadow-sm">
                <h2 class="font-semibold text-slate-900">Persetujuan Reservasi</h2>
                <p class="mt-2 text-sm text-slate-600">Setujui atau tolak pengajuan reservasi ruang.</p>
            </a>
        </div>
    </div>
</div>
<?php /**PATH D:\Informatika\Sem 4\Pemrograman Web II\ruangkita\resources\views\pages\admin\dashboard.blade.php ENDPATH**/ ?>