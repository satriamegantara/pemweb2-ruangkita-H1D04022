<?php

namespace App\Livewire\Components;

use App\Models\Reservasi;
use App\Models\Ruangan;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\On;
use Livewire\Component;

#[Isolate]
class SlotTersedia extends Component
{
    public ?int $ruanganId = null;

    public string $tanggal = '';

    public ?Ruangan $ruangan = null;

    public function mount(?int $ruanganId = null, ?string $tanggal = null): void
    {
        $this->ruanganId = $ruanganId;
        $this->tanggal = $tanggal ?: now()->toDateString();

        if ($this->ruanganId) {
            $this->ruangan = Ruangan::query()->find($this->ruanganId);
        }
    }

    #[On('slot-tersedia-refresh')]
    public function refresh(?int $ruanganId = null, ?string $tanggal = null): void
    {
        $this->ruanganId = $ruanganId;
        if ($ruanganId !== null) {
            $this->ruangan = Ruangan::query()->find($ruanganId);
        }
        if ($tanggal !== null) {
            $this->tanggal = $tanggal;
        }
    }

    /**
     * Bangun peta slot per jam (key = jam mulai integer) beserta status.
     * Hanya reservasi dengan status 'disetujui' yang ditandai sebagai terisi.
     *
     * @return array<int, array{jam: string, status: string, label: string}>
     */
    #[Computed]
    public function slots(): array
    {
        $jamBuka = intval(substr($this->ruangan->jam_buka ?? '07:00:00', 0, 2));
        $jamTutup = intval(substr($this->ruangan->jam_tutup ?? '21:00:00', 0, 2));

        $occupied = [];

        if ($this->ruanganId && $this->tanggal) {
            // Hanya hitung yang sudah disetujui
            $reservasi = Reservasi::query()
                ->where('ruangan_id', $this->ruanganId)
                ->whereDate('tanggal', \Carbon\Carbon::parse($this->tanggal))
                ->where('status', 'disetujui')
                ->get(['jam_mulai', 'jam_selesai']);

            foreach ($reservasi as $r) {
                $start = (int) \Carbon\Carbon::parse($r->jam_mulai)->format('H');
                $end = (int) \Carbon\Carbon::parse($r->jam_selesai)->format('H');

                for ($h = $start; $h < $end; $h++) {
                    if (!isset($occupied[$h])) {
                        $occupied[$h] = 'disetujui';
                    }
                }
            }
        }

        $slots = [];
        for ($h = $jamBuka; $h < $jamTutup; $h++) {
            $jam = str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00';
            $status = $occupied[$h] ?? 'kosong';
            $label = $status === 'disetujui' ? 'Sudah Dipesan' : 'Tersedia';

            $slots[$h] = [
                'jam' => $jam,
                'status' => $status,
                'label' => $label,
            ];
        }

        return $slots;
    }

    #[Computed]
    public function ringkasan(): array
    {
        $slots = $this->slots;

        $kosong = 0;
        $disetujui = 0;

        foreach ($slots as $slot) {
            $slot['status'] === 'disetujui' ? $disetujui++ : $kosong++;
        }

        return [
            'tersedia' => $kosong,
            'dipesan' => $disetujui,
            'total' => count($slots),
        ];
    }

    public function render()
    {
        return view('livewire.components.slot-tersedia');
    }
}
