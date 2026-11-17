<?php

namespace App\Models\AllUsers;

use App\Models\PublicSettings\Permission;
use App\Models\PublicSettings\Role;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'phone', 'avatar', 'password', 'status', 'role_id',
        'two_factor_enabled', 'code', 'expires_at', 'otp_attempts',
        'otp_locked_until', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token', 'code'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'expires_at' => 'datetime',
            'otp_locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Per-user overrides on top of the role: granted=true adds a permission,
     * granted=false revokes one the role would otherwise give.
     */
    public function permissionOverrides(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withPivot('granted');
    }

    public function extraPermissions(): BelongsToMany
    {
        return $this->permissionOverrides()->wherePivot('granted', true);
    }

    public function revokedPermissions(): BelongsToMany
    {
        return $this->permissionOverrides()->wherePivot('granted', false);
    }

    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin';
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return in_array($slug, $this->permissionSlugs(), true);
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    public function permissionSlugs(): array
    {
        return $this->grantedPermissions()->pluck('slug')->all();
    }

    public function permissionIds(): array
    {
        if ($this->isAdmin()) {
            return Permission::query()->pluck('id')->all();
        }

        return $this->grantedPermissions()->pluck('id')->all();
    }

    private function grantedPermissions()
    {
        $this->loadMissing(['role.permissions', 'permissionOverrides']);
        $overrides = $this->permissionOverrides;
        $revoked = $overrides->filter(fn ($permission) => ! $permission->pivot->granted)->pluck('id')->all();

        return ($this->role?->permissions ?? collect())
            ->concat($overrides->filter(fn ($permission) => (bool) $permission->pivot->granted))
            ->reject(fn ($permission) => in_array($permission->id, $revoked, true))
            ->unique('id')
            ->values();
    }

    public function generateCode(): void
    {
        $this->timestamps = false;
        $this->code = (string) random_int(1000, 9999);
        $this->expires_at = now()->addSeconds(60);
        $this->otp_attempts = 0;
        $this->save();
    }

    public function resetCode(): void
    {
        $this->timestamps = false;
        $this->code = null;
        $this->expires_at = null;
        $this->otp_attempts = 0;
        $this->otp_locked_until = null;
        $this->save();
    }

    public function isOtpLocked(): bool
    {
        return $this->otp_locked_until && $this->otp_locked_until->isFuture();
    }
}
