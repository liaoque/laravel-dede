<?php

namespace App;

use App\Helpers\HttpRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class Upload extends Model
{
    //

    /**
     * 拉取远程图片
     */
    public static function remoteImage(Request $request)
    {
        if ($request->post('remote', 0) == 1) {
            //处理远程图片
            $body = $request->post('body');
            $img_array = [];
            preg_match_all("/src=[\"|'|\s]{0,}(http:\/\/([^>]*)\.(gif|jpg|png))/isU", $body, $img_array);
            $img_array = array_unique($img_array[1]);
//            $imgUrl = $cfg_uploaddir.'/'.MyDate("ymd", time());
            $imgPath = config('uedit.imagePathFormat');
//            if(!is_dir($imgPath.'/'))
//            {
//                MkdirAll($imgPath, $GLOBALS['cfg_dir_purview']);
//                CloseFtp();
//            }

            $basehost = $request->root();
            $sysConfig = CfgConfig::sysConfig();
            $cfg_basehost = $sysConfig->cfg_basehost;
            foreach ($img_array as $key => $value) {
                if (preg_match("#" . $basehost . "#i", $value)) {
                    continue;
                }
                //$cfg_basehost 这个变量有问题， 以后处理
                if ($cfg_basehost != $basehost && preg_match("#" . $cfg_basehost . "#i", $value)) {
                    continue;
                }
                if (!preg_match("#^http:\/\/#i", $value)) {
                    continue;
                }
                if(!$data = HttpRequest::get($value)){
                    continue;
                }

                $storage = Storage::disk('public');
                $d = date('Ymd');
                $fileName = $d . '/' . Str::random(40) . '.'.$data->getContentType();
                $result = $storage->put($fileName, $data->getBody());
                if($result){
                    $imginfos = GetImageSize($rndFileName, $info);
                    $fsize = filesize($rndFileName);
                    //保存图片附件信息
                    $imageUpload = new self();
                    $imageUpload->arcid = '';
                    $imageUpload->title = '';
                    $imageUpload->url = '';
                    $imageUpload->mediatype = '';
                    $imageUpload->width = '';
                    $imageUpload->height = '';
                    $imageUpload->playtime = '';
                    $imageUpload->filesize = '';
                    $imageUpload->uptime = '';
                    $imageUpload->mid = '';
                    $imageUpload->save();
//                    $inquery = "INSERT INTO `#@__uploads`(arcid,title,url,mediatype,width,height,playtime,filesize,uptime,mid)
//			VALUES ('{$arcID}','$rndFileName','$fileurl','1','{$imginfos[0]}','$imginfos[1]','0','$fsize','" . time() . "','" . $cuserLogin->getUserID() . "'); ";
//                    $dsql->ExecuteNoneQuery($inquery);
//                    $fid = $dsql->GetLastID();
//                    AddMyAddon($fid, $fileurl);
//                    if ($cfg_multi_site == 'Y') {
//                        $fileurl = $cfg_basehost . $fileurl;
//                    }
//                    $body = str_replace($value, $fileurl, $body);
//                    @WaterImg($rndFileName, 'down');
                }
//
//
//                $htd->OpenUrl($value);
//                $itype = $htd->GetHead("content-type");
//                $itype = substr($value, -4, 4);
//                if (!preg_match("#\.(jpg|gif|png)#i", $itype)) {
//                    if ($itype == 'image/gif') {
//                        $itype = ".gif";
//                    } else if ($itype == 'image/png') {
//                        $itype = ".png";
//                    } else {
//                        $itype = '.jpg';
//                    }
//                }
//                $milliSecondN = dd2char($milliSecond . mt_rand(1000, 8000));
//                $value = trim($value);
//                $rndFileName = $imgPath . '/' . $milliSecondN . '-' . $key . $itype;
//                $fileurl = $imgUrl . '/' . $milliSecondN . '-' . $key . $itype;
//
//                $rs = $htd->SaveToBin($rndFileName);
//                if ($rs) {
//                    $info = '';
//                    $imginfos = GetImageSize($rndFileName, $info);
//                    $fsize = filesize($rndFileName);
//                    //保存图片附件信息
//                    $inquery = "INSERT INTO `#@__uploads`(arcid,title,url,mediatype,width,height,playtime,filesize,uptime,mid)
//			VALUES ('{$arcID}','$rndFileName','$fileurl','1','{$imginfos[0]}','$imginfos[1]','0','$fsize','" . time() . "','" . $cuserLogin->getUserID() . "'); ";
//                    $dsql->ExecuteNoneQuery($inquery);
//                    $fid = $dsql->GetLastID();
//                    AddMyAddon($fid, $fileurl);
//                    if ($cfg_multi_site == 'Y') {
//                        $fileurl = $cfg_basehost . $fileurl;
//                    }
//                    $body = str_replace($value, $fileurl, $body);
//                    @WaterImg($rndFileName, 'down');
//                }
            }


        }
    }


}
