<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservasi extends Model
{
    protected $table = 'reservasi';
    protected $fillable = [
        'ruangan_id', 
        'user_id', '
        tanggal', 
        'jam_mulai', 
        'jam_selesai', 
        'status', 
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
    
    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
