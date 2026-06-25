<?php

namespace App;

use App\Support\CmsRole;
use App\Support\Rbac;
use App\Support\RoleResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CmsUsers extends Model
{
    protected $table = 'cms_users';

    public function stores()
    {
        return $this->hasMany(MapCmsUsersRefWarehouse::class, "cms_users_id");
    }

    public function branches()
    {
        return $this->hasMany(MapCmsUsersRefBranches::class, "cms_users_id");
    }

    public function privilege()
    {
        return $this->belongsTo(CmsPrivilege::class, 'id_cms_privileges');
    }

    public function getRoleSlug(): ?string
    {
        if ($this->relationLoaded('privilege') && $this->privilege) {
            return $this->privilege->slug;
        }

        return RoleResolver::slugForPrivilegeId($this->id_cms_privileges);
    }

    public function hasRole(string $slug): bool
    {
        if (!Rbac::isEnabled()) {
            return false;
        }

        $slug = strtolower(trim($slug));
        $roleSlug = $this->getRoleSlug();

        return $roleSlug !== null && $roleSlug === $slug;
    }

    /**
     * @param string[] $slugs
     */
    public function hasAnyRole(array $slugs): bool
    {
        if (!Rbac::isEnabled()) {
            return false;
        }

        foreach ($slugs as $slug) {
            if ($this->hasRole($slug)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        if (!Rbac::isEnabled()) {
            if (empty($this->id_cms_privileges)) {
                return false;
            }

            $privilege = DB::table('cms_privileges')->where('id', $this->id_cms_privileges)->first();

            return $privilege && !empty($privilege->is_superadmin);
        }

        if ($this->hasRole(CmsRole::SUPER_ADMIN)) {
            return true;
        }

        if ($this->relationLoaded('privilege') && $this->privilege) {
            return !empty($this->privilege->is_superadmin);
        }

        $privilege = DB::table('cms_privileges')->where('id', $this->id_cms_privileges)->first();

        return $privilege && !empty($privilege->is_superadmin);
    }
}
