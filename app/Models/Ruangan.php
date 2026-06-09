<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruangan extends Model
{
    protected $table = 'ruangan';
    protected $fillable = [
        'nama', 
        'kapasitas', 
        'jam_buka', 
        'jam_tutup', 
        'foto'
    ];

    public function reservasi(): HasMany
    {
        return $this->hasMany(Reservasi::class);
    }

    public function fasilitas(): HasMany
    {
        return $this->hasMany(Fasilitas::class);
    }
}
