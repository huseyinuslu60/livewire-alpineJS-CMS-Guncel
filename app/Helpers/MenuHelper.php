<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class MenuHelper
{
    /**
     * Cache duration in minutes
     */
    private const CACHE_DURATION = 10;

    /**
     * Admin menü yapısını getir
     */
    public static function getAdminMenu()
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        // Generate cache key based on user ID and roles
        $cacheKey = self::getCacheKey($user);

        // Try to get from cache
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($user) {
            return self::buildMenu($user);
        });
    }

    /**
     * Generate cache key for user
     */
    private static function getCacheKey($user): string
    {
        $roles = $user->roles->pluck('name')->sort()->implode(',');

        return "admin_menu:user_{$user->id}:roles_{$roles}";
    }

    /**
     * Build menu structure
     */
    private static function buildMenu($user): array
    {

        // Kullanıcının erişebileceği menü item'larını filtrele
        $menuItems = \App\Models\MenuItem::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $menu = [];

        foreach ($menuItems as $item) {
            // Permission kontrolü
            if (! self::canAccessMenuItem($user, $item)) {
                continue;
            }

            $menuItem = [
                'name' => $item->name,
                'type' => $item->type,
                'icon' => $item->icon,
                'title' => $item->title,
                'roles' => $item->roles ?? [], // Model zaten array olarak cast ediyor
                'permission' => $item->permission,
                'active' => $item->active_pattern,
            ];

            // Modül kontrolü
            if ($item->module) {
                $menuItem['module'] = $item->module;
            }

            // Route kontrolü
            if ($item->route) {
                $menuItem['route'] = $item->route;
            }

            // Alt menüleri çek
            $submenus = \App\Models\MenuItem::where('parent_id', $item->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            if ($submenus->count() > 0) {
                $menuItem['submenu'] = [];
                foreach ($submenus as $submenu) {
                    // Alt menü için de permission kontrolü
                    if (! self::canAccessMenuItem($user, $submenu)) {
                        continue;
                    }

                    $submenuItem = [
                        'name' => $submenu->name,
                        'title' => $submenu->title,
                        'active' => $submenu->active_pattern,
                        'type' => $submenu->type,
                    ];

                    if ($submenu->route) {
                        $submenuItem['route'] = $submenu->route;
                    } elseif ($submenu->name === 'newsletters_templates') {
                        $submenuItem['url'] = '/newsletters/templates';
                    }

                    if ($submenu->roles) {
                        $submenuItem['roles'] = $submenu->roles; // Model zaten array olarak cast ediyor
                    }

                    if ($submenu->permission) {
                        $submenuItem['permission'] = $submenu->permission;
                    }

                    // Nested submenu kontrolü - eğer bu submenu'nun da alt menüleri varsa
                    if ($submenu->type === 'submenu') {
                        $nestedSubmenus = \App\Models\MenuItem::where('parent_id', $submenu->id)
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->get();

                        if ($nestedSubmenus->count() > 0) {
                            $submenuItem['submenu'] = [];
                            foreach ($nestedSubmenus as $nestedSubmenu) {
                                $nestedItem = [
                                    'name' => $nestedSubmenu->name,
                                    'title' => $nestedSubmenu->title,
                                    'active' => $nestedSubmenu->active_pattern,
                                ];

                                if ($nestedSubmenu->route) {
                                    $nestedItem['route'] = $nestedSubmenu->route;
                                }

                                if ($nestedSubmenu->roles) {
                                    $nestedItem['roles'] = $nestedSubmenu->roles; // Model zaten array olarak cast ediyor
                                }

                                if ($nestedSubmenu->permission) {
                                    $nestedItem['permission'] = $nestedSubmenu->permission;
                                }

                                $submenuItem['submenu'][] = $nestedItem;
                            }
                        }
                    }

                    $menuItem['submenu'][] = $submenuItem;
                }
            }

            $menu[] = $menuItem;
        }

        // Modül kontrolü yap
        $menu = self::filterMenuByModule($menu);

        // Eğer veritabanında menü yoksa, boş array döndür
        if (empty($menu)) {
            return [];
        }

        // Kullanıcı rolüne ve izinlerine göre menüyü filtrele
        return self::filterMenuByRole($menu);
    }

    /**
     * Clear menu cache for a user
     */
    public static function clearCacheForUser($user): void
    {
        $cacheKey = self::getCacheKey($user);
        Cache::forget($cacheKey);
    }

    /**
     * Clear menu cache for all users (when menu items change)
     */
    public static function clearAllCache(): void
    {
        // Clear all menu caches by pattern
        Cache::flush(); // In production, use a more targeted approach with tags if available
    }

    /**
     * Menüyü modül durumuna göre filtrele
     */
    private static function filterMenuByModule($menu)
    {
        $filteredMenu = [];

        foreach ($menu as $item) {
            // Modül kontrolü
            if (isset($item['module'])) {
                $module = \App\Models\Module::where('name', $item['module'])->first();
                $isActive = $module ? $module->is_active : false;

                if (! $isActive) {
                    continue;
                }
            }

            $filteredMenu[] = $item;
        }

        return $filteredMenu;
    }

    /**
     * Menüyü kullanıcı rolüne ve izinlerine göre filtrele
     */
    private static function filterMenuByRole($menu)
    {
        $user = auth()->user();
        if (! $user) {
            return []; // Giriş yapmamış kullanıcı için boş menü
        }

        $filteredMenu = [];

        foreach ($menu as $item) {
            // Süper Admin kullanıcısı için tüm menüleri göster
            if ($user->hasRole('super_admin')) {
                // Alt menü varsa filtrele
                if (isset($item['submenu'])) {
                    $item['submenu'] = self::filterSubmenuByRole($item['submenu'], $user);
                }
                $filteredMenu[] = $item;

                continue;
            }

            // Admin kullanıcısı için modül yönetimi hariç tüm menüleri göster
            if ($user->hasRole('admin')) {
                // Modül yönetimi menüsünü atla
                if (isset($item['name']) && $item['name'] === 'modules_management') {
                    continue;
                }
                // Alt menü varsa filtrele
                if (isset($item['submenu'])) {
                    $item['submenu'] = self::filterSubmenuByRole($item['submenu'], $user);
                }
                $filteredMenu[] = $item;

                continue;
            }

            // Ana menü için izin kontrolü
            if (! self::canAccessMenuItem($user, $item)) {
                continue;
            }

            // Alt menü varsa filtrele
            if (isset($item['submenu'])) {
                $item['submenu'] = self::filterSubmenuByRole($item['submenu'], $user);

                // Alt menü boşsa ana menüyü de ekleme
                if (empty($item['submenu'])) {
                    continue;
                }
            }

            $filteredMenu[] = $item;
        }

        return $filteredMenu;
    }

    /**
     * Alt menüyü rol ve yetki kontrolü ile filtrele
     */
    private static function filterSubmenuByRole($submenu, $user)
    {
        $filtered = [];

        foreach ($submenu as $item) {
            if (! self::canAccessMenuItem($user, $item)) {
                continue;
            }

            $filtered[] = $item;
        }

        return $filtered;
    }

    /**
     * Menü item'ına erişim yetkisi kontrolü
     * Permission-based kontrol: Önce permission kontrolü yapılır, yoksa roles kontrolü yapılır
     */
    private static function canAccessMenuItem($user, $item)
    {
        // 1. Permission kontrolü (öncelikli) - Eğer permission belirtilmişse, kullanıcının o permission'a sahip olması gerekir
        if (isset($item['permission']) && ! empty($item['permission'])) {
            if (! $user->can($item['permission'])) {
                return false;
            }
        }

        // 2. Roles kontrolü - Eğer permission yoksa veya permission kontrolü geçtiyse, roles kontrolü yapılır
        if (isset($item['roles']) && ! empty($item['roles'])) {
            if (! self::hasRole($user, $item['roles'])) {
                return false;
            }
        }

        // 3. Modül kontrolü - Modül aktif değilse erişim yok
        if (isset($item['module']) && ! SystemHelper::isModuleActive($item['module'])) {
            return false;
        }

        return true;
    }

    /**
     * Kullanıcının belirtilen rollerden birine sahip olup olmadığını kontrol et
     */
    private static function hasRole($user, $roles)
    {
        // Eski veriler string olarak saklanmış olabilir (backward compatibility)
        if (is_string($roles)) {
            $decoded = json_decode($roles, true);
            $roles = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [$roles];
        }

        if (! is_array($roles) || empty($roles)) {
            return false;
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Menü item'ının aktif olup olmadığını kontrol et
     */
    public static function isActive($item)
    {
        if (isset($item['active'])) {
            return request()->routeIs($item['active']);
        }

        return false;
    }

    /**
     * Menü item'ının aktif pattern'ini kontrol et
     */
    public static function isActivePattern($activePattern)
    {
        if (empty($activePattern)) {
            return false;
        }

        // Pattern'leri | ile ayır ve her birini kontrol et
        $patterns = explode('|', $activePattern);
        foreach ($patterns as $pattern) {
            if (request()->routeIs(trim($pattern))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Menü item'ının alt menülerinden herhangi birinin aktif olup olmadığını kontrol et
     */
    public static function hasActiveSubmenu($item)
    {
        if (! isset($item['submenu']) || empty($item['submenu'])) {
            return false;
        }

        foreach ($item['submenu'] as $subItem) {
            // Alt menü item'ının kendisi aktif mi?
            if (isset($subItem['active']) && self::isActivePattern($subItem['active'])) {
                return true;
            }

            // Nested alt menüler varsa onları da kontrol et
            if (isset($subItem['submenu']) && ! empty($subItem['submenu'])) {
                foreach ($subItem['submenu'] as $nestedItem) {
                    if (isset($nestedItem['active']) && self::isActivePattern($nestedItem['active'])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Menü item'ının CSS sınıflarını getir
     */
    public static function getItemClasses($item)
    {
        $classes = [];

        if ($item['type'] === 'single') {
            $classes[] = 'pcoded-submenu';
        } else {
            $classes[] = 'pcoded-hasmenu';
        }

        if (self::isActive($item)) {
            $classes[] = 'active pcoded-trigger';
        }

        return implode(' ', $classes);
    }
}
