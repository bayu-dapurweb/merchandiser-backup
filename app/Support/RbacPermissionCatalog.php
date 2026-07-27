<?php

namespace App\Support;

class RbacPermissionCatalog
{
    /**
     * Permissions grouped by module key from config/rbac.php.
     *
     * @return array<string, array<int, array{slug:string,name:string,module:?string,description:?string}>>
     */
    public static function groupedByModule(): array
    {
        $permissions = config('rbac.permissions', []);
        $grouped = [];

        foreach ($permissions as $slug => $meta) {
            $module = $meta['module'] ?? 'general';
            $grouped[$module][] = [
                'slug' => $slug,
                'name' => $meta['name'] ?? ucwords(str_replace('_', ' ', $slug)),
                'module' => $module,
                'description' => $meta['description'] ?? null,
            ];
        }

        ksort($grouped);

        return $grouped;
    }

    /**
     * @return array<string, string>
     */
    public static function moduleLabels(): array
    {
        return config('rbac.permission_modules', []);
    }

    public static function moduleLabel(string $module): string
    {
        $labels = self::moduleLabels();

        return $labels[$module] ?? ucwords(str_replace('_', ' ', $module));
    }
}
