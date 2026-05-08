<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'subjects';

    protected $primaryKey = 'id_mapel';

    protected $fillable = [
        'nama_mapel',
        'jam_pelajaran',
    ];

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_subject', 'id_mapel', 'id_kelas');
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'id_mapel', 'id_mapel');
    }
}
