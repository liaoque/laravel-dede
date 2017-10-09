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


class CatalogController extends Controller
{
    //
    public function index()
    {

//        DB::table(new Expression('xxx'))->delete();dd();
//        $channelTypeList = ChannelType::getShowAll();
        $arrayOne = Arctype::listAllTypeArrayOne(Arctype::listAllTypeArray());
        $listNav = Arctype::ListAllType();
        $list = [];
        foreach ($arrayOne as $v) {
            $list[] = [
                '_typename' => $v->_typename,
                'id' => $v->id,
                'channeltype' => $v->channeltype,
            ];
        }
//        dd(json_encode($arrayOne));
        return view('admin.catalog.index', [
            'navListArrayOne' => $list,
            'listNav' => $listNav
        ]);
    }

    public function add(Arctype $parentArctype = null)
    {

        $arctype = Arctype::defalutArctype();
//        dd($parentArctype);
        if (!$parentArctype) {
            $parentArctype = clone $arctype;
            $sysConfig = CfgConfig::sysConfig();
            $parentArctype->typedir = '{cmspath}' . $sysConfig->cfg_arcdir;
        }
        $arctype->typedir = preg_replace("/\/{1,}/", '/', $arctype->typedir);
        $channelTypeList = ChannelType::getShowAll();
        $list = [];
        foreach ($channelTypeList as $k => $v) {
            $list[] = $v->typename . '(' . $v->nid . ')';
        }
        return view('admin.catalog.add', [
            'action' => url()->current(),
            'channelTypeList' => $channelTypeList,
            'channelTypeListString' => implode('、', $list),
            'referpathList' => Arctype::$referpathList,
            'isdefaultList' => Arctype::$isdefaultList,
            'ispartList' => Arctype::$ispartList,
            'crossList' => Arctype::$crossList,
            'sysEnumInfoTypeList' => SysEnum::getInfoTypeAll(),
            'corankList' => Arcrank::getShowAll(),
            'arctype' => $arctype,
            'parentArctype' => $parentArctype,
            'sysConfig' => $sysConfig,
        ]);
    }

    public function create(Request $request)
    {
        $sysConfig = CfgConfig::sysConfig();
        if ($request->isMethod('post')) {
            $rule = [
                'typename' => 'required|string|between:1,20',
                'typedir' => 'string|nullable|between:1,100',
                'sortrank' => 'numeric',
                'isdefault' => 'digits_between:0,1',
                'issend' => 'digits_between:0,1',
                'channeltype' => 'numeric|exists:channeltype,id',
                'corank' => 'numeric|exists:arcrank,rank',
                'moresite' => 'digits_between:0,1',
                'cross' => 'digits_between:0,2',
                'ispart' => 'digits_between:0,2',
                'ishidden' => 'digits_between:0,1',
                'topid' => 'numeric',
                'reid' => 'numeric',
            ];
            if ($request->post('reid', '0') == 0 && $request->post('moresite', '0') == 1) {
                $rule['siteurl'] = 'required|url';
            }
            $this->validate($request, $rule);
            if (preg_match("/" . $sysConfig->cfg_basehost . "/i", $request->post('siteurl'))) {
                return \Redirect::back()->withErrors("你绑定的二级域名与当前站点是同一个域，不需要绑定！");
            }
            $result = Arctype::createNewArctype($request);
            if (!$result) {
                return \Redirect::back()->withErrors("保存目录数据时失败，请检查你的输入资料是否存在问题！");
            }
            return redirect(route('admin.catalog'));
        }
        return redirect(route('admin.catalog.create'));
    }

    public function edit(Arctype $arctype)
    {
//        dd($arctype);
        $sysConfig = CfgConfig::sysConfig();
        $parentArctype = $arctype->topid ? Arctype::where('id', $arctype->topid)->first() : Arctype::defalutArctype();
        $arctype->typedir = preg_replace("/\/{1,}/", '/', $arctype->typedir);
        $channelTypeList = ChannelType::getShowAll();
        $list = [];
        foreach ($channelTypeList as $k => $v) {
            $list[] = $v->typename . '(' . $v->nid . ')';
        }
//        dd($parentArctype);
        return view('admin.catalog.edit', [
            'action' => url()->current(),
            'channelTypeList' => $channelTypeList,
            'channelTypeListString' => implode('、', $list),
            'referpathList' => Arctype::$referpathList,
            'isdefaultList' => Arctype::$isdefaultList,
            'ispartList' => Arctype::$ispartList,
            'crossList' => Arctype::$crossList,
            'sysEnumInfoTypeList' => SysEnum::getInfoTypeAll(),
            'corankList' => Arcrank::getShowAll(),
            'arctype' => $arctype,
            'parentArctype' => $parentArctype,
            'sysConfig' => $sysConfig,
        ]);
    }

    public function update(Arctype $arctype, Request $request)
    {
        if ($request->isMethod('post')) {
            $rule = [
                'typename' => 'required|string|between:1,20',
                'typedir' => 'string|nullable|between:1,100',
                'sortrank' => 'numeric',
                'isdefault' => 'digits_between:0,1',
                'issend' => 'digits_between:0,1',
                'channeltype' => 'numeric|exists:channeltype,id',
                'corank' => 'numeric|exists:arcrank,rank',
                'moresite' => 'digits_between:0,1',
                'cross' => 'digits_between:0,2',
                'ispart' => 'digits_between:0,2',
                'ishidden' => 'digits_between:0,1'
            ];
            $this->validate($request, $rule);
            $result = $arctype->updateArctype($request);
            if (!$result) {
                return \Redirect::back()->withErrors("保存目录数据时失败，请检查你的输入资料是否存在问题！");
            }
            return redirect(route('admin.catalog'));
        }
        return redirect(route('admin.catalog.update'));
    }


    public function move(Request $request)
    {
        $rule = [
            'id' => 'required|numeric',
            'movetype' => 'required|numeric'
        ];
        $this->validate($request, $rule);
        $status = 200;
        if ($request->post('id') == $request->post('movetype')) {
            $status = 422;
            $mes = 'error';
            $error = [
                'id' => '移对对象和目标位置相同！'
            ];
        } else {
            $arctype = Arctype::where('id', $request->post('id'));
            $target = Arctype::where('id', $request->post('movetype'));
            if ($arctype->channeltype != $target->channeltype) {
                $status = 422;
                $mes = 'error';
                $error = [
                    'id' => '不能从父类移动到子类'
                ];
            } else {
                $arctype->reid = $target->id;
                if ($arctype->save()) {
                    $mes = 'success';
                    $error = [];
                } else {
                    $mes = 'error';
                    $error = [
                        'id' => '数据库保存失败'
                    ];
                }
            }
        }
        return response()
            ->json(['message' => $mes, 'errors' => $error], $status);
    }

    public function delete(Request $request)
    {
        $rule = [
            'id' => 'required|numeric|exists:arctype'
        ];
        $this->validate($request, $rule);
        $status = 200;

        Arctype::deleteArctype($request->post('id'));
        return response()
            ->json(['message' => 'success', 'errors' => []], $status);
    }

    public function sotrRank(){
        $result = \request()->all();
        $all = Arctype::getAllWithCache();
        foreach ($all as  $id => $value){
            $sort = intval($result['sortrank'.$id]);
            Arctype::where('id', $id)->update([
                'sortrank' => $sort
            ]);
        }
        return response()
            ->json(['message' => 'success', 'errors' => []], 200);
    }

}
