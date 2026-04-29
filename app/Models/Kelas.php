<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'school_classes';

    protected $primaryKey = 'id_kelas';

    protected $fillable = [
        'nama',
        'tingkat',
    ];

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'class_id', 'id_kelas');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'id_kelas', 'id_kelas');
    }
}
