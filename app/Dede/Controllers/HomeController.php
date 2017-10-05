<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/23
 * Time: 12:27
 */

namespace App\Dede\Controllers;


class HomeController extends Controller
{

    public function index(){
        return view('admin.home.index');
    }



}