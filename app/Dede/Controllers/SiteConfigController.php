<?php

namespace App\Dede\Controllers;

use App\Arctype;
use App\CfgConfig;
use App\ChannelType;
use App\Arcrank;
use App\SysEnum;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SiteConfigController extends Controller
{
    //
    public function index()
    {
        $sysConfig = CfgConfig::sysConfig();
        return view('admin.site.config', [
            'sysConfig' => $sysConfig->toJson(),
        ]);
    }


}
