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
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recordedAttendances()
    {
        return $this->hasMany(Attendance::class, 'recorded_by');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_student', 'student_id', 'subject_id');
    }

    public function getNameAttribute(): string
    {
        if ($this->role === self::ROLE_ADMIN) {
            return 'Administrator';
        }

        if ($this->role === self::ROLE_GURU) {
            return (string) ($this->guruProfile?->name ?? $this->username);
        }

        if ($this->role === self::ROLE_SISWA) {
            return (string) ($this->siswaProfile?->name ?? $this->username);
        }

        return (string) $this->username;
    }

    public function getEmailAttribute(): string
    {
        return (string) $this->username;
    }

    public function getIdentifierAttribute(): ?string
    {
        if ($this->role === self::ROLE_GURU) {
            return $this->guruProfile?->identifier;
        }

        if ($this->role === self::ROLE_SISWA) {
            return $this->siswaProfile?->identifier;
        }

        return null;
    }

    public function getClassroomAttribute(): ?string
    {
        return $this->role === self::ROLE_SISWA ? $this->siswaProfile?->classroom : null;
    }

    public function getTeachesClassAttribute(): ?string
    {
        return $this->role === self::ROLE_GURU ? $this->guruProfile?->teaches_class : null;
    }

    public function getSubjectAttribute(): ?string
    {
        return $this->role === self::ROLE_GURU ? $this->guruProfile?->subject : null;
    }

    public function getTeachingHoursAttribute(): ?string
    {
        return $this->role === self::ROLE_GURU ? $this->guruProfile?->teaching_hours : null;
    }

    public function guruProfile()
    {
        return $this->belongsTo(Guru::class, 'id_ref');
    }

    public function siswaProfile()
    {
        return $this->belongsTo(Siswa::class, 'id_ref');
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
