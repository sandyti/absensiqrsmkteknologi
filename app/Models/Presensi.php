<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensi';

    protected $primaryKey = 'id_presensi';

    public $timestamps = false;

    protected $fillable = [
        'id_sesi',
        'id_siswa',
        'edited_by',
        'status',
        'scanned_at',
        'method',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function sesiPresensi()
    {
        return $this->belongsTo(SesiPresensi::class, 'id_sesi', 'id_sesi');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function editor()
    {
        return $this->belongsTo(Guru::class, 'edited_by', 'id_guru');
    }
}
