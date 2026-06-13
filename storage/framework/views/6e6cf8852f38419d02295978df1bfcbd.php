<?php
use App\Models\Reservasi;
use App\Models\User;
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
                    <p class="text-sm font-medium text-teal-700">Data Pemohon</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">Daftar Pengguna</h1>
                    <p class="mt-2 text-slate-600">Lihat daftar dosen dan mahasiswa yang terdaftar sebagai pemohon reservasi.</p>
                </div>

                <a href="<?php echo e(route('admin.dashboard')); ?>"
                    class="inline-flex items-center text-sm font-medium text-teal-700 hover:text-teal-800">
                    &larr; Kembali ke Dashboard
                </a>
            </div>

            <!-- Statistik -->
            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium text-slate-500">Total Pemohon</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900"><?php echo e($this->statistik['total']); ?></p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium text-slate-500">Total Admin</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900"><?php echo e($this->statistik['admin']); ?></p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium text-slate-500">Pemohon Aktif</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900"><?php echo e($this->statistik['aktif']); ?></p>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700" for="search">Cari Pengguna</label>
                    <input id="search" type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Nama, email, atau NIM/NIP..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700" for="roleFilter">Role</label>
                    <select id="roleFilter" wire:model.live="roleFilter"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                        <option value="pemohon">Pemohon</option>
                        <option value="admin">Admin</option>
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
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Pengguna</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">NIM / NIP</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Role</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Reservasi Disetujui</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->pemohonList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $initials = collect(explode(' ', trim($user->name)))
                                    ->map(fn($p) => mb_substr($p, 0, 1))
                                    ->take(2)
                                    ->implode('');
                                $initials = strtoupper($initials) ?: '?';
                            ?>
                            <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'pemohon-'.e($user->id).''; ?>wire:key="pemohon-<?php echo e($user->id); ?>" class="hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-teal-100 text-sm font-semibold text-teal-700 ring-1 ring-slate-200">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->foto): ?>
                                                <img src="<?php echo e(asset('storage/' . $user->foto)); ?>" alt="<?php echo e($user->name); ?>" class="h-full w-full object-cover">
                                            <?php else: ?>
                                                <?php echo e($initials); ?>

                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-semibold text-slate-900"><?php echo e($user->name); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <div class="truncate"><?php echo e($user->email); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm tabular-nums text-slate-700">
                                    <?php echo e($user->nim_nip ?: '—'); ?>

                                </td>
                                <td class="px-6 py-4">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->role === 'admin'): ?>
                                        <span class="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                                            Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                            Pemohon
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm tabular-nums text-slate-700">
                                    <span class="font-semibold text-slate-900"><?php echo e($user->total_reservasi); ?></span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <button type="button" wire:click="showDetail(<?php echo e($user->id); ?>)"
                                        class="text-teal-700 transition hover:text-teal-800">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-slate-400">
                                        <svg class="mb-3 h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                        <p class="text-sm font-medium text-slate-600">Tidak ada data</p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($this->search)): ?>
                                                Tidak ditemukan pengguna yang cocok dengan "<?php echo e($this->search); ?>".
                                            <?php else: ?>
                                                Belum ada pengguna dengan role <?php echo e($this->roleFilter); ?>.
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->pemohonList->hasPages()): ?>
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-3">
                    <?php echo e($this->pemohonList->links()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Modal Detail -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedPemohon): ?>
        <?php
            $detail = $this->selectedPemohon;
            $initials = collect(explode(' ', trim($detail->name)))
                ->map(fn($p) => mb_substr($p, 0, 1))
                ->take(2)
                ->implode('');
            $initials = strtoupper($initials) ?: '?';
        ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
            wire:click.self="closeDetail">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between">
                    <h3 class="text-lg font-bold text-slate-900">Detail Pengguna</h3>
                    <button type="button" wire:click="closeDetail" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Profil -->
                <div class="mt-5 flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-teal-100 text-lg font-bold text-teal-700 ring-2 ring-slate-200">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail->foto): ?>
                            <img src="<?php echo e(asset('storage/' . $detail->foto)); ?>" alt="<?php echo e($detail->name); ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                            <?php echo e($initials); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-base font-semibold text-slate-900"><?php echo e($detail->name); ?></p>
                        <p class="truncate text-sm text-slate-500"><?php echo e($detail->email); ?></p>
                        <div class="mt-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail->role === 'admin'): ?>
                                <span class="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                    Admin
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-700">
                                    Pemohon
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Statistik Reservasi -->
                <div class="mt-5 grid grid-cols-3 gap-2">
                    <div class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-center">
                        <p class="text-xs text-sky-700">Disetujui</p>
                        <p class="mt-0.5 text-lg font-bold text-sky-700"><?php echo e($detail->total_reservasi); ?></p>
                    </div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-center">
                        <p class="text-xs text-amber-700">Menunggu</p>
                        <p class="mt-0.5 text-lg font-bold text-amber-700"><?php echo e($detail->reservasi_menunggu); ?></p>
                    </div>
                    <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-center">
                        <p class="text-xs text-rose-700">Ditolak</p>
                        <p class="mt-0.5 text-lg font-bold text-rose-700"><?php echo e($detail->reservasi_ditolak); ?></p>
                    </div>
                </div>

                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">NIM / NIP</dt>
                        <dd class="font-medium text-slate-900"><?php echo e($detail->nim_nip ?: '—'); ?></dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Terdaftar Sejak</dt>
                        <dd class="font-medium text-slate-900 tabular-nums"><?php echo e($detail->created_at->translatedFormat('d F Y')); ?></dd>
                    </div>
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
</div><?php /**PATH D:\Informatika\Sem 4\Pemrograman Web II\ruangkita\storage\framework\views/livewire/views/63615477.blade.php ENDPATH**/ ?>