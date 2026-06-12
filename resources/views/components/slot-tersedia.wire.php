<?php

use App\Models\Reservasi;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\On;
use Livewire\Component;

new #[Isolate] class extends Component {
    public ?int $ruanganId = null;

    public string $tanggal = '';

    public function mount(?int $ruanganId = null, ?string $tanggal = null): void
    {
        $this->ruanganId = $ruanganId;
        $this->tanggal = $tanggal ?: now()->toDateString();
    }

    #[On('slot-tersedia-refresh')]
    public function refresh(?int $ruanganId = null, ?string $tanggal = null): void
    {
        $this->ruanganId = $ruanganId;
        if ($tanggal !== null) {
            $this->tanggal = $tanggal;
        }
    }

    #[Computed]
    public function slots(): array
    {
        $jamBuka = 7;
        $jamTutup = 21;

        $occupied = [];

        if ($this->ruanganId && $this->tanggal) {
            $reservasi = Reservasi::query()
                ->where('ruangan_id', $this->ruanganId)
                ->where('tanggal', $this->tanggal)
                ->where('status', 'disetujui')
                ->get(['jam_mulai', 'jam_selesai']);

            foreach ($reservasi as $r) {
                $start = (int) Carbon::createFromFormat('H:i:s', $r->jam_mulai)->format('H');
                $end = (int) Carbon::createFromFormat('H:i:s', $r->jam_selesai)->format('H');

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

            $slots[$h] = [
                'jam' => $jam,
                'status' => $status,
                'label' => $status === 'disetujui' ? 'Sudah Dipesan' : 'Tersedia',
            ];
        }

        return $slots;
    }

    #[Computed]
    public function ringkasan(): array
    {
        $slots = $this->slots;

        $kosong = 0;
        $dipesan = 0;

        foreach ($slots as $slot) {
            $slot['status'] === 'disetujui' ? $dipesan++ : $kosong++;
        }

        return [
            'tersedia' => $kosong,
            'dipesan' => $dipesan,
            'total' => count($slots),
        ];
    }
};
?>

<div
    wire:poll.60s
    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
>
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-medium text-teal-700">Slot Tersedia</p>
            <h2 class="mt-1 text-lg font-semibold text-slate-900">
                {{ $this->tanggal ? \Carbon\Carbon::parse($this->tanggal)->translatedFormat('l, d F Y') : 'Pilih tanggal' }}
            </h2>
            <p class="mt-1 text-xs text-slate-500">
                @if ($this->ruanganId)
                    Slot untuk ruangan yang dipilih, hanya yang sudah disetujui.
                @else
                    Pilih ruangan dan tanggal untuk melihat slot kosong.
                @endif
            </p>
        </div>
        <span
            class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-teal-50 text-teal-700"
            aria-hidden="true"
        >
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6 2v3M14 2v3M3 8h14M5 5h10a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
            </svg>
        </span>
    </div>

    <div class="mt-5 grid grid-cols-2 gap-2 text-center text-xs">
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-2">
            <p class="font-semibold text-emerald-700">{{ $this->ringkasan['tersedia'] }}</p>
            <p class="text-emerald-700/80">Tersedia</p>
        </div>
        <div class="rounded-lg border border-sky-200 bg-sky-50 px-2 py-2">
            <p class="font-semibold text-sky-700">{{ $this->ringkasan['dipesan'] }}</p>
            <p class="text-sky-700/80">Dipesan</p>
        </div>
    </div>

    {{-- Timeline horizontal --}}
    <div class="mt-5 flex flex-wrap items-stretch gap-1">
        @foreach ($this->slots as $slot)
            @php
                $palette = match ($slot['status']) {
                    'disetujui' => 'border-sky-200 bg-sky-50 text-sky-800',
                    default     => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                };
            @endphp
            <div
                title="{{ $slot['jam'] }} — {{ $slot['label'] }}"
                class="flex min-w-[72px] grow items-center justify-center gap-1 rounded-md border px-2 py-1.5 text-xs font-medium {{ $palette }}"
            >
                <span class="tabular-nums">{{ $slot['jam'] }}</span>
            </div>
        @endforeach
    </div>

    {{-- Legend --}}
    <div class="mt-4 flex items-center gap-4 text-xs text-slate-500">
        <span class="flex items-center gap-1.5">
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            Tersedia
        </span>
        <span class="flex items-center gap-1.5">
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-sky-500"></span>
            Sudah Dipesan
        </span>
    </div>

    <p class="mt-4 text-xs text-slate-500">
        Island ini diperbarui otomatis saat ruangan atau tanggal berubah di form.
    </p>
</div>
