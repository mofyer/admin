<?php

namespace App\Http\Middleware;

use App\Contracts\PermissionMiddleware;

class AdminPermission extends PermissionMiddleware
{
    /**
     * @var array 白名单
     */
    protected $excepts = [
        '/auth/login',
        '/auth/logout',
        '/user',
        '/user/edit',
        '/configs/vue-routers',
    ];
}
