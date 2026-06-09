<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Daftar')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $nim_nip = '';

    public string $password = '';

    public string $password_confirmation = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'nim_nip' => ['required', 'string', 'max:50', 'unique:users,nim_nip'],
            'password' => ['required', 'string', Password::min(8)],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'nim_nip.required' => 'NIM/NIP wajib diisi.',
            'nim_nip.unique' => 'NIM/NIP sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password_confirmation.required' => 'Konfirmasi kata sandi wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi kata sandi tidak cocok.',
        ];
    }

    public function updated(string $property): void
    {
        if ($property === 'password' && filled($this->password_confirmation)) {
            $this->validateOnly(['password', 'password_confirmation']);

            return;
        }

        $this->validateOnly($property);
    }

    public function register(): void
    {
        $validated = $this->validate();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nim_nip' => $validated['nim_nip'],
            'password' => $validated['password'],
            'role' => 'pemohon',
        ]);

        Auth::login($user);

        session()->regenerate();

        $this->redirect(route('dashboard'), navigate: true);
    }
};
?>

<div class="mx-auto max-w-md px-4 py-12 sm:px-6 sm:py-16">
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-slate-900">Daftar Akun Baru</h1>
            <p class="mt-2 text-sm text-slate-600">Buat akun pemohon untuk mulai memesan ruang.</p>
        </div>

        <form wire:submit="register" class="space-y-5">
            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama Lengkap</label>
                <input
                    id="name"
                    type="text"
                    wire:model.live="name"
                    autocomplete="name"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 @error('name') border-red-500 @enderror"
                    placeholder="Nama lengkap"
                >
                @error('name')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                <input
                    id="email"
                    type="email"
                    wire:model.live="email"
                    autocomplete="email"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 @error('email') border-red-500 @enderror"
                    placeholder="nama@email.com"
                >
                @error('email')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nim_nip" class="mb-1.5 block text-sm font-medium text-slate-700">NIM / NIP</label>
                <input
                    id="nim_nip"
                    type="text"
                    wire:model.live="nim_nip"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 @error('nim_nip') border-red-500 @enderror"
                    placeholder="Contoh: 1234567890"
                >
                @error('nim_nip')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Kata Sandi</label>
                <input
                    id="password"
                    type="password"
                    wire:model.live="password"
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 @error('password') border-red-500 @enderror"
                    placeholder="Minimal 8 karakter"
                >
                @error('password')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">Konfirmasi Kata Sandi</label>
                <input
                    id="password_confirmation"
                    type="password"
                    wire:model.live="password_confirmation"
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 @error('password_confirmation') border-red-500 @enderror"
                    placeholder="Ulangi kata sandi"
                >
                @error('password_confirmation')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:opacity-50"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="register">Daftar</span>
                <span wire:loading wire:target="register">Memproses...</span>
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            Sudah punya akun?
            <a href="{{ url('/login') }}" class="font-medium text-teal-700 hover:text-teal-800">Masuk di sini</a>
        </p>
    </div>
</div>
