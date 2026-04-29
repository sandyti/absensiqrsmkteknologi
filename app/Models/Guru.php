<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'identifier',
        'teaches_class',
        'subject',
        'teaching_hours',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id_ref');
    }
}
