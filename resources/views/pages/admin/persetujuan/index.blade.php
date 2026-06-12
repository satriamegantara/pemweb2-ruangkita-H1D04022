<?php

use App\Models\Reservasi;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Persetujuan Reservasi')] class extends Component {
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'menunggu';

    public ?int $selectedId = null;

    public string $rejectReason = '';

    public ?int $rejectingId = null;

    public function mount(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function showDetail(int $id): void
    {
        $this->selectedId = $id;
    }

    public function closeDetail(): void
    {
        $this->selectedId = null;
    }

    public function approve(int $id): void
    {
        $reservasi = Reservasi::query()->find($id);

        if (!$reservasi) {
            $this->dispatch('notify', type: 'error', title: 'Gagal', message: 'Reservasi tidak ditemukan.');

            return;
        }

        if ($reservasi->status !== 'menunggu') {
            $this->dispatch('notify', type: 'error', title: 'Tidak bisa diproses', message: 'Reservasi ini sudah memiliki status lain.');

            return;
        }

        // Re-check bentrok untuk mencegah race condition
        $bentrok = Reservasi::query()
            ->where('ruangan_id', $reservasi->ruangan_id)
            ->where('tanggal', $reservasi->tanggal)
            ->where('status', 'disetujui')
            ->where('id', '!=', $id)
            ->where('jam_mulai', '<', $reservasi->jam_selesai)
            ->where('jam_selesai', '>', $reservasi->jam_mulai)
            ->exists();

        if ($bentrok) {
            $this->dispatch('notify', type: 'error', title: 'Jadwal bentrok', message: 'Reservasi ini bentrok dengan reservasi lain yang sudah disetujui.');

            return;
        }

        $reservasi->update(['status' => 'disetujui']);

        $this->dispatch('notify', type: 'success', title: 'Reservasi disetujui', message: 'Reservasi ' . $reservasi->ruangan->nama . ' untuk ' . $reservasi->user->name . ' telah disetujui.');

        // Refresh slot island jika ada user membuka form reservasi untuk ruangan/tanggal yang sama
        $this->dispatch('slot-tersedia-refresh', ruanganId: $reservasi->ruangan_id, tanggal: $reservasi->tanggal);
    }

    public function confirmReject(int $id): void
    {
        $this->rejectingId = $id;
        $this->rejectReason = '';
    }

    public function closeRejectConfirm(): void
    {
        $this->rejectingId = null;
    }

    public function submitReject(): void
    {
        if (!$this->rejectingId) {
            return;
        }

        $reservasi = Reservasi::query()->find($this->rejectingId);

        if (!$reservasi) {
            $this->dispatch('notify', type: 'error', title: 'Gagal', message: 'Reservasi tidak ditemukan.');
            $this->rejectingId = null;

            return;
        }

        if ($reservasi->status !== 'menunggu') {
            $this->dispatch('notify', type: 'error', title: 'Tidak bisa diproses', message: 'Reservasi ini sudah memiliki status lain.');
            $this->rejectingId = null;

            return;
        }

        $reservasi->update([
            'status' => 'ditolak',
            'keterangan' => $this->rejectReason ?: 'Permintaan reservasi ditolak.',
        ]);

        $this->dispatch('notify', type: 'success', title: 'Reservasi ditolak', message: 'Reservasi ' . $reservasi->ruangan->nama . ' untuk ' . $reservasi->user->name . ' telah ditolak.');

        $this->rejectingId = null;
    }

    #[Computed]
    public function reservasiList()
    {
        return Reservasi::query()
            ->with(['ruangan', 'user'])
            ->where('status', $this->statusFilter)
            ->when(
                filled($this->search),
                fn(Builder $q) => $q->whereHas(
                    'ruangan',
                    fn(Builder $rq) => $rq->where('nama', 'like', '%' . $this->search . '%'),
                ),
            )
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai')
            ->paginate(10);
    }

    #[Computed]
    public function selectedReservasi(): ?Reservasi
    {
        if (!$this->selectedId) {
            return null;
        }

        return Reservasi::query()->with(['ruangan', 'user'])->find($this->selectedId);
    }
};
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

                <a href="{{ route('admin.dashboard') }}"
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
                        @forelse ($this->reservasiList as $reservasi)
                            @php
                                $statusPalette = match ($reservasi->status) {
                                    'disetujui' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-200'],
                                    'menunggu' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
                                    'ditolak' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200'],
                                    default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200'],
                                };
                            @endphp
                            <tr wire:key="reservasi-{{ $reservasi->id }}" class="hover:bg-slate-50/50">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                    {{ $reservasi->ruangan->nama ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <div class="font-medium">{{ $reservasi->user->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $reservasi->user->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 tabular-nums">
                                    {{ $reservasi->tanggal->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 tabular-nums">
                                    {{ substr($reservasi->jam_mulai, 0, 5) }} — {{ substr($reservasi->jam_selesai, 0, 5) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium capitalize {{ $statusPalette['bg'] }} {{ $statusPalette['text'] }} {{ $statusPalette['border'] }}">
                                        {{ $reservasi->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        <button type="button" wire:click="showDetail({{ $reservasi->id }})"
                                            class="text-teal-700 transition hover:text-teal-800">
                                            Detail
                                        </button>
                                        @if ($reservasi->status === 'menunggu')
                                            <button type="button" wire:click="approve({{ $reservasi->id }})"
                                                class="text-emerald-600 transition hover:text-emerald-700">
                                                Setujui
                                            </button>
                                            <button type="button" wire:click="confirmReject({{ $reservasi->id }})"
                                                class="text-rose-600 transition hover:text-rose-700">
                                                Tolak
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-slate-400">
                                        <svg class="mb-3 h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12h6m-3-3v6m-7.5 1.5a8.95 8.95 0 0115 0m-3-5a8.95 8.95 0 0115 0M4.5 4.5h15" />
                                        </svg>
                                        <p class="text-sm font-medium text-slate-600">Tidak ada data</p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            @if ($this->statusFilter === 'menunggu')
                                                Belum ada permintaan reservasi yang menunggu persetujuan.
                                            @else
                                                Tidak ada reservasi dengan status ini.
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($this->reservasiList->hasPages())
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-3">
                    {{ $this->reservasiList->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Detail -->
    @if ($this->selectedReservasi)
        @php $detail = $this->selectedReservasi; @endphp
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
                        <dd class="font-medium text-slate-900">{{ $detail->ruangan->nama ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Tanggal</dt>
                        <dd class="font-medium text-slate-900 tabular-nums">{{ $detail->tanggal->translatedFormat('l, d F Y') }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Waktu</dt>
                        <dd class="font-medium text-slate-900 tabular-nums">{{ substr($detail->jam_mulai, 0, 5) }} — {{ substr($detail->jam_selesai, 0, 5) }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Status</dt>
                        <dd class="font-medium capitalize text-slate-900">{{ $detail->status }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Pemohon</dt>
                        <dd class="font-medium text-slate-900">{{ $detail->user->name ?? '-' }}</dd>
                    </div>
                    @if ($detail->keterangan)
                        <div>
                            <dt class="text-slate-500">Keterangan</dt>
                            <dd class="mt-1 text-slate-700">{{ $detail->keterangan }}</dd>
                        </div>
                    @endif
                </dl>

                <div class="mt-6 flex justify-end">
                    <button type="button" wire:click="closeDetail"
                        class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Reject dengan Alasan -->
    @if ($this->rejectingId)
        @php $rej = $this->selectedReservasi; @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
            wire:click.self="closeRejectConfirm">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-bold text-slate-900">Tolak Reservasi?</h3>
                @if ($rej)
                    <p class="mt-2 text-sm text-slate-600">
                        Reservasi <strong>{{ $rej->ruangan->nama ?? '-' }}</strong> pada
                        <strong>{{ $rej->tanggal->translatedFormat('d M Y') }}</strong> akan ditolak.
                    </p>
                @endif

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
    @endif
</div>
