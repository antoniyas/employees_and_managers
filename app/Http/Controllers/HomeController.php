<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function getUsers(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        // Fetch records
        $query = User::orderBy($columnName, $columnSortOrder)
            ->where('users.name', 'like', '%' . $searchValue . '%')
            ->select('users.*')
            ->skip($start)
            ->take($rowperpage);

        $query->when(auth()->user()->role->id === 1, function ($q) {
            return $q
                ->join('employee_manager', 'users.id', 'employee_id')
                ->where('manager_id', auth()->user()->id);
        });

        $query->when(auth()->user()->role->id === 2, function ($q) {
            return $q
                ->join('employee_manager', 'users.id', 'manager_id')
                ->where('employee_id', auth()->user()->id);
        });

        $records = $query->get();

        // Total records
        $totalRecords = $query->count();
        $totalRecordswithFilter = $query->where('name', 'like', '%' . $searchValue . '%')->count();

        $data_arr = array();
        foreach ($records as $record) {
            $firstname = $record->name;
            $lastname = $record->last_name;
            $email = $record->email;

            $data_arr[] = array(
                "name" => $firstname,
                "last_name" => $lastname,
                "email" => $email
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        echo json_encode($response);
        exit;
    }

}
