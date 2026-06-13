<?php

use Livewire\Attributes\On;
use Livewire\Component;

new #[On('slot-tersedia-refresh')] class extends Component {
    public ?int $ruanganId = null;
    public string $tanggal = '';
    public ?\App\Models\Ruangan $ruangan = null;

    public function mount(?int $ruanganId = null, ?string $tanggal = null): void
    {
        $this->ruanganId = $ruanganId;
        $this->tanggal = $tanggal ?: today()->toDateString();

        if ($this->ruanganId) {
            $this->ruangan = \App\Models\Ruangan::query()->find($this->ruanganId);
        }
    }

    public function refresh(?int $ruanganId = null, ?string $tanggal = null): void
    {
        if ($ruanganId !== null) {
            $this->ruanganId = $ruanganId;
            $this->ruangan = \App\Models\Ruangan::query()->find($ruanganId);
        }
        if ($tanggal !== null) {
            $this->tanggal = $tanggal;
        }
    }

    public function getJamBuka(): string
    {
        return $this->ruangan?->jam_buka ?? '07:00';
    }

    public function getJamTutup(): string
    {
        return $this->ruangan?->jam_tutup ?? '21:00';
    }

    /** @return array<int, array<string, mixed>> */
    public function getSlotsProperty(): array
    {
        $jamBukaInt = strtotime('1970-01-01 ' . $this->getJamBuka() . ':00');
        $jamTutupInt = strtotime('1970-01-01 ' . $this->getJamTutup() . ':00');

        $booked = [];

        if ($this->ruanganId && $this->tanggal) {
            $reservations = \App\Models\Reservasi::query()
                ->where('ruangan_id', $this->ruanganId)
                ->whereDate('tanggal', $this->tanggal)
                ->get(['jam_mulai', 'jam_selesai', 'status']);

            foreach ($reservations as $res) {
                $start = strtotime('1970-01-01 ' . $res->jam_mulai);
                $end = strtotime('1970-01-01 ' . $res->jam_selesai);

                $priority = match ($res->status) {
                    'disetujui' => 3,
                    'menunggu' => 2,
                    'ditolak' => 1,
                    default => 0,
                };

                for ($h = $start; $h < $end; $h += 3600) {
                    if (!isset($booked[$h]) || $booked[$h]['priority'] < $priority) {
                        $booked[$h] = ['status' => $res->status, 'priority' => $priority];
                    }
                }
            }
        }

        $slots = [];
        for ($h = $jamBukaInt; $h < $jamTutupInt; $h += 3600) {
            $hour = (int) date('H', $h);
            $label = str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00';

            if (isset($booked[$h])) {
                $status = $booked[$h]['status'];
                $slots[] = [
                    'hour' => $hour,
                    'label' => $label,
                    'status' => $status,
                    'isAvailable' => $status === 'disetujui' ? false : true,
                ];
            } else {
                $slots[] = [
                    'hour' => $hour,
                    'label' => $label,
                    'status' => 'tersedia',
                    'isAvailable' => true,
                ];
            }
        }

        return $slots;
    }

    public function getStatsProperty(): array
    {
        $slots = $this->slots;
        $tersedia = 0;
        $disetujui = 0;
        $menunggu = 0;

        foreach ($slots as $slot) {
            if ($slot['isAvailable']) {
                $tersedia++;
            } elseif ($slot['status'] === 'disetujui') {
                $disetujui++;
            } elseif ($slot['status'] === 'menunggu') {
                $menunggu++;
            }
        }

        return [
            'tersedia' => $tersedia,
            'dipesan' => $disetujui,
            'menunggu' => $menunggu,
            'total' => count($slots),
        ];
    }

    public function getDateFormattedProperty(): string
    {
        if (!$this->tanggal) {
            return '';
        }
        return \Carbon\Carbon::parse($this->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY');
    }

    public function getIsTodayProperty(): bool
    {
        return $this->tanggal === today()->toDateString();
    }

    public function statusColors(): array
    {
        return [
            'tersedia' => ['bg-light' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700'],
            'disetujui' => ['bg-light' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-700'],
            'menunggu' => ['bg-light' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-700'],
            'ditolak' => ['bg-light' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-700'],
        ];
    }
};
?>

<div wire:poll.60s class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-teal-600 to-cyan-600 px-6 py-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-teal-100 flex items-center gap-2">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                            clip-rule="evenodd" />
                    </svg>
                    Slot Ketersediaan
                </p>
                <h2 class="mt-1 text-xl font-bold text-white">
                    @if ($this->tanggal)
                        {{ $this->dateFormatted }}
                    @endif
                    @if ($this->isToday)
                        <span
                            class="ml-2 inline-flex rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide">Hari
                            Ini</span>
                    @endif
                </h2>
                @if ($this->ruangan)
                    <p class="mt-1 text-sm text-teal-100">
                        {{ $this->ruangan->nama }} &middot; {{ $this->getJamBuka() }} — {{ $this->getJamTutup() }}
                    </p>
                @else
                    <p class="mt-1 text-sm text-teal-200">Silakan pilih ruangan terlebih dahulu</p>
                @endif
            </div>
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm">
                <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                        clip-rule="evenodd" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    @if ($this->ruangan)
        <div class="grid grid-cols-3 divide-x divide-slate-200 border-b border-slate-100">
            <div class="px-4 py-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-2xl font-bold text-slate-900">{{ $this->stats['tersedia'] }}</span>
                </div>
                <p class="mt-0.5 text-xs font-medium text-slate-500">Tersedia</p>
            </div>
            <div class="px-4 py-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                    <span class="text-2xl font-bold text-slate-900">{{ $this->stats['dipesan'] }}</span>
                </div>
                <p class="mt-0.5 text-xs font-medium text-slate-500">Disetujui</p>
            </div>
            <div class="px-4 py-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                    <span class="text-2xl font-bold text-slate-900">{{ $this->stats['menunggu'] }}</span>
                </div>
                <p class="mt-0.5 text-xs font-medium text-slate-500">Menunggu</p>
            </div>
        </div>

        {{-- Visual Timeline Bars --}}
        <div class="px-6 py-6">
            <div class="space-y-2">
                @foreach($this->slots as $slot)
                    @php
                        $colors = ($this->statusColors())[$slot['status']] ?? ($this->statusColors())['tersedia'];
                    @endphp
                    <div class="group flex items-center gap-3">
                        <span class="w-12 shrink-0 text-right text-[11px] font-semibold text-slate-500 tabular-nums">
                            {{ $slot['label'] }}
                        </span>
                        <div class="flex-1">
                            @if ($slot['isAvailable'])
                                <div
                                    class="h-7 rounded-md bg-emerald-50 border border-emerald-200 flex items-center px-2.5 transition-all duration-200 group-hover:bg-emerald-100">
                                    <span class="text-[11px] font-medium text-emerald-700">Tersedia</span>
                                </div>
                            @else
                                <div
                                    class="h-7 rounded-md {{ $colors['bg-light'] }} border {{ $colors['border'] }} flex items-center px-2.5 transition-all duration-200 group-hover:scale-[1.02]">
                                    <span class="text-[11px] font-semibold {{ $colors['text'] }}">
                                        @if ($slot['status'] === 'disetujui')
                                            Disetujui
                                        @elseif ($slot['status'] === 'menunggu')
                                            Menunggu
                                        @elseif ($slot['status'] === 'ditolak')
                                            Ditolak
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Legend --}}
            <div class="mt-6 flex flex-wrap items-center justify-center gap-4 pt-4 border-t border-slate-100">
                <span class="flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    Tersedia
                </span>
                <span class="flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                    Disetujui
                </span>
                <span class="flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                    Menunggu
                </span>
                <span class="flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                    Ditolak
                </span>
            </div>
        </div>

        {{-- Auto-refresh indicator --}}
        <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                </span>
                <span class="text-[11px] text-slate-400">Auto-refresh 60 detik. </span>
            </div>
            <span class="text-[11px] text-slate-400">
                Terakhir diperbarui: {{ now()->format('H:i') }}
            </span>
        </div>
    @else
        {{-- Empty State --}}
        <div class="px-6 py-16 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                <svg class="h-8 w-8 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                    stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">Pilih ruangan untuk melihat</p>
            <p class="mt-1 text-xs text-slate-400">Slot ketersediaan akan muncul di sini</p>
        </div>
    @endif
</div>