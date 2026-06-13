<?php

use App\Models\Ruangan;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

new #[Title('Kelola Ruangan')] class extends Component {
    use WithPagination;

    public string $search = '';

    public int $minKapasitas = 0;

    public function mount(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMinKapasitas(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $ruangan = Ruangan::query()->find($id);

        if (!$ruangan) {
            $this->dispatch('notify', type: 'error', title: 'Gagal', message: 'Ruangan tidak ditemukan.');

            return;
        }

        if ($ruangan->reservasi()->exists()) {
            $this->dispatch('notify', type: 'error', title: 'Tidak bisa dihapus', message: 'Ruangan masih memiliki data reservasi.');

            return;
        }

        if ($ruangan->foto) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($ruangan->foto);
        }

        $ruangan->delete();

        $this->dispatch('notify', type: 'success', title: 'Dihapus', message: 'Ruangan berhasil dihapus.');
    }

    #[Computed]
    public function ruanganList()
    {
        return Ruangan::query()
            ->with('fasilitas')
            ->when(
                filled($this->search),
                fn(Builder $q) => $q->where('nama', 'like', '%' . $this->search . '%'),
            )
            ->when(
                $this->minKapasitas > 0,
                fn(Builder $q) => $q->where('kapasitas', '>=', $this->minKapasitas),
            )
            ->orderBy('nama')
            ->paginate(10);
    }
};
?>

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
    <div class="space-y-6">
        <!-- Header -->
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-medium text-teal-700">Kelola Ruangan</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">Daftar Ruangan</h1>
                    <p class="mt-2 text-slate-600">Kelola data ruang, kapasitas, jam operasional, dan foto ruangan.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.dashboard') }}"
                        class="inline-flex items-center text-sm font-medium text-teal-700 hover:text-teal-800">
                        &larr; Dashboard
                    </a>
                    <a href="{{ route('admin.ruangan.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Tambah Ruangan
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700" for="search">Cari Ruangan</label>
                    <input id="search" type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Nama ruangan..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700" for="minKapasitas">Kapasitas Minimum</label>
                    <input id="minKapasitas" type="number" min="0" wire:model.live.debounce.300ms="minKapasitas"
                        placeholder="0"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="w-24 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Foto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Nama Ruangan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Kapasitas</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Jam Operasional</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Fasilitas</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($this->ruanganList as $ruangan)
                            <tr wire:key="ruangan-{{ $ruangan->id }}" class="hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    @if ($ruangan->foto)
                                        <img src="{{ asset('storage/' . $ruangan->foto) }}" alt="{{ $ruangan->nama }}"
                                            class="h-12 w-16 rounded-lg object-cover ring-1 ring-slate-200">
                                    @else
                                        <div class="flex h-12 w-16 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-400 ring-1 ring-slate-200">
                                            No img
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                    {{ $ruangan->nama }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    {{ $ruangan->kapasitas }} orang
                                </td>
                                <td class="px-6 py-4 text-sm tabular-nums text-slate-700">
                                    {{ substr($ruangan->jam_buka, 0, 5) }} — {{ substr($ruangan->jam_tutup, 0, 5) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($ruangan->fasilitas->take(3) as $f)
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-700">
                                                {{ $f->nama }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400">—</span>
                                        @endforelse
                                        @if ($ruangan->fasilitas->count() > 3)
                                            <span class="text-xs text-slate-500">+{{ $ruangan->fasilitas->count() - 3 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="inline-flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.ruangan.edit', $ruangan->id) }}"
                                            class="text-teal-700 transition hover:text-teal-800">
                                            Edit
                                        </a>
                                        <button type="button"
                                            wire:click="delete({{ $ruangan->id }})"
                                            wire:confirm="Yakin ingin menghapus ruangan {{ $ruangan->nama }}?"
                                            class="text-rose-600 transition hover:text-rose-700">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-slate-400">
                                        <svg class="mb-3 h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                        </svg>
                                        <p class="text-sm font-medium text-slate-600">Belum ada ruangan</p>
                                        <p class="mt-1 text-xs text-slate-500">Klik "Tambah Ruangan" untuk membuat ruangan baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($this->ruanganList->hasPages())
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-3">
                    {{ $this->ruanganList->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
