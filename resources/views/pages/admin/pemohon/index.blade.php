<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Kelola Pemohon')] class extends Component {
    use WithPagination;

    public string $search = '';

    public ?int $selectedId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function usersList()
    {
        return User::query()
            ->where('role', 'pemohon')
            ->when(filled($this->search), fn($q) => $q->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate(10);
    }

    public function showUser(int $id): void
    {
        $this->selectedId = $id;
    }

    public function closeView(): void
    {
        $this->selectedId = null;
    }

    #[Computed]
    public function selectedUser(): ?User
    {
        if (!$this->selectedId) {
            return null;
        }

        return User::query()->find($this->selectedId);
    }
};
?>

<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
    <div class="space-y-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-medium text-teal-700">Admin Area</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">Kelola Pemohon</h1>
                    <p class="mt-2 text-slate-600">Daftar akun dengan peran pemohon. Admin bisa melihat detail
                        masing-masing.</p>
                </div>

                <a href="{{ route('admin.dashboard') }}"
                    class="inline-flex text-sm font-medium text-teal-700 hover:text-teal-800">
                    &larr; Kembali ke Dashboard Admin
                </a>
            </div>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Daftar Pemohon</h2>
                    <p class="mt-1 text-sm text-slate-600">Cari berdasarkan nama atau email.</p>
                </div>

                <div class="w-full max-w-sm">
                    <input type="search" wire:model.live.debounce.300ms="search"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                        placeholder="Cari nama atau email">
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Nama</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Email</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                NIM / NIP</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($this->usersList as $user)
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-slate-900">{{ $user->name }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-700">{{ $user->email }}</td>
                                <td class="px-4 py-4 text-sm text-slate-700">{{ $user->nim_nip ?? '-' }}</td>
                                <td class="px-4 py-4 text-right text-sm">
                                    <div class="inline-flex justify-end gap-2">
                                        <button type="button" wire:click="showUser({{ $user->id }})"
                                            class="rounded-lg border border-slate-300 px-3 py-2 font-medium text-slate-700 transition hover:bg-slate-50">Lihat</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada pemohon.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $this->usersList->links() }}</div>

            @if ($this->selectedUser)
                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Detail Pemohon</h3>
                            <p class="mt-1 text-sm text-slate-600">Informasi akun pemohon.</p>
                        </div>
                        <div>
                            <button type="button" wire:click="closeView"
                                class="text-sm font-medium text-slate-500 hover:text-slate-700">Tutup</button>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-sm text-slate-500">Nama</p>
                            <div class="mt-1 text-slate-900 font-medium">{{ $this->selectedUser->name }}</div>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Email</p>
                            <div class="mt-1 text-slate-900 font-medium">{{ $this->selectedUser->email }}</div>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">NIM / NIP</p>
                            <div class="mt-1 text-slate-900 font-medium">{{ $this->selectedUser->nim_nip ?? '-' }}</div>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Role</p>
                            <div class="mt-1 text-slate-900 font-medium">{{ $this->selectedUser->role }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>