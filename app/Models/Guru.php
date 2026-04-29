<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'gurus';

    protected $primaryKey = 'id_guru';

    protected $fillable = [
        'nama',
        'nip',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id_ref', 'id_guru');
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'id_guru', 'id_guru');
    }
}
