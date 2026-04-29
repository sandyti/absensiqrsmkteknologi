<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'user';

    public const ROLE_ADMIN = 'admin';
    public const ROLE_GURU = 'guru';
    public const ROLE_SISWA = 'siswa';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_user',
        'username',
        'id_ref',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (blank($user->id_user)) {
                $user->id_user = (string) Str::ulid();
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function presensis()
    {
        return $this->hasMany(Presensi::class, 'id_siswa', 'id_ref');
    }

    public function getNameAttribute(): string
    {
        if ($this->role === self::ROLE_ADMIN) {
            return 'Administrator';
        }

        if ($this->role === self::ROLE_GURU) {
            return (string) ($this->guruProfile?->nama ?? $this->username);
        }

        if ($this->role === self::ROLE_SISWA) {
            return (string) ($this->siswaProfile?->nama ?? $this->username);
        }

        return (string) $this->username;
    }

    public function getEmailAttribute(): string
    {
        return (string) $this->username;
    }

    public function guruProfile()
    {
        return $this->belongsTo(Guru::class, 'id_ref', 'id_guru');
    }

    public function siswaProfile()
    {
        return $this->belongsTo(Siswa::class, 'id_ref', 'id_siswa');
    }

    public function editedPresensis()
    {
        return $this->hasMany(Presensi::class, 'edited_by', 'id_ref');
    }

    public function getEmailForPasswordReset(): string
    {
        return (string) $this->username;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isGuru(): bool
    {
        return $this->role === self::ROLE_GURU;
    }

    public function isSiswa(): bool
    {
        return $this->role === self::ROLE_SISWA;
    }
}
