<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TagIndex extends Model
{
    //
    const UPDATED_AT = 'addtime';

    const CREATED_AT = 'addtime';

    //
    protected $table = 'tagindex';

    public static function createTagIndex($tag, $typeid){
        $tagIndex = new self();
        $tagIndex->tag = $tag;
        $tagIndex->typeid = $typeid;
        $tagIndex->count = 0;
        $tagIndex->total = 1;
        $tagIndex->weekcc = 0;
        $tagIndex->monthcc = 0;
        $tagIndex->weekup = time();
        $tagIndex->monthup = $tagIndex->weekup;
        return $tagIndex;
    }

}
