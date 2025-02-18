<?php

namespace App\Console\Commands;

use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Models\AdminUser;
use App\Models\VueRouter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AdminInitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:init';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化基础路由配置，超级管理员角色和权限';
    public static $initConfirmTip = '初始化操作，会清空路由、管理员、角色和权限表，以及相关关联表数据。是否确认？';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->confirm(static::$initConfirmTip)) {
            $this->createVueRouters();
            $this->createUserRolePermission();
            $this->info('初始化完成，管理员为：admin，密码为：000000');
        } else {
            return 1;
        }
    }

    protected function createVueRouters()
    {
        $inserts = [
            [1, 0, '首页', 'index', 0, null, 1],

            [2, 0, '路由配置', null, 1, null, 1],
            [3, 2, '所有路由', 'vue-routers', 2, null, 1],
            [4, 2, '添加路由', 'vue-routers/create', 3, null, 1],
            [5, 2, '编辑路由', 'vue-routers/:id(\\d+)/edit', 4, null, 0],

            [6, 0, '管理员管理', null, 5, null, 1],
            [7, 6, '管理员列表', 'admin-users', 6, null, 1],
            [8, 6, '添加管理员', 'admin-users/create', 7, null, 1],
            [9, 6, '编辑管理员', 'admin-users/:id(\\d+)/edit', 8, null, 0],

            [10, 0, '角色管理', null, 9, null, 1],
            [11, 10, '角色列表', 'admin-roles', 10, null, 1],
            [12, 10, '添加角色', 'admin-roles/create', 11, null, 1],
            [13, 10, '编辑角色', 'admin-roles/:id(\\d+)/edit', 12, null, 0],

            [14, 0, '权限管理', null, 13, null, 1],
            [15, 14, '权限列表', 'admin-permissions', 14, null, 1],
            [16, 14, '添加权限', 'admin-permissions/create', 15, null, 1],
            [17, 14, '编辑权限', 'admin-permissions/:id(\\d+)/edit', 16, null, 0],

            [18, 0, '文件管理', 'system-media', 17, null, 1],
        ];

        $inserts = collect($inserts)->map(function ($i) {
            $i = array_combine(['id', 'parent_id', 'title', 'path', 'order', 'icon', 'menu'], $i);
            return array_merge($i, [
                'cache' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'permission' => null,
            ]);
        })->all();

        VueRouter::truncate();
        VueRouter::insert($inserts);
    }

    protected function createUserRolePermission()
    {
        AdminUser::truncate();
        AdminRole::truncate();
        AdminPermission::truncate();

        collect(['admin_role_permission', 'admin_user_permission', 'admin_user_role', 'vue_router_role'])
            ->each(function ($table) {
                DB::table($table)->truncate();
            });

        $user = AdminUser::create([
            'name' => '管理员',
            'username' => 'admin',
            'password' => bcrypt('000000'),
        ]);

        $user->roles()->create([
            'name' => '超级管理员',
            'slug' => 'administrator',
        ]);

        AdminRole::first()
            ->permissions()
            ->create([
                'name' => '所有权限',
                'slug' => 'pass-all',
                'http_path' => '*',
            ]);
    }
}
