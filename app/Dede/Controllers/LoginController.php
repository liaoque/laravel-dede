<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/23
 * Time: 12:27
 */

namespace App\Dede\Controllers;


use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    public function index(){
        return view('admin.login.index');
    }

    public function login(){

        $this->validate(request(), [
            'userName' =>'required|min:2',
            'password' =>'required|min:5|max:20',
        ]);

        $request = request(['userName', 'password']);


        $result =Auth::guard('admin')->attempt([
            'uname' => $request['userName'],
            'password' => $request['password'],
        ]);

        if($result){
            return redirect()->route('admin.index');
        }
        return back()->withErrors("用户名密码错误");
    }

    public function logout(){

    }


}