<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component
{
    //
};
?>

<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-teal-700">Dashboard</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">
                    Selamat datang, {{ auth()->user()->name }}!
                </h1>
                <p class="mt-2 text-slate-600">
                    Anda login sebagai
                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                        {{ auth()->user()->isAdmin() ? 'Admin' : 'Pemohon' }}
                    </span>
                </p>
            </div>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-900">Reservasi Ruang</h2>
                <p class="mt-2 text-sm text-slate-600">Ajukan pemesanan ruang kelas atau laboratorium.</p>
                <p class="mt-3 text-xs text-slate-400">Fitur akan tersedia pada commit berikutnya.</p>
            </div>

            <div class="rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-900">Jadwal Harian</h2>
                <p class="mt-2 text-sm text-slate-600">Lihat ketersediaan ruang per hari.</p>
                <p class="mt-3 text-xs text-slate-400">Fitur akan tersedia pada commit berikutnya.</p>
            </div>

            @if (auth()->user()->isAdmin())
                <div class="rounded-xl border border-slate-200 p-5">
                    <h2 class="font-semibold text-slate-900">Kelola Ruangan</h2>
                    <p class="mt-2 text-sm text-slate-600">CRUD ruangan dan persetujuan reservasi.</p>
                    <p class="mt-3 text-xs text-slate-400">Fitur akan tersedia pada commit berikutnya.</p>
                </div>
            @endif
        </div>
    </div>
</div>
