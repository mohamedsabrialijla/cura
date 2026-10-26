<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\Role;
use App\Models\role_has_permissions;
use Illuminate\Support\Facades\DB;

class DashboardMenuPermissions
{
    /**
     * Menu-only permissions (one per dashboard sidebar item).
     *
     * @return array<int, array{name: string, display_name: string, descriptions: string}>
     */
    public static function definitions(): array
    {
        return [
            ['name' => 'menu-admins', 'display_name' => 'Admins', 'descriptions' => 'المشرفين'],
            ['name' => 'menu-roles', 'display_name' => 'Roles', 'descriptions' => 'الأدوار'],
            ['name' => 'menu-users', 'display_name' => 'Users', 'descriptions' => 'المستخدمين'],
            ['name' => 'menu-payment', 'display_name' => 'Payment', 'descriptions' => 'المدفوعات'],
            ['name' => 'menu-contact', 'display_name' => 'Contact Us', 'descriptions' => 'اتصل بنا'],
            ['name' => 'menu-landing-slider', 'display_name' => 'Home Slider Products', 'descriptions' => 'سلايدر الصفحة الرئيسية'],
            ['name' => 'menu-technical-slider', 'display_name' => 'Technical Apparel Slider', 'descriptions' => 'سلايدر الملابس التقنية'],
            ['name' => 'menu-category-strip', 'display_name' => 'Category Image Slider', 'descriptions' => 'سلايدر صور التصنيفات'],
            ['name' => 'menu-brand-slider', 'display_name' => 'Brand Slider', 'descriptions' => 'سلايدر الماركات'],
            ['name' => 'menu-shipping', 'display_name' => 'Shipping', 'descriptions' => 'الشحن'],
            ['name' => 'menu-payment-methods', 'display_name' => 'Payment Methods', 'descriptions' => 'طرق الدفع'],
            ['name' => 'menu-coupons', 'display_name' => 'Coupons', 'descriptions' => 'الكوبونات'],
            ['name' => 'menu-carts', 'display_name' => 'Shopping Carts', 'descriptions' => 'سلات التسوق'],
            ['name' => 'menu-pages', 'display_name' => 'Page List', 'descriptions' => 'قائمة الصفحات'],
            ['name' => 'menu-faq', 'display_name' => 'FAQ', 'descriptions' => 'الأسئلة الشائعة'],
            ['name' => 'menu-settings', 'display_name' => 'Website Settings', 'descriptions' => 'إعدادات الموقع'],
        ];
    }

    public static function names(): array
    {
        return array_column(self::definitions(), 'name');
    }

    public static function sync(bool $assignAllRoles = true): int
    {
        DB::table('role_has_permissions')->delete();
        DB::table('model_has_permissions')->delete();
        Permission::query()->delete();

        $created = 0;

        foreach (self::definitions() as $definition) {
            Permission::create([
                'name' => $definition['name'],
                'display_name' => $definition['display_name'],
                'guard_name' => 'user',
                'parent_id' => null,
                'descriptions' => $definition['descriptions'],
                'admin' => 1,
                'company_id' => null,
            ]);
            $created++;
        }

        if ($assignAllRoles) {
            $permissionIds = Permission::pluck('id');
            $roleIds = Role::pluck('id');

            foreach ($roleIds as $roleId) {
                foreach ($permissionIds as $permissionId) {
                    role_has_permissions::create([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ]);
                }
            }
        }

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));

        return $created;
    }
}
