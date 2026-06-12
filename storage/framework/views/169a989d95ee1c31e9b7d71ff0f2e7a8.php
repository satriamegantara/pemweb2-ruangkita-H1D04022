<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Persetujuan Reservasi')] class extends Component
{
    //
};
?>

<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">Persetujuan Reservasi</h1>
        <p class="mt-2 text-slate-600">Alur persetujuan reservasi akan diimplementasikan pada commit berikutnya.</p>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="mt-6 inline-flex text-sm font-medium text-teal-700 hover:text-teal-800">
            &larr; Kembali ke Dashboard Admin
        </a>
    </div>
</div>
<?php /**PATH D:\Informatika\Sem 4\Pemrograman Web II\ruangkita\resources\views\pages\admin\persetujuan\index.blade.php ENDPATH**/ ?>