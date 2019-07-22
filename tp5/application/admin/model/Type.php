<?php
namespace app\admin\model;

class Type extends Backend
{
    protected $table = 'type';

    // public function 

    public static function getLists($where=[], $order=[])
    {
        
        $lists = self::withSearch(['name'], $where)
                    ->select()
                    ->toArray();

        $res['total'] = count($lists);
        $res['list'] = $lists;
        return $res;
    }
}