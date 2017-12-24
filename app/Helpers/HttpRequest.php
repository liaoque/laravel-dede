<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/11
 * Time: 7:16
 */

namespace App\Helpers;


class HttpRequest
{
    public $url = "";
    public $contentType = "";
    public $httpCode = "";
    public $headerSize = "";
    public $requestSize = "";
    public $filetime = "";
    public $connectTime = "";
    public $sslVerifyResult = "";
    public $redirectCount = "";
    public $totalTime = "";
    public $namelookupTime = "";
    public $pretransferTime = "";
    public $sizeUpload = "";
    public $sizeDownload = "";
    public $speedDownload = "";
    public $speedUpload = "";
    public $downloadContentLength = "";
    public $uploadContentLength = "";
    public $starttransferTime = "";
    public $redirectTime = "";
    public $redirectUrl = "";
    public $primaryIp = "";
    public $certinfo = [];
    public $primaryPort = 80;
    public $localIp = '';
    public $localPort = '';
    public $data;

    public static function get($url, $params = false, $https = 0)
    {

        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }


        if ($params) {
            if (is_array($params)) {
                $params = http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }

        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        $obj = new self();
        foreach ($httpInfo as $key => $value) {
            $key = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
                return strtoupper($matches[2]);
            }, $key);
            $obj->$key = $value;
        }
        $obj->data = $response;
        curl_close($ch);
        return $obj;
    }

    public static function post($url, $params = false, $https = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_exec($ch);
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    public function getContentType(){
        $itype = $this->contentType;
        if($itype=='image/gif')
        {
            $itype = "gif";
        }
        else if($itype=='image/png')
        {
            $itype = "png";
        }
        else
        {
            $itype = 'jpg';
        }
        return $itype;
    }

    public function getBody(){
        return $this->data;
    }

}