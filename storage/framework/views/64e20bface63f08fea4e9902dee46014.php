<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component
{
    public function mount(): void
    {
        if (auth()->user()->isAdmin()) {
            $this->redirect(route('admin.dashboard'), navigate: true);
        }
    }
};
?>

<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <div>
            <p class="text-sm font-medium text-teal-700">Dashboard Pemohon</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">
                Selamat datang, <?php echo e(auth()->user()->name); ?>!
            </h1>
            <p class="mt-2 text-slate-600">
                NIM/NIP: <span class="font-medium text-slate-800"><?php echo e(auth()->user()->nim_nip); ?></span>
            </p>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            <a href="<?php echo e(route('reservasi.index')); ?>" class="rounded-xl border border-slate-200 p-5 transition hover:border-teal-300 hover:shadow-sm">
                <h2 class="font-semibold text-slate-900">Reservasi Ruang</h2>
                <p class="mt-2 text-sm text-slate-600">Ajukan pemesanan ruang kelas atau laboratorium.</p>
            </a>

            <a href="<?php echo e(route('jadwal.harian')); ?>" class="rounded-xl border border-slate-200 p-5 transition hover:border-teal-300 hover:shadow-sm">
                <h2 class="font-semibold text-slate-900">Jadwal Harian</h2>
                <p class="mt-2 text-sm text-slate-600">Lihat ketersediaan ruang per hari.</p>
            </a>
        </div>
    </div>
</div>
<?php /**PATH D:\Informatika\Sem 4\Pemrograman Web II\ruangkita\resources\views\pages\dashboard.blade.php ENDPATH**/ ?>