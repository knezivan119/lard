<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // $roles = [ 'admin', 'retailer', 'factory' ];
        // $permissions = [ 'view', 'edit' ];
        // $models = [ 'users', 'customers', 'orders' ];
        //
        $role = Role::create( [ 'name' => 'root' ] );

        $rules = [
            'admin' => [
                'view' => [ 'users', /* 'customers' */ ],
                'edit' => [ 'users', /* 'customers' */ ],
            ],
            'retailer' => [
                'view' => [],
                'edit' => [],
            //     'view' => [ 'customers' ],
            //     'edit' => [ 'customers' ],
            ],
            // 'factory' => [
            //     'view' => [ 'orders' ],
            //     'edit' => [],
            // ],
        ];

        // create permissions
        foreach ( $rules as $role => $permissions  ) {
            $role = Role::firstOrCreate( [ 'name' => $role ] );
            foreach ( $permissions as $permis => $models ) {
                foreach ( $models as $model ) {
                    $permission = Permission::firstOrcreate( [ 'name' => implode(' ', [ $permis, $model ] ) ] );
                    $role->givePermissionTo( $permission->name );
                }
            }
        }


        // this can be done as separate statements
        // $role = Role::create(['name' => 'writer']);
        // $role->givePermissionTo('edit articles');

        // or may be done by chaining
        // $role = Role::create(['name' => 'moderator'])
        //     ->givePermissionTo(['publish articles', 'unpublish articles']);

        // $role = Role::create(['name' => 'super-admin']);
        // $role->givePermissionTo(Permission::all());
    }
}