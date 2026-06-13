<?php

use App\Models\Reservasi;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Daftar Reservasi')] class extends Component {
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public ?int $selectedId = null;

    public ?int $cancelingId = null;

    public array $orderedIds = [];

    public function mount(): void
    {
        $this->resetPage();
        $this->orderedIds = $this->reservasiList->pluck('id')->toArray();
    }

    public function updatedOrderedIds($value): void
    {
        $this->orderedIds = $value;
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

    public function confirmCancel(int $id): void
    {
        $this->cancelingId = $id;
    }

    public function cancelReservation(): void
    {
        if (!$this->cancelingId) {
            return;
        }

        $reservasi = Reservasi::query()->find($this->cancelingId);

        if (!$reservasi) {
            $this->dispatch('notify', type: 'error', title: 'Gagal', message: 'Reservasi tidak ditemukan.');
            $this->cancelingId = null;

            return;
        }

        $user = Auth::user();

        if (!$user->isAdmin() && $reservasi->user_id !== $user->id) {
            $this->dispatch('notify', type: 'error', title: 'Tidak diizinkan', message: 'Anda hanya bisa membatalkan reservasi sendiri.');
            $this->cancelingId = null;

            return;
        }

        if ($reservasi->status !== 'menunggu') {
            $this->dispatch('notify', type: 'error', title: 'Tidak bisa dibatalkan', message: 'Hanya reservasi menunggu yang dapat dibatalkan.');
            $this->cancelingId = null;

            return;
        }

        $reservasi->delete();

        $this->dispatch('notify', type: 'success', title: 'Reservasi dibatalkan', message: 'Reservasi berhasil dihapus dari daftar.');

        $this->cancelingId = null;
        $this->resetPage();
    }

    public function closeCancelConfirm(): void
    {
        $this->cancelingId = null;
    }

    public function updateOrder($orderedIds): void
    {
        $this->orderedIds = $orderedIds;
        $this->dispatch(
            'notify',
            type: 'success',
            title: 'Urutan diperbarui',
            message: 'Urutan baris berhasil disimpan di sisi klien.',
        );
    }

    #[Computed]
    public function isAdmin(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    #[Computed]
    public function reservasiList()
    {
        $user = Auth::user();

        return Reservasi::query()
            ->with(['ruangan', 'user'])
            ->when(
                !$this->isAdmin,
                fn(Builder $q) => $q->where('user_id', $user->id),
            )
            ->when(
                filled($this->search),
                fn(Builder $q) => $q->whereHas(
                    'ruangan',
                    fn(Builder $rq) => $rq->where('nama', 'like', '%' . $this->search . '%'),
                ),
            )
            ->when(
                filled($this->statusFilter),
                fn(Builder $q) => $q->where('status', $this->statusFilter),
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

        $reservasi = Reservasi::query()->with(['ruangan', 'user'])->find($this->selectedId);

        if (!$reservasi) {
            return null;
        }

        if (!$this->isAdmin && $reservasi->user_id !== Auth::id()) {
            return null;
        }

        return $reservasi;
    }

    #[Computed]
    public function cancelingReservasi(): ?Reservasi
    {
        if (!$this->cancelingId) {
            return null;
        }

        $reservasi = Reservasi::query()->find($this->cancelingId);

        if (!$reservasi) {
            return null;
        }

        if (!$this->isAdmin && $reservasi->user_id !== Auth::id()) {
            return null;
        }

        return $reservasi;
    }
};
?>

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
    <div class="space-y-6">
        <!-- Header -->
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-medium text-teal-700">
                        {{ $this->isAdmin ? 'Manajemen Reservasi' : 'Reservasi Saya' }}
                    </p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">Daftar Reservasi</h1>
                    <p class="mt-2 text-slate-600">
                        @if ($this->isAdmin)
                            Kelola semua permintaan reservasi ruangan. Anda dapat menyetujui atau menolak dari sini.
                        @else
                            Lihat status dan riwayat reservasi ruangan yang pernah Anda ajukan.
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center text-sm font-medium text-teal-700 hover:text-teal-800">
                        &larr; Dashboard
                    </a>
                    @unless ($this->isAdmin)
                        <a href="{{ route('reservasi.create') }}"
                            class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-700">
                            + Buat Reservasi
                        </a>
                    @endunless
                </div>
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
                    <label class="mb-1.5 block text-sm font-medium text-slate-700" for="statusFilter">Status</label>
                    <select id="statusFilter" wire:model.live="statusFilter"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                        <option value="">Semua status</option>
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
                            @if ($this->isAdmin)
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Pemohon</th>
                            @endif
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Jam</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white" wire:sort="updateOrder">
                        @forelse ($this->reservasiList as $reservasi)
                            @php
                                $statusPalette = match ($reservasi->status) {
                                    'disetujui' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-200'],
                                    'menunggu' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
                                    'ditolak' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200'],
                                    default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200'],
                                };
                            @endphp
                            <tr wire:sort:item="{{ $reservasi->id }}" wire:key="reservasi-{{ $reservasi->id }}" class="hover:bg-slate-50/50 cursor-move">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                    {{ $reservasi->ruangan->nama ?? '-' }}
                                </td>
                                @if ($this->isAdmin)
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        <div class="font-medium">{{ $reservasi->user->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $reservasi->user->email ?? '' }}</div>
                                    </td>
                                @endif
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
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button" wire:click="showDetail({{ $reservasi->id }})"
                                            class="text-teal-700 transition hover:text-teal-800">
                                            Detail
                                        </button>
                                        @if ($reservasi->status === 'menunggu' && ($this->isAdmin || $reservasi->user_id === Auth::id()))
                                            <button type="button" wire:click="confirmCancel({{ $reservasi->id }})"
                                                class="text-rose-600 transition hover:text-rose-700">
                                                Batalkan
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $this->isAdmin ? 6 : 5 }}" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-slate-400">
                                        <svg class="mb-3 h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z" />
                                        </svg>
                                        <p class="text-sm font-medium text-slate-600">Belum ada reservasi</p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            @if ($this->isAdmin)
                                                Belum ada permintaan reservasi yang masuk.
                                            @else
                                                Anda belum pernah membuat reservasi.
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

    <!-- Modal Konfirmasi Batalkan -->
    @if ($this->cancelingReservasi)
        @php $cancel = $this->cancelingReservasi; @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
            wire:click.self="closeCancelConfirm">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-bold text-slate-900">Batalkan Reservasi?</h3>
                <p class="mt-2 text-sm text-slate-600">
                    Reservasi <strong>{{ $cancel->ruangan->nama ?? '-' }}</strong> pada
                    <strong>{{ $cancel->tanggal->translatedFormat('d M Y') }}</strong>
                    ({{ substr($cancel->jam_mulai, 0, 5) }} — {{ substr($cancel->jam_selesai, 0, 5) }})
                    akan dihapus. Tindakan ini tidak bisa dibatalkan.
                </p>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="closeCancelConfirm"
                        class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                        Kembali
                    </button>
                    <button type="button" wire:click="cancelReservation"
                        class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                        Ya, Batalkan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
