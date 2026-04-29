<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'identifier',
        'classroom',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id_ref');
    }
}
