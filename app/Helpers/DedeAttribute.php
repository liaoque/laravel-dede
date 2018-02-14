<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/13
 * Time: 18:02
 */

namespace App\Helpers;

/**********************************************
//class DedeAttribute Dede模板标记属性集合
function c____DedeAttribute();
 **********************************************/
class DedeAttribute
{
    var $Count = -1;
    var $Items = ""; //属性元素的集合
    //获得某个属性
    function GetAtt($str)
    {
        if($str=="")
        {
            return "";
        }
        if(isset($this->Items[$str]))
        {
            return $this->Items[$str];
        }
        else
        {
            return "";
        }
    }

    //同上
    function GetAttribute($str)
    {
        return $this->GetAtt($str);
    }

    //判断属性是否存在
    function IsAttribute($str)
    {
        if(isset($this->Items[$str])) return TRUE;
        else return FALSE;
    }

    //获得标记名称
    function GetTagName()
    {
        return $this->GetAtt("tagname");
    }

    // 获得属性个数
    function GetCount()
    {
        return $this->Count+1;
    }
}