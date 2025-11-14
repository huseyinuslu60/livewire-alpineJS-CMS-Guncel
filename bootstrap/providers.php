<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\LivewireServiceProvider::class,
    Modules\Articles\Providers\ArticlesServiceProvider::class,
    Modules\User\Providers\UserServiceProvider::class,
    Modules\Roles\Providers\RolesServiceProvider::class,
    Modules\Categories\Providers\CategoriesServiceProvider::class,
    Modules\Lastminutes\Providers\LastminutesServiceProvider::class,
];
