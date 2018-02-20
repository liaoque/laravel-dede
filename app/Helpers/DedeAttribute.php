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
    public $count = -1;
    public $items = ""; //属性元素的集合
    //获得某个属性
    function getAtt($str)
    {
        if($str=="")
        {
            return "";
        }
        if(isset($this->items[$str]))
        {
            return $this->items[$str];
        }
        else
        {
            return "";
        }
    }

    //同上
    function getAttribute($str)
    {
        return $this->getAtt($str);
    }

    //判断属性是否存在
    function isAttribute($str)
    {
        if(isset($this->items[$str])) return TRUE;
        else return FALSE;
    }

    //获得标记名称
    function getTagName()
    {
        return $this->getAtt("tagname");
    }

    // 获得属性个数
    function getCount()
    {
        return $this->count+1;
    }
}