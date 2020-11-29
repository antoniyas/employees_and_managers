<?php

use Illuminate\Database\Seeder;
use App\Role;
use App\RoleUser;

class UsersSeeder extends Seeder
{
    private function insertUsersWithRole($count, $type)
    {
        $role = App\Role::where('name', $type)->first();
        return factory('App\User', $count)->create([
            'role_id' => $role->id
        ]);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create roles
        $roles = [
            ['name' => 'manager'],
            ['name' => 'employee']
        ];
        Role::insert($roles);
        $this->insertUsersWithRole(5, 'manager');
        $employees = $this->insertUsersWithRole(4995, 'employee');
        foreach ($employees as $employee) {
            $randomNum = rand(1, 4);
            $randomManagers = [
                \App\User::find($randomNum)->id,
                \App\User::find($randomNum + 1)->id
            ];
            $employee->managers()->attach($randomManagers);
        }
    }
}
