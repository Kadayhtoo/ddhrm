<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable; 
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'profile_image',
        'password',
        'department_id',
        'position_id',
        'salary',
        'shift_id',
        'is_active',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'salary' => 'decimal:2',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasRoleSlug(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->exists();
    }

    public function hasPermission(string $slug): bool
    {
        return $this->roles()->whereHas('permissions', fn ($q) => $q->where('slug', $slug))->exists();
    }

    /**
     * @param  list<string>  $slugs
     */
    public function hasAnyPermission(array $slugs): bool
    {
        foreach ($slugs as $slug) {
            if ($this->hasPermission($slug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public function permissionSlugs(): array
    {
        $this->loadMissing('roles.permissions');

        return $this->roles
            ->pluck('permissions')
            ->flatten()
            ->pluck('slug')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<array{id:int,name:string,slug:string}>
     */
    public function rolesPayload(): array
    {
        return $this->roles
            ->map(fn (Role $r) => ['id' => $r->id, 'name' => $r->name, 'slug' => $r->slug])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing('roles.permissions');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'department_id' => $this->department_id,
            'position_id' => $this->position_id,
            'salary' => $this->salary,
            'shift_id' => $this->shift_id,
            'is_active' => $this->is_active,
            'roles' => $this->rolesPayload(),
            'permissions' => $this->permissionSlugs(),
            'profile_image_url' => $this->profile_image 
            ? asset('storage/' . $this->profile_image) 
            : null,
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests() {

        return $this->hasMany(LeaveRequest::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function documents()
    {
        return $this->hasMany(StaffDocument::class, 'staff_id');
    }
}
