<?php

use App\Models\Ruangan;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Kelola Ruangan')] class extends Component {
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $min_capacity = '';

    public ?int $editingId = null;

    public string $nama = '';

    public string $kapasitas = '';

    public string $jam_buka = '07:00';

    public string $jam_tutup = '21:00';

    public TemporaryUploadedFile|string|null $foto = null;

    public string $existingFoto = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMinCapacity(): void
    {
        $this->resetPage();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'min_capacity'], true)) {
            return;
        }

        $this->validateOnly($property);
    }

    protected function rules(): array
    {
        return [
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ruangan', 'nama')->ignore($this->editingId),
            ],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:1000'],
            'jam_buka' => ['required', 'date_format:H:i'],
            'jam_tutup' => ['required', 'date_format:H:i'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nama.required' => 'Nama ruangan wajib diisi.',
            'nama.unique' => 'Nama ruangan sudah digunakan.',
            'kapasitas.required' => 'Kapasitas wajib diisi.',
            'kapasitas.integer' => 'Kapasitas harus berupa angka.',
            'kapasitas.min' => 'Kapasitas minimal 1.',
            'jam_buka.required' => 'Jam buka wajib diisi.',
            'jam_buka.date_format' => 'Format jam buka tidak valid.',
            'jam_tutup.required' => 'Jam tutup wajib diisi.',
            'jam_tutup.date_format' => 'Format jam tutup tidak valid.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->jam_tutup <= $this->jam_buka) {
            $this->addError('jam_tutup', 'Jam tutup harus lebih besar dari jam buka.');

            return;
        }

        $ruangan = $this->editingId
            ? Ruangan::query()->findOrFail($this->editingId)
            : new Ruangan();

        $fotoPath = $ruangan->foto;

        if ($this->foto instanceof TemporaryUploadedFile) {
            if ($ruangan->exists && filled($ruangan->foto)) {
                Storage::disk('public')->delete($ruangan->foto);
            }

            $fotoPath = $this->foto->store('ruangan', 'public');
        }

        Ruangan::updateOrCreate(
            ['id' => $this->editingId],
            [
                'nama' => $validated['nama'],
                'kapasitas' => $validated['kapasitas'],
                'jam_buka' => $this->jam_buka . ':00',
                'jam_tutup' => $this->jam_tutup . ':00',
                'foto' => $fotoPath ?? '',
            ],
        );

        $this->dispatch(
            'notify',
            type: 'success',
            title: $this->editingId ? 'Ruangan diperbarui' : 'Ruangan ditambahkan',
            message: "Data ruangan {$validated['nama']} berhasil disimpan.",
        );

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $ruangan = Ruangan::query()->findOrFail($id);

        $this->editingId = $ruangan->id;
        $this->nama = $ruangan->nama;
        $this->kapasitas = (string) $ruangan->kapasitas;
        $this->jam_buka = substr($ruangan->jam_buka, 0, 5);
        $this->jam_tutup = substr($ruangan->jam_tutup, 0, 5);
        $this->existingFoto = (string) $ruangan->foto;
        $this->foto = null;

        $this->resetErrorBag();
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $ruangan = Ruangan::query()->findOrFail($id);
        $nama = $ruangan->nama;

        if (filled($ruangan->foto)) {
            Storage::disk('public')->delete($ruangan->foto);
        }

        $ruangan->delete();

        $this->dispatch(
            'notify',
            type: 'success',
            title: 'Ruangan dihapus',
            message: "Ruangan {$nama} berhasil dihapus.",
        );

        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'nama', 'kapasitas', 'foto', 'existingFoto']);
        $this->jam_buka = '07:00';
        $this->jam_tutup = '21:00';
        $this->resetErrorBag();
    }

    #[Computed]
    public function fotoPreviewUrl(): ?string
    {
        if ($this->foto instanceof TemporaryUploadedFile) {
            return $this->foto->temporaryUrl();
        }

        if (filled($this->existingFoto)) {
            return asset('storage/' . ltrim($this->existingFoto, '/'));
        }

        return null;
    }

    #[Computed]
    public function ruanganList()
    {
        return Ruangan::query()
            ->withCount('fasilitas')
            ->when(filled($this->search), function ($query) {
                $query->where('nama', 'like', '%' . $this->search . '%');
            })
            ->when(filled($this->min_capacity), function ($query) {
                $query->where('kapasitas', '>=', (int) $this->min_capacity);
            })
            ->orderBy('nama')
            ->paginate(10);
    }
};
?>

<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
    <div class="space-y-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-medium text-teal-700">Admin Area</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">Kelola Ruangan</h1>
                    <p class="mt-2 text-slate-600">Tambah, ubah, cari, dan hapus ruangan kampus dari satu halaman.</p>
                </div>

                <a href="{{ route('admin.dashboard') }}"
                    class="inline-flex text-sm font-medium text-teal-700 hover:text-teal-800">
                    &larr; Kembali ke Dashboard Admin
                </a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[380px_minmax(0,1fr)]">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">
                            {{ $editingId ? 'Edit Ruangan' : 'Tambah Ruangan' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-600">Isi data dasar ruangan untuk operasional reservasi.</p>
                    </div>

                    @if ($editingId)
                        <button type="button" wire:click="cancelEdit"
                            class="text-sm font-medium text-slate-500 hover:text-slate-700">
                            Batal
                        </button>
                    @endif
                </div>

                <form wire:submit="save" class="mt-6 space-y-4">
                    <div>
                        <label for="nama" class="mb-1.5 block text-sm font-medium text-slate-700">Nama Ruangan</label>
                        <input id="nama" type="text" wire:model.live="nama"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 @error('nama') border-red-500 @enderror"
                            placeholder="Contoh: Lab Multimedia">
                        @error('nama')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="kapasitas" class="mb-1.5 block text-sm font-medium text-slate-700">Kapasitas</label>
                        <input id="kapasitas" type="number" min="1" wire:model.live="kapasitas"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 @error('kapasitas') border-red-500 @enderror"
                            placeholder="20">
                        @error('kapasitas')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="jam_buka" class="mb-1.5 block text-sm font-medium text-slate-700">Jam
                                Buka</label>
                            <input id="jam_buka" type="time" wire:model.live="jam_buka"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 @error('jam_buka') border-red-500 @enderror">
                            @error('jam_buka')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jam_tutup" class="mb-1.5 block text-sm font-medium text-slate-700">Jam
                                Tutup</label>
                            <input id="jam_tutup" type="time" wire:model.live="jam_tutup"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 @error('jam_tutup') border-red-500 @enderror">
                            @error('jam_tutup')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="foto" class="mb-1.5 block text-sm font-medium text-slate-700">Foto Ruangan</label>
                        <input
                            id="foto"
                            type="file"
                            wire:model="foto"
                            accept="image/*"
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm outline-none transition file:mr-4 file:rounded-md file:border-0 file:bg-teal-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-teal-700 hover:file:bg-teal-100 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 @error('foto') border-red-500 @enderror"
                        >
                        <p class="mt-1.5 text-xs text-slate-500">Maksimal 2 MB. Format disarankan JPG, PNG, atau WEBP.</p>
                        @error('foto')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        @if ($this->fotoPreviewUrl)
                            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                <img
                                    src="{{ $this->fotoPreviewUrl }}"
                                    alt="Pratinjau foto ruangan"
                                    class="h-48 w-full object-cover"
                                >
                            </div>
                        @endif
                    </div>

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700">
                        {{ $editingId ? 'Simpan Perubahan' : 'Tambah Ruangan' }}
                    </button>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Daftar Ruangan</h2>
                        <p class="mt-1 text-sm text-slate-600">Gunakan pencarian dan filter kapasitas untuk mempersempit
                            data.</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:min-w-[360px]">
                        <input type="search" wire:model.live.debounce.300ms="search"
                            class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                            placeholder="Cari nama ruangan">
                        <input type="number" min="0" wire:model.live.debounce.300ms="min_capacity"
                            class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                            placeholder="Min. kapasitas">
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Ruangan</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Kapasitas</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Operasional</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Fasilitas</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($this->ruanganList as $ruangan)
                                <tr class="align-top">
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-slate-900">{{ $ruangan->nama }}</div>
                                        <div class="mt-1 text-sm text-slate-500">ID #{{ $ruangan->id }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-700">{{ $ruangan->kapasitas }} orang</td>
                                    <td class="px-4 py-4 text-sm text-slate-700">
                                        {{ substr($ruangan->jam_buka, 0, 5) }} - {{ substr($ruangan->jam_tutup, 0, 5) }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-700">{{ $ruangan->fasilitas_count }} item</td>
                                    <td class="px-4 py-4 text-right text-sm">
                                        <div class="inline-flex flex-wrap justify-end gap-2">
                                            <button type="button" wire:click="edit({{ $ruangan->id }})"
                                                class="rounded-lg border border-slate-300 px-3 py-2 font-medium text-slate-700 transition hover:bg-slate-50">
                                                Ubah
                                            </button>
                                            <button type="button" wire:click="delete({{ $ruangan->id }})"
                                                wire:confirm="Hapus ruangan ini?"
                                                class="rounded-lg border border-rose-200 px-3 py-2 font-medium text-rose-700 transition hover:bg-rose-50">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">
                                        Tidak ada ruangan yang cocok dengan filter saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $this->ruanganList->links() }}
                </div>
            </section>
        </div>
    </div>
</div>