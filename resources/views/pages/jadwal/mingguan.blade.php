<?php

use App\Models\Reservasi;
use App\Models\Ruangan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kalender Mingguan')] class extends Component {
    public ?int $ruanganId = null;

    public string $startDate = '';

    public function mount(): void
    {
        $this->startDate = request()->query('start', Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString());
    }

    public function goToPreviousWeek(): void
    {
        $this->startDate = Carbon::parse($this->startDate)->subWeek()->toDateString();
    }

    public function goToNextWeek(): void
    {
        $this->startDate = Carbon::parse($this->startDate)->addWeek()->toDateString();
    }

    public function goToCurrentWeek(): void
    {
        $this->startDate = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
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

    /**
     * Bangun array 7 hari (Senin–Minggu) untuk minggu yang dipilih.
     *
     * @return array<int, array{tanggal: string, label: string, iso: string, day: int, is_today: bool}>
     */
    #[Computed]
    public function days(): array
    {
        $start = Carbon::parse($this->startDate)->startOfWeek(Carbon::MONDAY);
        $today = Carbon::now()->toDateString();

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $days[] = [
                'tanggal' => $date->toDateString(),
                'label' => $date->translatedFormat('D'),
                'iso' => $date->translatedFormat('d M'),
                'day' => $i,
                'is_today' => $date->toDateString() === $today,
            ];
        }

        return $days;
    }

    /**
     * Ambil semua reservasi untuk ruangan (jika dipilih) atau semua ruangan dalam minggu ini.
     * Mengembalikan peta: [tanggal][ruangan_id] = list reservasi.
     *
     * @return array<string, array<int, \Illuminate\Support\Collection<int, Reservasi>>>
     */
    #[Computed]
    public function reservasiByDayAndRuangan(): array
    {
        $start = Carbon::parse($this->startDate)->startOfWeek(Carbon::MONDAY);
        $end = $start->copy()->addDays(7);

        $query = Reservasi::query()
            ->with(['user', 'ruangan'])
            ->whereIn('status', ['menunggu', 'disetujui'])
            ->where('tanggal', '>=', $start->toDateString())
            ->where('tanggal', '<', $end->toDateString())
            ->orderBy('jam_mulai');

        if ($this->ruanganId) {
            $query->where('ruangan_id', $this->ruanganId);
        }

        $reservasiList = $query->get();

        $map = [];
        foreach ($reservasiList as $r) {
            $tanggal = Carbon::parse($r->tanggal)->toDateString();
            $ruanganId = $r->ruangan_id;
            if (!isset($map[$tanggal])) {
                $map[$tanggal] = [];
            }
            if (!isset($map[$tanggal][$ruanganId])) {
                $map[$tanggal][$ruanganId] = collect();
            }
            $map[$tanggal][$ruanganId]->push($r);
        }

        return $map;
    }

    /**
     * List ruangan yang akan ditampilkan sebagai baris kalender.
     *
     * @return \Illuminate\Support\Collection<int, Ruangan>
     */
    #[Computed]
    public function ruanganRows()
    {
        if ($this->ruanganId) {
            $rows = collect([$this->ruangan]);
        } else {
            $rows = $this->ruanganList;
        }

        return $rows;
    }

    /**
     * Hitung minggu label: "13 — 19 Juni 2026".
     */
    #[Computed]
    public function weekRangeLabel(): string
    {
        $start = Carbon::parse($this->startDate)->startOfWeek(Carbon::MONDAY);
        $end = $start->copy()->addDays(6);

        if ($start->month === $end->month) {
            return $start->format('d') . ' — ' . $end->translatedFormat('d F Y');
        }

        return $start->translatedFormat('d M') . ' — ' . $end->translatedFormat('d F Y');
    }

    public function gotoDate(string $date): void
    {
        $this->startDate = Carbon::parse($date)->startOfWeek(Carbon::MONDAY)->toDateString();
    }
};
?>

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
    <div class="space-y-6">
        <!-- Header -->
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-medium text-teal-700">Kalender Reservasi</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">Kalender Mingguan</h1>
                    <p class="mt-2 text-slate-600">
                        Tampilkan blok reservasi selama 7 hari untuk satu ruangan atau semua ruangan.
                    </p>
                </div>

                <a href="{{ route('jadwal.harian') }}"
                    class="inline-flex items-center text-sm font-medium text-teal-700 hover:text-teal-800">
                    &larr; Lihat Jadwal Harian
                </a>
            </div>
        </div>

        <!-- Filter & Navigasi Minggu -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700" for="ruanganId">Ruangan</label>
                    <select id="ruanganId" wire:model.live="ruanganId"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                        <option value="">Semua ruangan</option>
                        @foreach ($this->ruanganList as $ruangan)
                            <option value="{{ $ruangan->id }}">{{ $ruangan->nama }} ({{ $ruangan->kapasitas }} orang)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700" for="startDate">Mulai minggu</label>
                    <input id="startDate" type="date" wire:model.live="startDate"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                </div>
                <div class="flex items-end gap-2">
                    <button type="button" wire:click="goToPreviousWeek"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                        Minggu Lalu
                    </button>
                    <button type="button" wire:click="goToCurrentWeek"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-3 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-700">
                        Hari Ini
                    </button>
                    <button type="button" wire:click="goToNextWeek"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Minggu Depan
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            <p class="mt-4 text-xs text-slate-500">
                Menampilkan minggu <strong>{{ $this->weekRangeLabel }}</strong>
                @if ($this->ruangan)
                    untuk ruangan <strong>{{ $this->ruangan->nama }}</strong>
                @else
                    untuk <strong>semua ruangan</strong>
                @endif
            </p>
        </div>

        <!-- Legenda -->
        <div class="flex flex-wrap items-center gap-4 rounded-2xl border border-slate-200 bg-white px-6 py-4 shadow-sm">
            <span class="text-sm font-medium text-slate-700">Legenda:</span>
            <span class="inline-flex items-center gap-2 text-xs">
                <span class="inline-block h-3 w-3 rounded bg-emerald-500"></span>
                Disetujui
            </span>
            <span class="inline-flex items-center gap-2 text-xs">
                <span class="inline-block h-3 w-3 rounded bg-amber-500"></span>
                Menunggu
            </span>
            <span class="inline-flex items-center gap-2 text-xs">
                <span class="inline-block h-3 w-3 rounded bg-rose-500"></span>
                Ditolak
            </span>
        </div>

        <!-- Calendar Grid -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full table-fixed border-collapse">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col"
                                class="w-40 border-b border-r border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Ruangan
                            </th>
                            @foreach ($this->days as $day)
                                <th scope="col"
                                    class="border-b border-slate-200 px-2 py-3 text-center text-xs font-semibold uppercase tracking-wider {{ $day['is_today'] ? 'bg-teal-50 text-teal-700' : 'text-slate-600' }}">
                                    <div>{{ $day['label'] }}</div>
                                    <div class="mt-0.5 text-[10px] font-normal {{ $day['is_today'] ? 'text-teal-600' : 'text-slate-500' }}">
                                        {{ $day['iso'] }}
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($this->ruanganRows as $ruangan)
                            @php
                                $ruanganId = $ruangan->id;
                            @endphp
                            <tr wire:key="row-{{ $ruanganId }}">
                                <td class="border-r border-slate-200 bg-slate-50/50 px-4 py-3 align-top">
                                    <div class="text-sm font-semibold text-slate-900">{{ $ruangan->nama }}</div>
                                    <div class="mt-0.5 text-xs text-slate-500">
                                        Kapasitas {{ $ruangan->kapasitas }} · {{ substr($ruangan->jam_buka, 0, 5) }}–{{ substr($ruangan->jam_tutup, 0, 5) }}
                                    </div>
                                </td>
                                @foreach ($this->days as $day)
                                    @php
                                        $list = $this->reservasiByDayAndRuangan[$day['tanggal']][$ruanganId] ?? collect();
                                    @endphp
                                    <td wire:key="cell-{{ $ruanganId }}-{{ $day['tanggal'] }}"
                                        class="h-32 min-w-[120px] border-r border-slate-100 p-1.5 align-top {{ $day['is_today'] ? 'bg-teal-50/30' : '' }}">
                                        @forelse ($list as $r)
                                            @php
                                                $palette = match ($r->status) {
                                                    'disetujui' => ['bg' => 'bg-emerald-100', 'border' => 'border-emerald-300', 'text' => 'text-emerald-800', 'dot' => 'bg-emerald-500'],
                                                    'menunggu' => ['bg' => 'bg-amber-100', 'border' => 'border-amber-300', 'text' => 'text-amber-800', 'dot' => 'bg-amber-500'],
                                                    'ditolak' => ['bg' => 'bg-rose-100', 'border' => 'border-rose-300', 'text' => 'text-rose-800', 'dot' => 'bg-rose-500'],
                                                    default => ['bg' => 'bg-slate-100', 'border' => 'border-slate-300', 'text' => 'text-slate-800', 'dot' => 'bg-slate-500'],
                                                };
                                            @endphp
                                            <div class="mb-1 rounded-md border-l-4 p-1.5 text-[10px] leading-tight {{ $palette['bg'] }} {{ $palette['border'] }} {{ $palette['text'] }}"
                                                title="{{ $r->keterangan ?? '-' }}">
                                                <div class="flex items-center gap-1 font-semibold">
                                                    <span class="inline-block h-1.5 w-1.5 rounded-full {{ $palette['dot'] }}"></span>
                                                    {{ substr($r->jam_mulai, 0, 5) }}–{{ substr($r->jam_selesai, 0, 5) }}
                                                </div>
                                                <div class="mt-0.5 truncate font-medium">
                                                    {{ $r->user->name ?? '-' }}
                                                </div>
                                                @if ($r->keterangan)
                                                    <div class="mt-0.5 truncate opacity-75">
                                                        {{ \Illuminate\Support\Str::limit($r->keterangan, 30) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="flex h-full items-center justify-center text-[10px] text-slate-300">
                                                —
                                            </div>
                                        @endforelse
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">
                                    Tidak ada ruangan yang dapat ditampilkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ringkasan -->
        @php
            $totalReservasi = collect($this->reservasiByDayAndRuangan)
                ->flatten(1)
                ->flatten(1)
                ->count();
            $disetujui = collect($this->reservasiByDayAndRuangan)
                ->flatten(1)
                ->flatten(1)
                ->filter(fn ($r) => $r->status === 'disetujui')
                ->count();
            $menunggu = collect($this->reservasiByDayAndRuangan)
                ->flatten(1)
                ->flatten(1)
                ->filter(fn ($r) => $r->status === 'menunggu')
                ->count();
            $ditolak = collect($this->reservasiByDayAndRuangan)
                ->flatten(1)
                ->flatten(1)
                ->filter(fn ($r) => $r->status === 'ditolak')
                ->count();
        @endphp
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500">Total Reservasi</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totalReservasi }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs font-medium text-emerald-700">Disetujui</p>
                <p class="mt-1 text-2xl font-bold text-emerald-700">{{ $disetujui }}</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-xs font-medium text-amber-700">Menunggu</p>
                <p class="mt-1 text-2xl font-bold text-amber-700">{{ $menunggu }}</p>
            </div>
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4">
                <p class="text-xs font-medium text-rose-700">Ditolak</p>
                <p class="mt-1 text-2xl font-bold text-rose-700">{{ $ditolak }}</p>
            </div>
        </div>
    </div>
</div>
