<?php
use App\Models\Reservasi;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
?>

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
    <div class="space-y-6">
        <!-- Header -->
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-medium text-teal-700">Persetujuan Reservasi</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">Kelola Permintaan Reservasi</h1>
                    <p class="mt-2 text-slate-600">Setujui atau tolak permintaan reservasi ruangan yang masuk dari para pemohon.</p>
                </div>

                <a href="<?php echo e(route('admin.dashboard')); ?>"
                    class="inline-flex items-center text-sm font-medium text-teal-700 hover:text-teal-800">
                    &larr; Kembali ke Dashboard Admin
                </a>
            </div>
        </div>

        <!-- Filter -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700" for="search">Cari Ruangan</label>
                    <input id="search" type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Nama ruangan..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700" for="statusFilter">Filter Status</label>
                    <select id="statusFilter" wire:model.live="statusFilter"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                        <option value="menunggu">Menunggu</option>
                        <option value="disetujui">Disetujui</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Ruangan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Pemohon</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Jam</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->reservasiList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservasi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $statusPalette = match ($reservasi->status) {
                                    'disetujui' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-200'],
                                    'menunggu' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
                                    'ditolak' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200'],
                                    default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200'],
                                };
                            ?>
                            <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'reservasi-'.e($reservasi->id).''; ?>wire:key="reservasi-<?php echo e($reservasi->id); ?>" class="hover:bg-slate-50/50">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                    <?php echo e($reservasi->ruangan->nama ?? '-'); ?>

                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <div class="font-medium"><?php echo e($reservasi->user->name ?? '-'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo e($reservasi->user->email ?? ''); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 tabular-nums">
                                    <?php echo e($reservasi->tanggal->translatedFormat('d M Y')); ?>

                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 tabular-nums">
                                    <?php echo e(substr($reservasi->jam_mulai, 0, 5)); ?> — <?php echo e(substr($reservasi->jam_selesai, 0, 5)); ?>

                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium capitalize <?php echo e($statusPalette['bg']); ?> <?php echo e($statusPalette['text']); ?> <?php echo e($statusPalette['border']); ?>">
                                        <?php echo e($reservasi->status); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        <button type="button" wire:click="showDetail(<?php echo e($reservasi->id); ?>)"
                                            class="text-teal-700 transition hover:text-teal-800">
                                            Detail
                                        </button>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reservasi->status === 'menunggu'): ?>
                                            <button type="button" wire:click="approve(<?php echo e($reservasi->id); ?>)"
                                                class="text-emerald-600 transition hover:text-emerald-700">
                                                Setujui
                                            </button>
                                            <button type="button" wire:click="confirmReject(<?php echo e($reservasi->id); ?>)"
                                                class="text-rose-600 transition hover:text-rose-700">
                                                Tolak
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-slate-400">
                                        <svg class="mb-3 h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12h6m-3-3v6m-7.5 1.5a8.95 8.95 0 0115 0m-3-5a8.95 8.95 0 0115 0M4.5 4.5h15" />
                                        </svg>
                                        <p class="text-sm font-medium text-slate-600">Tidak ada data</p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->statusFilter === 'menunggu'): ?>
                                                Belum ada permintaan reservasi yang menunggu persetujuan.
                                            <?php else: ?>
                                                Tidak ada reservasi dengan status ini.
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->reservasiList->hasPages()): ?>
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-3">
                    <?php echo e($this->reservasiList->links()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Modal Detail -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedReservasi): ?>
        <?php $detail = $this->selectedReservasi; ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
            wire:click.self="closeDetail">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between">
                    <h3 class="text-lg font-bold text-slate-900">Detail Reservasi</h3>
                    <button type="button" wire:click="closeDetail" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Ruangan</dt>
                        <dd class="font-medium text-slate-900"><?php echo e($detail->ruangan->nama ?? '-'); ?></dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Tanggal</dt>
                        <dd class="font-medium text-slate-900 tabular-nums"><?php echo e($detail->tanggal->translatedFormat('l, d F Y')); ?></dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Waktu</dt>
                        <dd class="font-medium text-slate-900 tabular-nums"><?php echo e(substr($detail->jam_mulai, 0, 5)); ?> — <?php echo e(substr($detail->jam_selesai, 0, 5)); ?></dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Status</dt>
                        <dd class="font-medium capitalize text-slate-900"><?php echo e($detail->status); ?></dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Pemohon</dt>
                        <dd class="font-medium text-slate-900"><?php echo e($detail->user->name ?? '-'); ?></dd>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail->keterangan): ?>
                        <div>
                            <dt class="text-slate-500">Keterangan</dt>
                            <dd class="mt-1 text-slate-700"><?php echo e($detail->keterangan); ?></dd>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </dl>

                <div class="mt-6 flex justify-end">
                    <button type="button" wire:click="closeDetail"
                        class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Modal Reject dengan Alasan -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->rejectingId): ?>
        <?php $rej = $this->selectedReservasi; ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
            wire:click.self="closeRejectConfirm">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-bold text-slate-900">Tolak Reservasi?</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rej): ?>
                    <p class="mt-2 text-sm text-slate-600">
                        Reservasi <strong><?php echo e($rej->ruangan->nama ?? '-'); ?></strong> pada
                        <strong><?php echo e($rej->tanggal->translatedFormat('d M Y')); ?></strong> akan ditolak.
                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mt-4">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700" for="rejectReason">
                        Alasan Penolakan <span class="text-slate-400">(opsional)</span>
                    </label>
                    <textarea id="rejectReason" rows="3" wire:model="rejectReason"
                        placeholder="Contoh: Ruangan tidak tersedia pada waktu tersebut..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"></textarea>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="closeRejectConfirm"
                        class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                        Kembali
                    </button>
                    <button type="button" wire:click="submitReject"
                        class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                        Tolak Reservasi
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH D:\Informatika\Sem 4\Pemrograman Web II\ruangkita\storage\framework\views/livewire/views/00a9eaea.blade.php ENDPATH**/ ?>