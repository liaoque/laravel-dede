<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TagList extends Model
{
    //
    const UPDATED_AT = '';

    const CREATED_AT = '';

    //
    protected $table = 'taglist';

    public static function createTagList($tid, $aid, $tag, $typeid, $arcrank){
        $tagIndex = new self();
        $tagIndex->tid = $tid;
        $tagIndex->aid = $aid;
        $tagIndex->arcrank = $arcrank;
        $tagIndex->typeid = $typeid;
        $tagIndex->tag = $tag;
        return $tagIndex;
    }
}
