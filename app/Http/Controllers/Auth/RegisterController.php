<?php

namespace App\Http\Controllers\Auth;

use App\EmployeeManager;
use App\Http\Controllers\Controller;
use App\Mail\RegisterMail;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    private function getEmployeesEmailsOfManagers($employeeID)
    {
        $managerIds = User::join('employee_manager', 'users.id', 'manager_id')
            ->where('employee_id', $employeeID)
            ->with('employees')
            ->get()
            ->pluck('id');
        return User::join('employee_manager', 'users.id', 'employee_id')
            ->whereIn('manager_id', $managerIds)
            ->pluck('email');
    }

    private function getTwoManagerIdsWithSmallestNumberOfEmployees($employeeID)
    {
        $managerIds = User::withCount('employees')
            ->where('role_id', 1)
            ->orderBy('employees_count', 'asc')
            ->take(2)
            ->pluck('id');
        $result = [];
        foreach ($managerIds as $managerId) {
            $result[] = [
                'employee_id' => $employeeID,
                'manager_id' => $managerId
            ];
        }
        return $result;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'last_name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        EmployeeManager::insert($this->getTwoManagerIdsWithSmallestNumberOfEmployees($user->id));
        $emails = $this->getEmployeesEmailsOfManagers($user->id);
        Mail::to($emails)->send(new RegisterMail($data));
        return $user;
    }
}
