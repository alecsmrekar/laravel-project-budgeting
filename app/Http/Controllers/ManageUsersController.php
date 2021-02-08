<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ManageUsersController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return $this->generateFormView();
    }

    public function submitUserForm(Request $request) {
        $data = $request->input();
        $users = DB::table('users')->get()->whereNotIn('id', [Auth::user()->id]);
        $all_roles = User::getAllRoles();
        $updated = [];
        foreach ($users as $user) {
            if (isset($data[$user->id]) and $user->role != $data[$user->id] and in_array($data[$user->id], $all_roles)) {
                $new_role = $data[$user->id];
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['role' => $new_role]);
                array_push($updated, $user->name);
            }
        }
        $update_msg = (count($updated) > 0 ? 'Updated users: ' . implode(", ",$updated) : '');
        return $this->generateFormView(['submitted' => True, 'update_msg' => $update_msg]);
    }

    private function generateFormView(Array $args = []){
        $users = DB::table('users')->get()->whereNotIn('id', [Auth::user()->id]);
        $all_roles = User::getAllRoles();
        $view_params = ['users'=> $users, 'roles' => $all_roles] + $args;
        return view('settings')->with($view_params);
    }

}
