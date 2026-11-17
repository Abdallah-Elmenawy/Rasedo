<?php

namespace App\Support;

use App\Models\AllUsers\User;
use App\Models\PublicSettings\Permission;
use App\Models\PublicSettings\Role;
use Illuminate\Support\Collection;

class PermissionCatalog
{
    public static function modules(): array
    {
        return [
            'dashboard' => 'لوحة التحكم',
            'customers' => 'العملاء',
            'suppliers' => 'الموردون',
            'products' => 'المنتجات والتصنيفات',
            'inventory' => 'المخزون',
            'pos' => 'نقطة البيع',
            'sales' => 'المبيعات',
            'purchases' => 'المشتريات',
            'accounting' => 'الحسابات',
            'treasury' => 'الخزينة',
            'expenses' => 'المصروفات والإيرادات',
            'reports' => 'التقارير',
            'users' => 'المستخدمون والصلاحيات',
            'settings' => 'الإعدادات',
        ];
    }

    public static function all(): array
    {
        return [
            ['name' => 'عرض لوحة التحكم', 'slug' => 'dashboard.view', 'module' => 'dashboard', 'action' => 'view'],

            ['name' => 'عرض العملاء', 'slug' => 'customers.view', 'module' => 'customers', 'action' => 'view'],
            ['name' => 'إضافة عميل', 'slug' => 'customers.create', 'module' => 'customers', 'action' => 'create'],
            ['name' => 'تعديل عميل وتحصيل', 'slug' => 'customers.update', 'module' => 'customers', 'action' => 'update'],
            ['name' => 'حذف عميل', 'slug' => 'customers.delete', 'module' => 'customers', 'action' => 'delete'],

            ['name' => 'عرض الموردين', 'slug' => 'suppliers.view', 'module' => 'suppliers', 'action' => 'view'],
            ['name' => 'إضافة مورد', 'slug' => 'suppliers.create', 'module' => 'suppliers', 'action' => 'create'],
            ['name' => 'تعديل مورد وصرف', 'slug' => 'suppliers.update', 'module' => 'suppliers', 'action' => 'update'],
            ['name' => 'حذف مورد', 'slug' => 'suppliers.delete', 'module' => 'suppliers', 'action' => 'delete'],

            ['name' => 'عرض المنتجات والتصنيفات', 'slug' => 'products.view', 'module' => 'products', 'action' => 'view'],
            ['name' => 'إضافة منتج أو تصنيف', 'slug' => 'products.create', 'module' => 'products', 'action' => 'create'],
            ['name' => 'تعديل منتج أو تصنيف', 'slug' => 'products.update', 'module' => 'products', 'action' => 'update'],
            ['name' => 'حذف منتج أو تصنيف', 'slug' => 'products.delete', 'module' => 'products', 'action' => 'delete'],

            ['name' => 'عرض المخزون', 'slug' => 'inventory.view', 'module' => 'inventory', 'action' => 'view'],
            ['name' => 'تسجيل حركة مخزون', 'slug' => 'inventory.create', 'module' => 'inventory', 'action' => 'create'],

            ['name' => 'عرض ملخص المبيعات', 'slug' => 'pos.view', 'module' => 'pos', 'action' => 'view'],
            ['name' => 'شاشة البيع وإصدار الفاتورة', 'slug' => 'pos.create', 'module' => 'pos', 'action' => 'create'],

            ['name' => 'عرض فواتير المبيعات', 'slug' => 'sales.view', 'module' => 'sales', 'action' => 'view'],
            ['name' => 'إنشاء فاتورة أو مرتجع', 'slug' => 'sales.create', 'module' => 'sales', 'action' => 'create'],
            ['name' => 'تعديل وتأكيد وتحصيل', 'slug' => 'sales.update', 'module' => 'sales', 'action' => 'update'],
            ['name' => 'حذف فاتورة أو مرتجع', 'slug' => 'sales.delete', 'module' => 'sales', 'action' => 'delete'],

            ['name' => 'عرض المشتريات', 'slug' => 'purchases.view', 'module' => 'purchases', 'action' => 'view'],
            ['name' => 'إنشاء فاتورة مشتريات', 'slug' => 'purchases.create', 'module' => 'purchases', 'action' => 'create'],
            ['name' => 'تعديل وتأكيد وصرف', 'slug' => 'purchases.update', 'module' => 'purchases', 'action' => 'update'],
            ['name' => 'حذف فاتورة أو مرتجع', 'slug' => 'purchases.delete', 'module' => 'purchases', 'action' => 'delete'],

            ['name' => 'عرض الحسابات والقيود', 'slug' => 'accounting.view', 'module' => 'accounting', 'action' => 'view'],
            ['name' => 'إضافة حساب أو قيد', 'slug' => 'accounting.create', 'module' => 'accounting', 'action' => 'create'],
            ['name' => 'تعديل حساب', 'slug' => 'accounting.update', 'module' => 'accounting', 'action' => 'update'],
            ['name' => 'حذف حساب', 'slug' => 'accounting.delete', 'module' => 'accounting', 'action' => 'delete'],

            ['name' => 'عرض الخزينة', 'slug' => 'treasury.view', 'module' => 'treasury', 'action' => 'view'],
            ['name' => 'تحصيل وصرف وتحويل', 'slug' => 'treasury.create', 'module' => 'treasury', 'action' => 'create'],

            ['name' => 'عرض المصروفات والإيرادات', 'slug' => 'expenses.view', 'module' => 'expenses', 'action' => 'view'],
            ['name' => 'إضافة عملية أو تصنيف', 'slug' => 'expenses.create', 'module' => 'expenses', 'action' => 'create'],
            ['name' => 'تعديل عملية', 'slug' => 'expenses.update', 'module' => 'expenses', 'action' => 'update'],
            ['name' => 'حذف عملية', 'slug' => 'expenses.delete', 'module' => 'expenses', 'action' => 'delete'],

            ['name' => 'عرض التقارير', 'slug' => 'reports.view', 'module' => 'reports', 'action' => 'view'],

            ['name' => 'عرض المستخدمين والأدوار والسجل', 'slug' => 'users.view', 'module' => 'users', 'action' => 'view'],
            ['name' => 'إضافة مستخدم أو دور', 'slug' => 'users.create', 'module' => 'users', 'action' => 'create'],
            ['name' => 'تعديل مستخدم أو دور', 'slug' => 'users.update', 'module' => 'users', 'action' => 'update'],
            ['name' => 'حذف مستخدم أو دور', 'slug' => 'users.delete', 'module' => 'users', 'action' => 'delete'],

            ['name' => 'إدارة إعدادات النظام', 'slug' => 'settings.update', 'module' => 'settings', 'action' => 'update'],
        ];
    }

    public static function actions(): array
    {
        return ['view' => 'عرض', 'create' => 'إضافة', 'update' => 'تعديل', 'delete' => 'حذف'];
    }

    public static function icons(): array
    {
        return [
            'dashboard' => 'fe-home',
            'customers' => 'fe-users',
            'suppliers' => 'fe-truck',
            'products' => 'fe-box',
            'inventory' => 'fe-layers',
            'pos' => 'fe-monitor',
            'sales' => 'fe-shopping-cart',
            'purchases' => 'fe-file-text',
            'accounting' => 'fe-book-open',
            'treasury' => 'fe-credit-card',
            'expenses' => 'fe-minus-circle',
            'reports' => 'fe-bar-chart-2',
            'users' => 'fe-user-check',
            'settings' => 'fe-settings',
        ];
    }

    public static function grouped(Collection $permissions): Collection
    {
        $grouped = $permissions->groupBy('module');
        $order = array_flip(array_keys(self::actions()));

        return collect(self::modules())
            ->map(fn ($label, $module) => ($grouped->get($module) ?? collect())
                ->sortBy(fn ($permission) => $order[$permission->action] ?? 9)
                ->values())
            ->filter(fn ($items) => $items->isNotEmpty());
    }

    /**
     * Non-admins may only grant or revoke permissions they hold themselves;
     * anything else already assigned is kept untouched.
     */
    public static function restrictToActor(array $current, array $submitted, ?User $actor): array
    {
        $submitted = array_map('intval', $submitted);
        if ($actor?->isAdmin()) {
            return array_values(array_unique($submitted));
        }

        $grantable = $actor ? $actor->permissionIds() : [];
        $kept = array_diff(array_map('intval', $current), $grantable);

        return array_values(array_unique(array_merge($kept, array_intersect($submitted, $grantable))));
    }

    public static function canAssignRole(?User $actor, ?Role $role): bool
    {
        if (! $role || $actor?->isAdmin()) {
            return true;
        }
        if (! $actor || $role->slug === 'admin') {
            return false;
        }

        return $role->permissions()->pluck('permissions.id')->diff($actor->permissionIds())->isEmpty();
    }

    public static function sync(): void
    {
        foreach (self::all() as $permission) {
            $existing = Permission::query()->where('slug', $permission['slug'])->first();
            if ($existing) {
                $existing->fill(collect($permission)->except('name')->all())->save();
                continue;
            }
            Permission::query()->create($permission);
        }

        $admin = Role::query()->where('slug', 'admin')->first();
        if ($admin) {
            $admin->permissions()->sync(Permission::query()->pluck('id'));
        }

        $inherit = [
            'sales.view' => ['pos.view'],
            'sales.create' => ['pos.create'],
        ];
        foreach ($inherit as $from => $slugs) {
            $fromId = Permission::query()->where('slug', $from)->value('id');
            $toIds = Permission::query()->whereIn('slug', $slugs)->pluck('id');
            if (! $fromId || $toIds->isEmpty()) {
                continue;
            }
            Role::query()
                ->whereHas('permissions', fn ($query) => $query->where('permissions.id', $fromId))
                ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($toIds));
        }
    }
}
