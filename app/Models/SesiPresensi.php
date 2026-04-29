<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesiPresensi extends Model
{
    use HasFactory;

    protected $table = 'sesi_presensi';

    protected $primaryKey = 'id_sesi';

    public $timestamps = false;

    protected $fillable = [
        'id_jadwal',
        'tanggal',
        'start_time',
        'end_time',
        'token',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'id_jadwal', 'id_jadwal');
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class, 'id_sesi', 'id_sesi');
    }
}
