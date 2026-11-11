<?php

namespace App\Models;

use App\Enums\DefaultRoleSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Admin extends Authenticatable
{
    use Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'password_changed_at', 'role_id', 'profile_photo_path', 'is_active',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at'  => 'datetime',
        'password_changed_at' => 'datetime',
        'is_active'          => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // ===== Scopes =====

    /**
     * Free-text search across the officer's name and email — the accounts
     * list's search box.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('name', 'like', $like)->orWhere('email', 'like', $like);
        });
    }

    /**
     * Whether this admin holds the given role.
     *
     */
    public function hasRole(DefaultRoleSlug|string $slug): bool
    {
        $slug = $slug instanceof DefaultRoleSlug ? $slug->value : $slug;

        return $this->role?->name === $slug;
    }

    /**
     * Whether this admin holds any of the given roles.
     *
     * @param  array<int, DefaultRoleSlug|string>  $slugs
     */
    public function hasAnyRole(array $slugs): bool
    {
        foreach ($slugs as $slug) {
            if ($this->hasRole($slug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether this admin is the only administrator left in the system.
     */
    public function isLastAdministrator(): bool
    {
        if (! $this->hasRole(DefaultRoleSlug::ADMINISTRATOR())) {
            return false;
        }

        return static::whereHas('role', function (Builder $query) {
                $query->where('name', DefaultRoleSlug::ADMINISTRATOR);
            })
            ->where('id', '!=', $this->id)
            ->doesntExist();
    }

    /**
     * Whether this admin's role grants an action on a module.
     */
    public function hasPermission(string $module, string $type): bool
    {
        return $this->role?->hasPermission($module, $type) ?? false;
    }

    /**
     * Get the admin activities for this admin.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(AdminActivity::class, 'admin_id');
    }

    /**
     * Get the URL for the admin's profile image (used by AdminLTE).
     *
     * @return string
     */
    public function adminlte_image(): string
    {
        if ($this->profile_photo_path) {
            return Storage::disk('public')->url($this->profile_photo_path);
        }

        if (file_exists(public_path('image/user.png'))) {
            return asset('image/user.png');
        }

        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email))) . '?s=100&d=mm';
    }

    /**
     * Get the description / role label for the admin (used by AdminLTE).
     *
     * @return string
     */
    public function adminlte_desc(): string
    {
        return ucfirst($this->role->name ?? 'Staff');
    }

    /**
     * Get the profile route name (used by AdminLTE for the profile link).
     *
     * @return string
     */
    public function adminlte_profile_url(): string
    {
        return (string) config('adminlte.profile_url');
    }
}
