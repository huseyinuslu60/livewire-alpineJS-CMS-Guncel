<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Kullanıcının belirtilen izne sahip olup olmadığını kontrol et
     */
    public static function can($permission)
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->can($permission);
    }

    /**
     * Kullanıcının belirtilen izinlerden herhangi birine sahip olup olmadığını kontrol et
     */
    public static function canAny($permissions)
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kullanıcının belirtilen izinlerin hepsine sahip olup olmadığını kontrol et
     */
    public static function canAll($permissions)
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (! $user->can($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Kullanıcının belirtilen role sahip olup olmadığını kontrol et
     */
    public static function hasRole($role)
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasRole($role);
    }

    /**
     * Kullanıcının belirtilen rollerden herhangi birine sahip olup olmadığını kontrol et
     */
    public static function hasAnyRole($roles)
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole($roles);
    }

    /**
     * Admin kullanıcısı kontrolü
     */
    public static function isAdmin()
    {
        return self::hasRole('admin');
    }

    /**
     * View dosyalarında kullanım için Blade directive'leri
     */
    public static function registerBladeDirectives()
    {
        \Blade::directive('can', function ($permission) {
            return "<?php if (\\App\\Helpers\\PermissionHelper::can($permission)): ?>";
        });

        \Blade::directive('endcan', function () {
            return '<?php endif; ?>';
        });

        \Blade::directive('cannot', function ($permission) {
            return "<?php if (!\\App\\Helpers\\PermissionHelper::can($permission)): ?>";
        });

        \Blade::directive('endcannot', function () {
            return '<?php endif; ?>';
        });

        \Blade::directive('canany', function ($permissions) {
            return "<?php if (\\App\\Helpers\\PermissionHelper::canAny($permissions)): ?>";
        });

        \Blade::directive('endcanany', function () {
            return '<?php endif; ?>';
        });

        \Blade::directive('canall', function ($permissions) {
            return "<?php if (\\App\\Helpers\\PermissionHelper::canAll($permissions)): ?>";
        });

        \Blade::directive('endcanall', function () {
            return '<?php endif; ?>';
        });
    }
}
