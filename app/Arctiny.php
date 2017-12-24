<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Arctiny extends Model
{
    //
    protected $table = 'arctiny';

    /**
     * @return Arctiny
     */
    public static function defalutArctiny()
    {
        $self = new self();
        $self->arcrank = '';
        $self->typeid = 0;
        $self->typeid2 = 0;
        $self->channel = 0;
        $self->senddate = time();
        $self->mid = Auth::user()->id;
        $self->sortrank = $self->senddate;
        return $self;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public static function createNewArctiny(Request $request)
    {
        $parmas = $request->post([
            'typeid',
            'typeid2',
            'channel',
            'arcrank',
            'sortrank',
            'senddate',
        ]);

        $parmas = array_filter($parmas);

        $self = self::defalutArchives();
        foreach ($parmas as $key => $v){
            if($v){
                $self->$key = $v;
            }
        }
        return $self;

    }

}
