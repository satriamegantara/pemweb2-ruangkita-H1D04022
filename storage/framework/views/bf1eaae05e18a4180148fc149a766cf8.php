<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Beranda')] class extends Component
{
    //
};
?>

<div>
    <section class="relative overflow-hidden bg-gradient-to-br from-teal-700 via-teal-600 to-cyan-600 text-white">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%220.05%22%3E%3Cpath d=%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-40"></div>

        <div class="relative mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28">
            <div class="max-w-2xl">
                <p class="mb-4 inline-flex rounded-full bg-white/15 px-3 py-1 text-sm font-medium backdrop-blur-sm">
                    Sistem Reservasi Ruang Kampus
                </p>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                    Pesan ruang kelas & laboratorium tanpa bentrok jadwal
                </h1>
                <p class="mt-6 text-lg text-teal-50">
                    <?php echo e(config('app.name')); ?> membantu dosen dan mahasiswa memesan ruang pada slot waktu tertentu
                    dengan validasi jadwal otomatis dan alur persetujuan yang terstruktur.
                </p>
                <div class="mt-10 flex flex-wrap gap-4">
                    <a
                        href="<?php echo e(url('/register')); ?>"
                        class="rounded-lg bg-white px-5 py-3 text-sm font-semibold text-teal-700 shadow-sm transition hover:bg-teal-50"
                    >
                        Mulai Reservasi
                    </a>
                    <a
                        href="<?php echo e(url('/login')); ?>"
                        class="rounded-lg border border-white/40 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                    >
                        Masuk ke Akun
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="fitur" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20">
        <div class="mb-12 text-center">
            <h2 class="text-3xl font-bold text-slate-900">Fitur Utama</h2>
            <p class="mt-3 text-slate-600">Semua yang dibutuhkan program studi untuk mengelola pemakaian ruang.</p>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900">Reservasi Mudah</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    Pilih ruang, tanggal, dan jam mulai–selesai lewat form interaktif tanpa reload halaman penuh.
                </p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900">Cegah Bentrok Jadwal</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    Sistem memvalidasi irisan waktu secara realtime sehingga tidak ada dua reservasi tabrakan di ruang yang sama.
                </p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900">Persetujuan Admin</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    Setiap pengajuan memiliki status menunggu, disetujui, atau ditolak dengan alur persetujuan yang jelas.
                </p>
            </article>
        </div>
    </section>

    <section id="cara-kerja" class="bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-slate-900">Cara Kerja</h2>
                <p class="mt-3 text-slate-600">Empat langkah sederhana untuk memesan ruang.</p>
            </div>

            <ol class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                    ['step' => '1', 'title' => 'Daftar Akun', 'desc' => 'Buat akun sebagai pemohon (dosen/mahasiswa).'],
                    ['step' => '2', 'title' => 'Pilih Ruang & Waktu', 'desc' => 'Tentukan ruang, tanggal, dan slot jam yang dibutuhkan.'],
                    ['step' => '3', 'title' => 'Ajukan Reservasi', 'desc' => 'Kirim permohonan dan pantau status persetujuan.'],
                    ['step' => '4', 'title' => 'Gunakan Ruang', 'desc' => 'Setelah disetujui, ruang siap dipakai sesuai jadwal.'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="rounded-2xl border border-slate-200 p-6">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-teal-600 text-sm font-bold text-white">
                            <?php echo e($item['step']); ?>

                        </span>
                        <h3 class="mt-4 font-semibold text-slate-900"><?php echo e($item['title']); ?></h3>
                        <p class="mt-2 text-sm text-slate-600"><?php echo e($item['desc']); ?></p>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ol>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <div class="rounded-2xl bg-slate-900 px-8 py-12 text-center text-white sm:px-12">
            <h2 class="text-2xl font-bold sm:text-3xl">Siap mengatur jadwal ruang kampus?</h2>
            <p class="mx-auto mt-4 max-w-xl text-slate-300">
                Bergabung dengan <?php echo e(config('app.name')); ?> dan hindari bentrok jadwal ruang kelas maupun laboratorium.
            </p>
            <a
                href="<?php echo e(url('/register')); ?>"
                class="mt-8 inline-flex rounded-lg bg-teal-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-400"
            >
                Daftar Sekarang
            </a>
        </div>
    </section>
</div>
<?php /**PATH D:\Informatika\Sem 4\Pemrograman Web II\ruangkita\resources\views\pages\landing.blade.php ENDPATH**/ ?>