<?php

use App\Models\Reservasi;
use App\Models\Ruangan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Jadwal Harian')] class extends Component {
    public ?int $ruanganId = null;

    public string $tanggal = '';

    public function mount(): void
    {
        $this->tanggal = now()->toDateString();
        // Default ke ruangan pertama jika ada
        $first = Ruangan::query()->orderBy('nama')->first();
        $this->ruanganId = $first?->id;
    }

    #[Computed]
    public function ruanganList()
    {
        return Ruangan::query()->orderBy('nama')->get();
    }

    #[Computed]
    public function ruangan(): ?Ruangan
    {
        if (!$this->ruanganId) {
            return null;
        }

        return Ruangan::query()->find($this->ruanganId);
    }

    #[Computed]
    public function jadwal()
    {
        $jamBuka = intval(substr($this->ruangan?->jam_buka ?? '07:00:00', 0, 2));
        $jamTutup = intval(substr($this->ruangan?->jam_tutup ?? '21:00:00', 0, 2));

        if (!$this->ruanganId || !$this->tanggal) {
            return [];
        }

        $reservasi = Reservasi::query()
            ->with('user')
            ->where('ruangan_id', $this->ruanganId)
            ->whereDate('tanggal', \Carbon\Carbon::parse($this->tanggal))
            ->whereIn('status', ['menunggu', 'disetujui'])
            ->orderBy('jam_mulai')
            ->get();

        // Bangun peta slot per jam
        $slots = [];
        for ($h = $jamBuka; $h < $jamTutup; $h++) {
            $jamMulai = str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00:00';
            $jamSelesai = str_pad((string) ($h + 1), 2, '0', STR_PAD_LEFT) . ':00:00';

            $slot = [
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'jam_label' => str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00',
                'status' => 'kosong',
                'reservasi' => null,
            ];

            // Cari reservasi yang overlap dengan slot ini
            foreach ($reservasi as $r) {
                if ($r->jam_mulai < $jamSelesai && $r->jam_selesai > $jamMulai) {
                    // Prioritas: disetujui > menunggu
                    if ($slot['status'] === 'kosong' || ($slot['status'] === 'menunggu' && $r->status === 'disetujui')) {
                        $slot['status'] = $r->status;
                        $slot['reservasi'] = $r;
                    }
                }
            }

            $slots[$h] = $slot;
        }

        return $slots;
    }

    #[Computed]
    public function ringkasan(): array
    {
        $jadwal = $this->jadwal;

        $tersedia = 0;
        $tidakTersedia = 0;

        foreach ($jadwal as $slot) {
            $slot['status'] === 'kosong' ? $tersedia++ : $tidakTersedia++;
        }

        return [
            'tersedia' => $tersedia,
            'tidak_tersedia' => $tidakTersedia,
            'total' => count($jadwal),
        ];
    }
};
?>

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
    <div class="space-y-6">
        <!-- Header -->
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-medium text-teal-700">Jadwal Ruangan</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">Jadwal Harian</h1>
                    <p class="mt-2 text-slate-600">Lihat ketersediaan ruangan berdasarkan tanggal dan jam operasional.</p>
                </div>

                <a href="<?php echo e(route('dashboard')); ?>"
                    class="inline-flex items-center text-sm font-medium text-teal-700 hover:text-teal-800">
                    &larr; Kembali ke Dashboard
                </a>
            </div>
        </div>

        <!-- Filter -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700" for="ruanganId">Ruangan</label>
                    <select id="ruanganId" wire:model.live="ruanganId"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                        <option value="">Pilih ruangan</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->ruanganList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ruangan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($ruangan->id); ?>"><?php echo e($ruangan->nama); ?> (<?php echo e($ruangan->kapasitas); ?> orang)</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700" for="tanggal">Tanggal</label>
                    <input id="tanggal" type="date" wire:model.live="tanggal"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->ruangan && $this->tanggal): ?>
                <p class="mt-4 text-xs text-slate-500">
                    Menampilkan jadwal <strong><?php echo e($this->ruangan->nama); ?></strong>
                    untuk <?php echo e(\Carbon\Carbon::parse($this->tanggal)->translatedFormat('l, d F Y')); ?>

                    (<?php echo e(substr($this->ruangan->jam_buka, 0, 5)); ?> — <?php echo e(substr($this->ruangan->jam_tutup, 0, 5)); ?>)
                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- Ringkasan -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->ruangan): ?>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-emerald-700">Tersedia</p>
                            <p class="mt-1 text-2xl font-bold text-emerald-700"><?php echo e($this->ringkasan['tersedia']); ?></p>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-rose-700">Tidak Tersedia</p>
                            <p class="mt-1 text-2xl font-bold text-rose-700"><?php echo e($this->ringkasan['tidak_tersedia']); ?></p>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Jadwal Grid -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->ruangan && $this->tanggal): ?>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-3">
                    <h2 class="text-sm font-semibold text-slate-700">Slot Waktu per Jam</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="w-32 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Jam</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Pemesan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->jadwal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $palette = match ($slot['status']) {
                                        'kosong' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                                        default => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'dot' => 'bg-rose-500'],
                                    };
                                ?>
                                <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'slot-'.e($slot['jam_mulai']).''; ?>wire:key="slot-<?php echo e($slot['jam_mulai']); ?>" class="hover:bg-slate-50/50">
                                    <td class="px-6 py-4 text-sm font-medium tabular-nums text-slate-900">
                                        <?php echo e($slot['jam_label']); ?>

                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-2 rounded-full border px-2.5 py-0.5 text-xs font-medium capitalize <?php echo e($palette['bg']); ?> <?php echo e($palette['text']); ?> <?php echo e($palette['border']); ?>">
                                            <span class="h-1.5 w-1.5 rounded-full <?php echo e($palette['dot']); ?>"></span>
                                            <?php echo e($slot['status'] === 'kosong' ? 'Tersedia' : 'Tidak Tersedia'); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($slot['reservasi']): ?>
                                            <div class="font-medium"><?php echo e($slot['reservasi']->user->name ?? '-'); ?></div>
                                            <div class="text-xs text-slate-500"><?php echo e($slot['reservasi']->user->email ?? ''); ?></div>
                                        <?php else: ?>
                                            <span class="text-slate-400">—</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($slot['reservasi']): ?>
                                            <div class="text-xs text-slate-500 tabular-nums">
                                                <?php echo e(substr($slot['reservasi']->jam_mulai, 0, 5)); ?> — <?php echo e(substr($slot['reservasi']->jam_selesai, 0, 5)); ?>

                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($slot['reservasi']->keterangan): ?>
                                                <div class="mt-1 text-xs text-slate-600"><?php echo e(\Illuminate\Support\Str::limit($slot['reservasi']->keterangan, 60)); ?></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400">—</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">
                                        Tidak ada slot waktu untuk ditampilkan.
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z" />
                </svg>
                <p class="mt-3 text-sm font-medium text-slate-700">Pilih ruangan dan tanggal</p>
                <p class="mt-1 text-xs text-slate-500">Pilih ruangan serta tanggal di atas untuk melihat jadwal harian.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH D:\Informatika\Sem 4\Pemrograman Web II\ruangkita\resources\views\pages\jadwal\harian.blade.php ENDPATH**/ ?>