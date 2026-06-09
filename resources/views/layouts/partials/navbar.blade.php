<header class="border-b border-slate-200 bg-white/80 backdrop-blur-sm">
    <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
        <a href="{{ url('/') }}" class="flex items-center gap-2 font-semibold text-slate-900">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-600 text-sm font-bold text-white">RK</span>
            <span>{{ config('app.name') }}</span>
        </a>

        <div class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
            <a href="{{ url('/') }}" class="transition hover:text-teal-700">Beranda</a>
            <a href="#fitur" class="transition hover:text-teal-700">Fitur</a>
            <a href="#cara-kerja" class="transition hover:text-teal-700">Cara Kerja</a>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ url('/login') }}"
                class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            >
                Masuk
            </a>
            <a
                href="{{ url('/register') }}"
                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700"
            >
                Daftar
            </a>
        </div>
    </nav>
</header>
