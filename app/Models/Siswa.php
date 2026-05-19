<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $primaryKey = 'id_siswa';

    protected $fillable = [
        'nama',
        'nis',
        'id_kelas',
        'name',
        'identifier',
        'classroom',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id_ref', 'id_siswa');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class, 'id_siswa', 'id_siswa');
    }
}
