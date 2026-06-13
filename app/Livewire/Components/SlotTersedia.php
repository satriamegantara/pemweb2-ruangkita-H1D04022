<?php

namespace App\Livewire\Components;

use App\Models\Reservasi;
use App\Models\Ruangan;
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
        $this->tanggal = $tanggal ?: today()->toDateString();

        if ($this->ruanganId) {
            $this->ruangan = Ruangan::query()->find($this->ruanganId);
        }
    }

    #[On('slot-tersedia-refresh')]
    public function refresh(?int $ruanganId = null, ?string $tanggal = null): void
    {
        if ($ruanganId !== null) {
            $this->ruanganId = $ruanganId;
            $this->ruangan = Ruangan::query()->find($ruanganId);
        }
        if ($tanggal !== null) {
            $this->tanggal = $tanggal;
        }
    }

    /**
     * Get the operating hours of the room.
     */
    public function getJamBuka(): string
    {
        if (!$this->ruangan) {
            return '07:00';
        }
        return $this->ruangan->jam_buka ?? '07:00';
    }

    public function getJamTutup(): string
    {
        if (!$this->ruangan) {
            return '21:00';
        }
        return $this->ruangan->jam_tutup ?? '21:00';
    }

    /**
     * Get all bookable time slots for a given date.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function slots(): array
    {
        $jamBukaParts = explode(':', $this->getJamBuka());
        $jamTutupParts = explode(':', $this->getJamTutup());
        $jamBukaHour = (int) $jamBukaParts[0];
        $jamTutupHour = (int) $jamTutupParts[0];

        $bookedSlots = [];

        if ($this->ruanganId && $this->tanggal) {
            $reservations = Reservasi::query()
                ->where('ruangan_id', $this->ruanganId)
                ->whereDate('tanggal', $this->tanggal)
                ->get(['jam_mulai', 'jam_selesai', 'status']);

            foreach ($reservations as $res) {
                $startHour = (int) substr($res->jam_mulai, 0, 2);
                $endHour = (int) substr($res->jam_selesai, 0, 2);

                // Calculate status priority for overlapping slots
                $statusPriority = match ($res->status) {
                    'disetujui' => 3,
                    'menunggu' => 2,
                    'ditolak' => 1,
                    default => 0,
                };

                for ($h = $startHour; $h < $endHour; $h++) {
                    if (!isset($bookedSlots[$h]) || $bookedSlots[$h]['priority'] < $statusPriority) {
                        $bookedSlots[$h] = [
                            'status' => $res->status,
                            'priority' => $statusPriority,
                        ];
                    }
                }
            }
        }

        $slots = [];
        for ($h = $jamBukaHour; $h < $jamTutupHour; $h++) {
            $label = str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00';

            if (isset($bookedSlots[$h])) {
                $status = $bookedSlots[$h]['status'];
                $slots[] = [
                    'hour' => $h,
                    'label' => $label,
                    'status' => $status,
                    'isAvailable' => false,
                ];
            } else {
                $slots[] = [
                    'hour' => $h,
                    'label' => $label,
                    'status' => 'tersedia',
                    'isAvailable' => true,
                ];
            }
        }

        return $slots;
    }

    #[Computed]
    public function statistics(): array
    {
        $slots = $this->slots;
        $tersedia = 0;
        $disetujui = 0;
        $menunggu = 0;

        foreach ($slots as $slot) {
            if ($slot['isAvailable']) {
                $tersedia++;
            } elseif ($slot['status'] === 'disetujui') {
                $disetujui++;
            } elseif ($slot['status'] === 'menunggu') {
                $menunggu++;
            }
        }

        return [
            'tersedia' => $tersedia,
            'dipesan' => $disetujui,
            'menunggu' => $menunggu,
            'total' => count($slots),
        ];
    }

    #[Computed]
    public function stats(): array
    {
        return $this->statistics;
    }

    public function getStatsProperty(): array
    {
        return $this->statistics;
    }

    #[Computed]
    public function dateFormatted(): string
    {
        if (!$this->tanggal) {
            return '';
        }
        return \Carbon\Carbon::parse($this->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY');
    }

    #[Computed]
    public function isToday(): bool
    {
        return $this->tanggal === today()->toDateString();
    }

    public function statusColors(): array
    {
        return [
            'tersedia' => ['bg-light' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700'],
            'disetujui' => ['bg-light' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-700'],
            'menunggu' => ['bg-light' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-700'],
            'ditolak' => ['bg-light' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-700'],
        ];
    }

    public function render()
    {
        return view('livewire.components.slot-tersedia');
    }
}
