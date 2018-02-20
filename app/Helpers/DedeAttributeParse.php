<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/13
 * Time: 18:02
 */

namespace App\Helpers;


/*******************************
//属性解析器(本版本中已经支持使用\'这种语法,和用.间隔表示name属性,如 field.body)
function c____DedeAttributeParse();
 ********************************/
class DedeAttributeParse
{
    public $sourceString = "";
    public $sourceMaxSize = 1024;
    public $cAttributes = "";
    public $charToLow = TRUE;
    function setSource($str='')
    {
        $this->cAttributes = new DedeAttribute();
        $strLen = 0;
        $this->sourceString = trim(preg_replace("/[ \r\n\t]{1,}/"," ",$str));

        //为了在function内能使用数组，这里允许对[ ]进行转义使用
        $this->sourceString = str_replace('\]',']',$this->sourceString);
        $this->sourceString = str_replace('[','[',$this->sourceString);
        /*
        $this->sourceString = str_replace('\>','>',$this->sourceString);
        $this->sourceString = str_replace('<','>',$this->sourceString);
        $this->sourceString = str_replace('{','{',$this->sourceString);
        $this->sourceString = str_replace('\}','}',$this->sourceString);
        */

        $strLen = strlen($this->sourceString);
        if($strLen>0 && $strLen <= $this->sourceMaxSize)
        {
            $this->ParseAttribute();
        }
    }

    //解析属性
    function parseAttribute()
    {
        $d = '';
        $tmpatt = '';
        $tmpvalue = '';
        $startdd = -1;
        $ddtag = '';
        $hasAttribute=FALSE;
        $strLen = strlen($this->sourceString);
        $this->cAttributes->Items = array();

        // 获得Tag的名称，解析到 cAtt->getAtt('tagname') 中
        for($i=0; $i<$strLen; $i++)
        {
            if($this->sourceString[$i]==' ')
            {
                $this->cAttributes->Count++;
                $tmpvalues = explode('.', $tmpvalue);
                $this->cAttributes->Items['tagname'] = ($this->charToLow ? strtolower($tmpvalues[0]) : $tmpvalues[0]);
                if(isset($tmpvalues[1]) && $tmpvalues[1]!='')
                {
                    $this->cAttributes->Items['name'] = $tmpvalues[1];
                }
                $tmpvalue = '';
                $hasAttribute = TRUE;
                break;
            }
            else
            {
                $tmpvalue .= $this->sourceString[$i];
            }
        }

        //不存在属性列表的情况
        if(!$hasAttribute)
        {
            $this->cAttributes->Count++;
            $tmpvalues = explode('.', $tmpvalue);
            $this->cAttributes->Items['tagname'] = ($this->charToLow ? strtolower($tmpvalues[0]) : $tmpvalues[0]);
            if(isset($tmpvalues[1]) && $tmpvalues[1]!='')
            {
                $this->cAttributes->Items['name'] = $tmpvalues[1];
            }
            return ;
        }
        $tmpvalue = '';

        //如果字符串含有属性值，遍历源字符串,并获得各属性
        for($i; $i<$strLen; $i++)
        {
            $d = $this->sourceString[$i];
            //查找属性名称
            if($startdd==-1)
            {
                if($d != '=')
                {
                    $tmpatt .= $d;
                }
                else
                {
                    if($this->charToLow)
                    {
                        $tmpatt = strtolower(trim($tmpatt));
                    }
                    else
                    {
                        $tmpatt = trim($tmpatt);
                    }
                    $startdd=0;
                }
            }

            //查找属性的限定标志
            else if($startdd==0)
            {
                switch($d)
                {
                    case ' ':
                        break;
                    case '"':
                        $ddtag = '"';
                        $startdd = 1;
                        break;
                    case '\'':
                        $ddtag = '\'';
                        $startdd = 1;
                        break;
                    default:
                        $tmpvalue .= $d;
                        $ddtag = ' ';
                        $startdd = 1;
                        break;
                }
            }
            else if($startdd==1)
            {
                if($d==$ddtag && ( isset($this->sourceString[$i-1]) && $this->sourceString[$i-1]!="\\") )
                {
                    $this->cAttributes->Count++;
                    $this->cAttributes->Items[$tmpatt] = trim($tmpvalue);
                    $tmpatt = '';
                    $tmpvalue = '';
                    $startdd = -1;
                }
                else
                {
                    $tmpvalue .= $d;
                }
            }
        }//for

        //最后一个属性的给值
        if($tmpatt != '')
        {
            $this->cAttributes->Count++;
            $this->cAttributes->Items[$tmpatt] = trim($tmpvalue);
        }
        //print_r($this->cAttributes->Items);
    }// end func
}