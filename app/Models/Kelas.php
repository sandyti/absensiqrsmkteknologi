<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $primaryKey = 'id_kelas';

    protected $fillable = [
        'nama',
        'tingkat',
    ];

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'id_kelas', 'id_kelas');
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'id_kelas', 'id_kelas');
    }

    public function mapels()
    {
        return $this->belongsToMany(Mapel::class, 'kelas_subject', 'id_kelas', 'id_mapel');
    }
}
