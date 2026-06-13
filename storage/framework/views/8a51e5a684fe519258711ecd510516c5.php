<?php

use App\Models\Reservasi;
use App\Models\Ruangan;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\On;
use Livewire\Component;

new #[Isolate] class extends Component {
    public ?int $ruanganId = null;

    public string $tanggal = '';

    public ?Ruangan $ruangan = null;

    public function mount(?int $ruanganId = null, ?string $tanggal = null): void
    {
        $this->ruanganId = $ruanganId;
        $this->tanggal = $tanggal ?: now()->toDateString();

        if ($this->ruanganId) {
            $this->ruangan = Ruangan::query()->find($this->ruanganId);
        }
    }

    #[On('slot-tersedia-refresh')]
    public function refresh(?int $ruanganId = null, ?string $tanggal = null): void
    {
        $this->ruanganId = $ruanganId;
        if ($ruanganId !== null) {
            $this->ruangan = Ruangan::query()->find($ruanganId);
        }
        if ($tanggal !== null) {
            $this->tanggal = $tanggal;
        }
    }

    /**
     * Bangun peta slot per jam (key = jam mulai integer) beserta status.
     * Hanya reservasi dengan status 'disetujui' yang ditandai sebagai terisi.
     *
     * @return array<int, array{jam: string, status: string, label: string}>
     */
    #[Computed]
    public function slots(): array
    {
        $jamBuka = intval(substr($this->ruangan->jam_buka ?? '07:00:00', 0, 2));
        $jamTutup = intval(substr($this->ruangan->jam_tutup ?? '21:00:00', 0, 2));

        $occupied = [];

        if ($this->ruanganId && $this->tanggal) {
            // Hanya hitung yang sudah disetujui
            $reservasi = Reservasi::query()
                ->where('ruangan_id', $this->ruanganId)
                ->whereDate('tanggal', \Carbon\Carbon::parse($this->tanggal))
                ->where('status', 'disetujui')
                ->get(['jam_mulai', 'jam_selesai']);

            foreach ($reservasi as $r) {
                $start = (int) \Carbon\Carbon::parse($r->jam_mulai)->format('H');
                $end = (int) \Carbon\Carbon::parse($r->jam_selesai)->format('H');

                for ($h = $start; $h < $end; $h++) {
                    if (!isset($occupied[$h])) {
                        $occupied[$h] = 'disetujui';
                    }
                }
            }
        }

        $slots = [];
        for ($h = $jamBuka; $h < $jamTutup; $h++) {
            $jam = str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00';
            $status = $occupied[$h] ?? 'kosong';
            $label = $status === 'disetujui' ? 'Sudah Dipesan' : 'Tersedia';

            $slots[$h] = [
                'jam' => $jam,
                'status' => $status,
                'label' => $label,
            ];
        }

        return $slots;
    }

    #[Computed]
    public function ringkasan(): array
    {
        $slots = $this->slots;

        $kosong = 0;
        $disetujui = 0;

        foreach ($slots as $slot) {
            $slot['status'] === 'disetujui' ? $disetujui++ : $kosong++;
        }

        return [
            'tersedia' => $kosong,
            'dipesan' => $disetujui,
            'total' => count($slots),
        ];
    }
};
?>

<div
    wire:poll.60s
    class="flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
>
    <!-- Header -->
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-medium text-teal-700">Slot Tersedia Hari Ini</p>
            <h2 class="mt-1 text-lg font-semibold text-slate-900">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->tanggal): ?>
                    <?php echo e(\Carbon\Carbon::parse($this->tanggal)->translatedFormat('l, d F Y')); ?>

                <?php else: ?>
                    Pilih tanggal
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </h2>
            <p class="mt-1 text-xs text-slate-500">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->ruangan): ?>
                    Ruangan: <strong><?php echo e($this->ruangan->nama); ?></strong> · Jam operasional <?php echo e($this->ruangan->jam_buka ?? '07:00'); ?> — <?php echo e($this->ruangan->jam_tutup ?? '21:00'); ?>

                <?php else: ?>
                    Pilih ruangan untuk melihat slot ketersediaan.
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
        </div>
        <span
            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700"
            aria-hidden="true"
        >
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6 2v3M14 2v3M3 8h14M5 5h10a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
            </svg>
        </span>
    </div>

    <!-- Ringkasan -->
    <div class="mt-5 flex gap-3 text-xs">
        <div class="flex flex-1 items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2">
            <span class="h-3 w-3 shrink-0 rounded-full bg-emerald-500"></span>
            <span class="font-semibold text-emerald-700"><?php echo e($this->ringkasan['tersedia']); ?></span>
            <span class="text-emerald-700/80">Tersedia</span>
        </div>
        <div class="flex flex-1 items-center gap-2 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2">
            <span class="h-3 w-3 shrink-0 rounded-full bg-sky-500"></span>
            <span class="font-semibold text-sky-700"><?php echo e($this->ringkasan['dipesan']); ?></span>
            <span class="text-sky-700/80">Dipesan</span>
        </div>
    </div>

    <!-- Timeline Horizontal -->
    <div class="mt-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->ruangan): ?>
            <div class="relative">
                <!-- Garis timeline -->
                <div class="absolute left-0 right-0 top-5 h-0.5 bg-slate-200"></div>

                <!-- Slot nodes -->
                <div class="flex items-center justify-between gap-1 overflow-x-auto pb-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $palette = match ($slot['status']) {
                                'disetujui' => [
                                    'node' => 'bg-sky-500 ring-sky-200',
                                    'label' => 'text-sky-700',
                                    'border' => 'border-sky-200',
                                ],
                                default => [
                                    'node' => 'bg-emerald-500 ring-emerald-200',
                                    'label' => 'text-emerald-700',
                                    'border' => 'border-emerald-200',
                                ],
                            };
                        ?>
                        <div
                            class="group relative flex flex-1 flex-col items-center"
                            title="<?php echo e($slot['jam']); ?> — <?php echo e($slot['label']); ?>"
                        >
                            <!-- Node dot -->
                            <div class="z-10 flex h-6 w-6 items-center justify-center rounded-full ring-4 <?php echo e($palette['node']); ?>">
                            </div>
                            <!-- Jam label -->
                            <span class="mt-1.5 text-[10px] font-medium <?php echo e($palette['label']); ?>">
                                <?php echo e($slot['jam']); ?>

                            </span>
                            <!-- Label status (hover tooltip) -->
                            <span class="invisible absolute -bottom-12 left-1/2 z-20 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-[10px] font-medium text-white opacity-0 transition group-hover:visible group-hover:opacity-100">
                                <?php echo e($slot['label']); ?>

                            </span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>

            <!-- Keterangan bawah -->
            <div class="mt-4 flex items-center justify-center gap-6 text-[11px] text-slate-500">
                <span class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    Tersedia
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span>
                    Sudah Dipesan
                </span>
            </div>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center py-8 text-slate-400">
                <svg class="mb-2 h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6 2v3M18 2v3M3 8h18M5 5h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
                </svg>
                <p class="text-sm">Pilih ruangan untuk melihat slot tersedia</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <p class="mt-4 text-xs text-slate-400">
        Island ini diperbarui otomatis setiap 60 detik. Reservasi yang sudah disetujui admin akan ditandai sebagai sudah dipesan.
    </p>
</div>
<?php /**PATH D:\Informatika\Sem 4\Pemrograman Web II\ruangkita\resources\views\livewire\components\slot-tersedia.blade.php ENDPATH**/ ?>