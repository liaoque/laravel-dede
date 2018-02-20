<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/13
 * Time: 18:02
 */

namespace App\Helpers;

/**
 * class DedeTag 标记的数据结构描述
 * function c____DedeTag();
 *
 * @package          DedeTag
 * @subpackage       DedeCMS.Libraries
 * @link             http://www.dedecms.com
 */
class DedeTag
{
    public $isReplace=FALSE; //标记是否已被替代，供解析器使用
    public $tagName="";      //标记名称
    public $innerText="";    //标记之间的文本
    public $startPos=0;      //标记起始位置
    public $endPos=0;        //标记结束位置
    public $cAttribute="";   //标记属性描述,即是class DedeAttribute
    public $tagValue="";     //标记的值
    public $tagID = 0;

    /**
     *  获取标记的名称和值
     *
     * @access    public
     * @return    string
     */
    function getName()
    {
        return strtolower($this->tagName);
    }

    /**
     *  获取值
     *
     * @access    public
     * @return    string
     */
    function getValue()
    {
        return $this->tagValue;
    }

    //下面两个成员函数仅是为了兼容旧版
    function getTagName()
    {
        return strtolower($this->tagName);
    }

    function getTagValue()
    {
        return $this->tagValue;
    }

    //获取标记的指定属性
    function isAttribute($str)
    {
        return $this->cAttribute->IsAttribute($str);
    }

    function getAttribute($str)
    {
        return $this->cAttribute->getAtt($str);
    }

    function getAtt($str)
    {
        return $this->cAttribute->getAtt($str);
    }

    function getInnerText()
    {
        return $this->innerText;
    }
}
