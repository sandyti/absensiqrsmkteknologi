<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesiPresensi extends Model
{
    use HasFactory;

    protected $table = 'sesi_presensis';

    protected $primaryKey = 'id_sesi';

    public $timestamps = false;

    protected $fillable = [
        'id_jadwal',
        'tanggal',
        'token',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'id_jadwal', 'id_jadwal');
    }
}
