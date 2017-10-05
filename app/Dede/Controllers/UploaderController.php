<?php

namespace App\Dede\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploaderController extends Controller
{
    /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        //
        switch ($request->get('action')) {
            case 'uploadimage':
                $file = $request->file(config('uedit.imageFieldName'));
                $state = '文件类型错误';
                if (in_array($file->guessClientExtension(), config('uedit.imageAllowFiles'))) {
                    $d = date('Ymd');
                    $path = $file->store($d, config('uedit.imagePathFormat'));
                    $state = 'SUCCESS';
                }
                $data = [
                    "state" => $state,
                    "url" => '/storage/' . $path,
                    "title" => str_replace($d . '/', '', $path),
                    "original" => $file->getClientOriginalName(),
                    "type" => $file->guessClientExtension(),
                    "size" => $file->getSize()
                ];
                break;
            case 'uploadscrawl':
            case 'catchimage':
            case 'uploadvideo':
            case 'uploadfile':
                $data = [];
        }
        return $data;
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function action(Request $request)
    {
        switch ($request->get('action')) {
            case 'config':
                $data = config('uedit');
                break;
            case 'listimage':
                $data = Storage::allFiles(config('uedit.imagePathFormat'));
                if (empty($data)) {
                    $data = [
                        "state" => "no match file",
                        "list" => [],
                        "start" => 0,
                        "total" => 0
                    ];
                } else {
                    $allow = config('uedit.imageAllowFiles');;
                    $allow = implode('|', $allow);
                    $allow = str_replace('.', '', $allow);
                    $data = array_filter($data, function ($value) use ($allow) {
                        return preg_match('/(' . $allow . ')$/', $value);
                    });
                    $data = array_map(function ($value) {
                        return [
                            'url' => preg_replace('/^public/', '/storage', $value),
                            'mtime' => filemtime(Storage::disk()->path($value))
                        ];
                    }, $data);
                    $data = [
                        "state" => "SUCCESS",
                        "list" => array_values($data),
                        "start" => 0,
                        "total" => count($data)
                    ];
                }
                break;
            default:
                $data = [
                    "state" => "no match file",
                    "list" => [],
                    "start" => 0,
                    "total" => 0
                ];
                break;
        }
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
