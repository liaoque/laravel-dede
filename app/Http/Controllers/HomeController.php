<?php

namespace App\Http\Controllers;

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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        app()->scws->send_text('函数返回项目根目录的完整路径。你还可以使用 base_path 函数生成指定文件相对于项目根目录的完整路径');
//        dd( app()->scws   );
//        dd( app());
        return view('home');
    }
}
