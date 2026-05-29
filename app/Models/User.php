<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Modules\People\Models\Student;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * @return HasMany<Student, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    /**
     * @return HasOne<Student, $this>
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    /**
     * @return MorphMany<DatabaseNotification, $this>
     */
    public function parentNotifications(): MorphMany
    {
        return $this->notifications()
            ->where('type', \Modules\Notifications\Notifications\PortalNotification::class)
            ->where('data->audience', 'parent');
    }

    /**
     * @return MorphMany<DatabaseNotification, $this>
     */
    public function studentNotifications(): MorphMany
    {
        return $this->notifications()
            ->where('type', \Modules\Notifications\Notifications\PortalNotification::class)
            ->where('data->audience', 'student');
    }
}
