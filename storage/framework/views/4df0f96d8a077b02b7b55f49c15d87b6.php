<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
?>

<div class="mx-auto max-w-md px-4 py-12 sm:px-6 sm:py-16">
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-slate-900">Masuk ke <?php echo e(config('app.name')); ?></h1>
            <p class="mt-2 text-sm text-slate-600">Gunakan akun Anda untuk mengakses sistem reservasi.</p>
        </div>

        <form wire:submit="login" class="space-y-5">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                <input
                    id="email"
                    type="email"
                    wire:model.live="email"
                    autocomplete="email"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    placeholder="nama@email.com"
                >
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Kata Sandi</label>
                <input
                    id="password"
                    type="password"
                    wire:model.live="password"
                    autocomplete="current-password"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    placeholder="••••••••"
                >
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input
                    type="checkbox"
                    wire:model="remember"
                    class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                >
                Ingat saya
            </label>

            <button
                type="submit"
                class="w-full rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:opacity-50"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="login">Masuk</span>
                <span wire:loading wire:target="login">Memproses...</span>
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            Belum punya akun?
            <a href="<?php echo e(url('/register')); ?>" class="font-medium text-teal-700 hover:text-teal-800">Daftar sekarang</a>
        </p>
    </div>
</div><?php /**PATH D:\Informatika\Sem 4\Pemrograman Web II\ruangkita\storage\framework\views/livewire/views/367c1be1.blade.php ENDPATH**/ ?>