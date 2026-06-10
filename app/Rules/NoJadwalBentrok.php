<?php

namespace App\Rules;

use App\Models\Reservasi;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoJadwalBentrok implements ValidationRule
{
    public function __construct(
        protected ?int $ruanganId,
        protected ?string $tanggal,
        protected ?string $jamMulai,
        protected ?string $jamSelesai,
        protected ?int $ignoreId = null,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->ruanganId || !$this->tanggal || !$this->jamMulai || !$this->jamSelesai) {
            return;
        }

        try {
            $tanggal = Carbon::parse($this->tanggal)->toDateString();
            $jamMulai = Carbon::createFromFormat('H:i', $this->jamMulai)->format('H:i:00');
            $jamSelesai = Carbon::createFromFormat('H:i', $this->jamSelesai)->format('H:i:00');
        } catch (\Throwable) {
            return;
        }

        if ($jamSelesai <= $jamMulai) {
            return;
        }

        $bentrok = Reservasi::query()
            ->where('ruangan_id', $this->ruanganId)
            ->whereDate('tanggal', $tanggal)
            ->when($this->ignoreId, fn($query) => $query->where('id', '!=', $this->ignoreId))
            ->where(function ($query) use ($jamMulai, $jamSelesai) {
                $query->whereBetween('jam_mulai', [$jamMulai, $jamSelesai])
                    ->orWhereBetween('jam_selesai', [$jamMulai, $jamSelesai])
                    ->orWhere(function ($query) use ($jamMulai, $jamSelesai) {
                        $query->where('jam_mulai', '<=', $jamMulai)
                            ->where('jam_selesai', '>=', $jamSelesai);
                    });
            })
            ->exists();

        if ($bentrok) {
            $fail('Jadwal bentrok dengan reservasi lain pada ruangan dan tanggal yang sama.');
        }
    }
}