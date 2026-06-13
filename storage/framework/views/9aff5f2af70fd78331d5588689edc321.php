<?php

use App\Models\Ruangan;
use App\Rules\NoJadwalBentrok;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Buat Reservasi')] class extends Component {
    public ?int $ruangan_id = null;

    public string $tanggal = '';

    public string $jam_mulai = '';

    public string $jam_selesai = '';

    public string $keterangan = '';

    public string $jamBukaRuangan = '';

    public string $jamTutupRuangan = '';

    public function mount(): void
    {
        $this->tanggal = now()->toDateString();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['ruangan_id', 'tanggal', 'jam_mulai', 'jam_selesai'], true)) {
            $this->syncJamOperasional();
            $this->validateOnly($property);
            $this->validateOnly('jam_selesai');

            if (in_array($property, ['ruangan_id', 'tanggal'], true)) {
                $this->dispatch('slot-tersedia-refresh',
                    ruanganId: $this->ruangan_id,
                    tanggal: $this->tanggal,
                );
            }

            return;
        }

        $this->validateOnly($property);
    }

    protected function rules(): array
    {
        return [
            'ruangan_id' => ['required', 'exists:ruangan,id'],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam_mulai' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if ($this->jamBukaRuangan && $value < $this->jamBukaRuangan) {
                        $fail('Jam mulai harus berada dalam jam operasional ruangan.');
                    }
                },
            ],
            'jam_selesai' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if ($this->jamTutupRuangan && $value > $this->jamTutupRuangan) {
                        $fail('Jam selesai harus berada dalam jam operasional ruangan.');
                    }
                },
                new NoJadwalBentrok($this->ruangan_id, $this->tanggal, $this->jam_mulai, $this->jam_selesai),
            ],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'ruangan_id.required' => 'Ruangan wajib dipilih.',
            'ruangan_id.exists' => 'Ruangan tidak ditemukan.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.after_or_equal' => 'Tanggal reservasi minimal hari ini.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.date_format' => 'Format jam selesai tidak valid.',
            'keterangan.max' => 'Keterangan terlalu panjang.',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $ruangan = Ruangan::query()->findOrFail($validated['ruangan_id']);

        $jamMulai = Carbon::createFromFormat('H:i', $validated['jam_mulai']);
        $jamSelesai = Carbon::createFromFormat('H:i', $validated['jam_selesai']);
        $jamBuka = Carbon::createFromFormat('H:i:s', $ruangan->jam_buka);
        $jamTutup = Carbon::createFromFormat('H:i:s', $ruangan->jam_tutup);

        if ($jamMulai->lt($jamBuka)) {
            $this->addError('jam_mulai', 'Jam mulai harus berada dalam jam operasional ruangan.');

            return;
        }

        if ($jamSelesai->gt($jamTutup)) {
            $this->addError('jam_selesai', 'Jam selesai harus berada dalam jam operasional ruangan.');

            return;
        }

        if ($jamSelesai->lessThanOrEqualTo($jamMulai)) {
            $this->addError('jam_selesai', 'Jam selesai harus lebih besar dari jam mulai.');

            return;
        }

        Auth::user()?->reservasi()->create([
            'ruangan_id' => $ruangan->id,
            'tanggal' => $validated['tanggal'],
            'jam_mulai' => $jamMulai->format('H:i:00'),
            'jam_selesai' => $jamSelesai->format('H:i:00'),
            'status' => 'menunggu',
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        $this->dispatch(
            'notify',
            type: 'success',
            title: 'Reservasi disimpan',
            message: 'Permintaan reservasi berhasil dikirim dan menunggu persetujuan admin.',
        );

        $this->reset(['ruangan_id', 'jam_mulai', 'jam_selesai', 'keterangan', 'jamBukaRuangan', 'jamTutupRuangan']);
        $this->tanggal = now()->toDateString();
        $this->resetErrorBag();
    }

    public function syncJamOperasional(): void
    {
        if (!$this->ruangan_id) {
            $this->jamBukaRuangan = '';
            $this->jamTutupRuangan = '';

            return;
        }

        $ruangan = Ruangan::query()->find($this->ruangan_id);

        $this->jamBukaRuangan = $ruangan ? substr($ruangan->jam_buka, 0, 5) : '';
        $this->jamTutupRuangan = $ruangan ? substr($ruangan->jam_tutup, 0, 5) : '';
    }

    #[Computed]
    public function ruanganList()
    {
        return Ruangan::query()
            ->orderBy('nama')
            ->get();
    }
};
?>

<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6">
    <div class="space-y-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-medium text-teal-700">Reservasi Ruangan</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">Buat Reservasi</h1>
                    <p class="mt-2 text-slate-600">Pilih ruangan, tanggal, dan jam penggunaan. Sistem akan menolak
                        jadwal yang bentrok.</p>
                </div>

                <a href="<?php echo e(route('dashboard')); ?>"
                    class="inline-flex text-sm font-medium text-teal-700 hover:text-teal-800">
                    &larr; Kembali ke Dashboard
                </a>
            </div>
        </div>

        <section class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:grid-cols-3">
            <form wire:submit="save" class="space-y-5 lg:col-span-2">
                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700" for="ruangan_id">Ruangan</label>
                        <select id="ruangan_id" wire:model.live="ruangan_id"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 <?php $__errorArgs = ['ruangan_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">Pilih ruangan</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->ruanganList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ruangan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($ruangan->id); ?>"><?php echo e($ruangan->nama); ?> (<?php echo e($ruangan->kapasitas); ?> orang)
                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['ruangan_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1.5 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->jamBukaRuangan && $this->jamTutupRuangan): ?>
                            <p class="mt-2 text-xs text-slate-500">Jam operasional ruangan: <?php echo e($this->jamBukaRuangan); ?> -
                                <?php echo e($this->jamTutupRuangan); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700" for="tanggal">Tanggal</label>
                        <input id="tanggal" type="date" wire:model.live="tanggal"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 <?php $__errorArgs = ['tanggal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tanggal'];
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
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700" for="jam_mulai">Jam Mulai</label>
                        <input id="jam_mulai" type="time" wire:model.live="jam_mulai"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 <?php $__errorArgs = ['jam_mulai'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['jam_mulai'];
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
                        <label class="mb-1.5 block text-sm font-medium text-slate-700" for="jam_selesai">Jam
                            Selesai</label>
                        <input id="jam_selesai" type="time" wire:model.live="jam_selesai"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 <?php $__errorArgs = ['jam_selesai'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['jam_selesai'];
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
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700" for="keterangan">Keterangan</label>
                    <textarea id="keterangan" rows="4" wire:model.live="keterangan"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 <?php $__errorArgs = ['keterangan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="Contoh: Seminar proposal, rapat himpunan, dan lain-lain"></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['keterangan'];
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

                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">
                    Jadwal yang dipilih akan divalidasi secara realtime agar tidak bentrok dengan reservasi lain pada
                    ruangan dan tanggal yang sama.
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700">
                        Simpan Reservasi
                    </button>
                </div>
            </form>

            <div class="lg:col-span-1">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('slot-tersedia', ['ruangan-id' => $this->ruangan_id,'tanggal' => ''.e($this->tanggal).'']);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3329840535-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        </section>
    </div>
</div><?php /**PATH D:\Informatika\Sem 4\Pemrograman Web II\ruangkita\resources\views\pages\reservasi\create.blade.php ENDPATH**/ ?>