<?php

use App\Models\Fasilitas;
use App\Models\Ruangan;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Livewire\Component;

new #[Title('Kelola Ruangan')] class extends Component {
    use WithFileUploads;

    public ?int $ruanganId = null;

    public bool $isEdit = false;

    public string $nama = '';

    public int $kapasitas = 0;

    public string $jam_buka = '07:00';

    public string $jam_tutup = '21:00';

    public ?string $existingFoto = null;

    public $foto;

    public array $fasilitas = [];

    public string $newFasilitas = '';

    public function mount(?int $id = null): void
    {
        $this->ruanganId = $id;

        if ($id) {
            $this->isEdit = true;
            $ruangan = Ruangan::query()->with('fasilitas')->find($id);

            if (!$ruangan) {
                abort(404);
            }

            $this->nama = $ruangan->nama;
            $this->kapasitas = $ruangan->kapasitas;
            $this->jam_buka = $ruangan->jam_buka;
            $this->jam_tutup = $ruangan->jam_tutup;
            $this->existingFoto = $ruangan->foto;
            $this->fasilitas = $ruangan->fasilitas->pluck('nama')->toArray();
        }
    }

    public function addFasilitas(): void
    {
        $this->validate([
            'newFasilitas' => 'required|string|max:100',
        ]);

        $name = trim(strtolower($this->newFasilitas));

        if (in_array($name, array_map('strtolower', $this->fasilitas))) {
            $this->dispatch('notify', type: 'warning', title: 'Fasilitas duplikat', message: 'Fasilitas "' . $name . '" sudah ada dalam daftar.');

            return;
        }

        $this->fasilitas[] = $name;
        $this->newFasilitas = '';
    }

    public function removeFasilitas(int $index): void
    {
        unset($this->fasilitas[$index]);
        $this->fasilitas = array_values($this->fasilitas);
    }

    public function save()
    {
        $rules = [
            'nama' => 'required|string|max:150',
            'kapasitas' => 'required|integer|min:1|max:5000',
            'jam_buka' => 'required|date_format:H:i',
            'jam_tutup' => 'required|date_format:H:i',
        ];

        if (!$this->isEdit) {
            $rules['foto'] = 'nullable|image|max:2048';
        }

        $validated = $this->validate($rules);

        if ($this->jam_buka >= $this->jam_tutup) {
            $this->dispatch('notify', type: 'error', title: 'Jam tidak valid', message: 'Jam buka harus lebih awal dari jam tutup.');

            return;
        }

        $fotoPath = $this->existingFoto;

        if ($this->foto) {
            if ($this->existingFoto) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($this->existingFoto);
            }

            $fotoPath = $this->foto->store('ruangan', 'public');
        }

        if ($this->isEdit) {
            $ruangan = Ruangan::query()->findOrFail($this->ruanganId);

            $ruangan->update([
                'nama' => $validated['nama'],
                'kapasitas' => $validated['kapasitas'],
                'jam_buka' => $validated['jam_buka'] . ':00',
                'jam_tutup' => $validated['jam_tutup'] . ':00',
                'foto' => $fotoPath,
            ]);

            // Update fasilitas
            Fasilitas::where('ruangan_id', $ruangan->id)->delete();

            foreach ($this->fasilitas as $fName) {
                Fasilitas::create([
                    'ruangan_id' => $ruangan->id,
                    'nama' => $fName,
                ]);
            }

            $this->dispatch('notify', type: 'success', title: 'Berhasil', message: 'Ruangan "' . $ruangan->nama . '" berhasil diperbarui.');
        } else {
            $ruangan = Ruangan::create([
                'nama' => $validated['nama'],
                'kapasitas' => $validated['kapasitas'],
                'jam_buka' => $validated['jam_buka'] . ':00',
                'jam_tutup' => $validated['jam_tutup'] . ':00',
                'foto' => $fotoPath,
            ]);

            foreach ($this->fasilitas as $fName) {
                Fasilitas::create([
                    'ruangan_id' => $ruangan->id,
                    'nama' => $fName,
                ]);
            }

            $this->dispatch('notify', type: 'success', title: 'Berhasil', message: 'Ruangan "' . $ruangan->nama . '" berhasil ditambahkan.');
        }

        return redirect()->route('admin.ruangan.index');
    }

    public function render()
    {
        return view('pages.admin.ruangan.form');
    }
};
?>

<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <!-- Header -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <a href="{{ route('admin.ruangan.index') }}"
                        class="mb-2 inline-flex items-center text-sm font-medium text-teal-700 hover:text-teal-800">
                        &larr; Daftar Ruangan
                    </a>
                    <h1 class="text-2xl font-bold text-slate-900">
                        {{ $isEdit ? 'Edit Ruangan' : 'Tambah Ruangan' }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ $isEdit ? 'Perbarui informasi ruangan.' : 'Isi data ruangan baru.' }}
                    </p>
                </div>
            </div>

            <!-- Form -->
            <form wire:submit="save" class="mt-8 space-y-6">
                <!-- Info Ruangan -->
                <div class="rounded-xl border border-slate-200 p-6">
                    <h2 class="text-base font-semibold text-slate-900">Informasi Ruangan</h2>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-slate-700" for="nama">Nama
                                Ruangan</label>
                            <input id="nama" type="text" wire:model="nama" required
                                placeholder="Contoh: Ruang Kulum Umum A"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                            @error('nama') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700"
                                for="kapasitas">Kapasitas</label>
                            <input id="kapasitas" type="number" wire:model="kapasitas" required min="1" max="5000"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                            @error('kapasitas') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700" for="foto">Foto
                                Ruangan</label>
                            @if ($existingFoto)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $existingFoto) }}" alt="Foto saat ini"
                                        class="h-24 w-32 rounded-lg object-cover ring-1 ring-slate-200">
                                    <p class="mt-1 text-xs text-slate-500">Foto saat ini</p>
                                </div>
                            @endif
                            <input id="foto" type="file" wire:model="foto" accept="image/*"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition file:mr-3 file:rounded-lg file:border-0 file:bg-teal-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-teal-700">
                            <p class="mt-1 text-xs text-slate-500">Max 2MB (JPG, PNG)</p>
                            @error('foto') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700" for="jam_buka">Jam
                                Buka</label>
                            <input id="jam_buka" type="time" wire:model="jam_buka" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                            @error('jam_buka') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700" for="jam_tutup">Jam
                                Tutup</label>
                            <input id="jam_tutup" type="time" wire:model="jam_tutup" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                            @error('jam_tutup') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Fasilitas -->
                <div class="rounded-xl border border-slate-200 p-6">
                    <h2 class="text-base font-semibold text-slate-900">Fasilitas</h2>
                    <p class="mt-1 text-sm text-slate-600">Tambahkan fasilitas yang tersedia di ruangan ini.</p>

                    <div class="mt-4">
                        <div class="flex gap-2">
                            <input type="text" wire:model.live.debounce.300ms="newFasilitas"
                                placeholder="Contoh: Projector, AC, WiFi"
                                class="flex-1 rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                            <button type="button" wire:click="addFasilitas"
                                class="rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700">
                                Tambah
                            </button>
                        </div>
                        @error('newFasilitas') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($fasilitas as $index => $fName)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-sm text-slate-700">
                                {{ $fName }}
                                <button type="button" wire:click="removeFasilitas({{ $index }})"
                                    class="ml-1 rounded-full p-0.5 text-slate-400 transition hover:bg-slate-200 hover:text-rose-600">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </span>
                        @endforeach
                        @if (empty($fasilitas))
                            <p class="text-sm text-slate-400">Belum ada fasilitas ditambahkan.</p>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.ruangan.index') }}"
                        class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Batal
                    </a>
                    <button type="submit"
                        class="rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Ruangan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>